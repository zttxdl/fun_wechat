<?php


namespace app\merchants\controller;
use app\common\controller\MerchantsBase;
use EasyWeChat\Factory;
use think\Request;
use think\facade\Env;
use think\Db;

class Refund extends MerchantsBase
{
    protected $noNeedLogin = [];
    /*
     * 退款申请查询
     */
    public function index(Request $request) {
        $shop_id = $this->shop_id;
        $type = $request->param('type','');//1申请退款， 2退款成功， 3退款失败
        $map = [];

        if($type) {
            $map[] = ['status','=',$type];
        }

        if($shop_id) {
            $map[] = ['shop_id','=',$shop_id];
        }


        $refund_info = Model('Refund')
            ->where($map)
            ->select();

//        dump($refund_info);

        foreach ($refund_info as &$row) {
            $row['add_time'] = date('m-d H:i',$row['add_time']);//申请退款时间
            $row['refund_time'] = date('m-d H:i',$row['refund_time']);//退款完成时间
            $row['imgs'] = explode(',',$row['imgs']);
            $detail = $this->detail($row['orders_id']);
            $row['box_money'] = $detail['box_money'];
            $row['phone'] = $detail['phone'];
            $row['detail'] = $detail['detail'];
        }


        return $this->success('获取成功',$refund_info);
    }

    /**
     * 获取店铺订单详情
     */
    public function detail($id)
    {
        $box_money = 0;
        $detail = model('OrdersInfo')
            ->field('id,orders_id,product_id,num,ping_fee,box_money,attr_ids,total_money,old_money')
            ->where('orders_id','=',$id)
            ->select();

        $data = model('Orders')->field('address')->where('id',$id)->find();

        foreach ($detail as &$row)
        {
            $row['attr_names'] = model('Shop')->getGoodsAttrName($row['attr_ids']);
            $row['name'] = model('Product')->getNameById($row['product_id']);
            $box_money += $row['num'] * $row['box_money'];
        }

        return ['detail'=>$detail,'box_money'=>$box_money,'phone'=>$data['address']->phone];
    }

    /**
     * 商家同意用户的申请退款
     */
    public function refund(Request $request) {
        $orders_sn = $request->param('orders_sn');

        //error_log(print_r($orders_sn,1),3,Env::get('root_path')."./logs/refund.log");
        Db::startTrans();
        try{
            if(empty($orders_sn) && !isset($orders_sn)) {
                throw new \Exception('订单号不能为空!');
            }
            $data = model('Refund')->where('out_refund_no',$orders_sn)->find();

            if($data['status'] == 2) {
                throw new \Exception('商家已退款!');
            }

            if($data['status'] == 3) {
                throw new \Exception('商家已拒绝退款');
            }

            $res = $this->wxRefund($data['out_trade_no']);

            if('SUCCESS' == $res['return_code'] && 'SUCCESS' == $res['result_code']) {
                //更新退单状态 add by ztt 20190722
                $res = model('Refund')->where('out_refund_no',$orders_sn)->setField('status',2);
                if(!$res) {
                    throw new \Exception('refundStatus update fail');
                }
                //回写订单主表订单状态
                $res = model('Orders')->where('orders_sn',$data['out_trade_no'])->setField('status',11);
                if(!$res) {
                    throw new \Exception('orderStatus update fail');
                }
                //退款收支明细 add by ztt 20190814
                $res = model('Withdraw')->refund($orders_sn);

                if(!$res) {
                    throw new \Exception('refund insert fail');
                }

                //统计店铺日取消订单量
                model('Shop')->setDayCancelNum($data['shop_id']);

                // 提交事务
                Db::commit();
                return json_success('退款成功');
            }else{
                throw new \Exception($res['err_code_des']);
            }




        }catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error($e->getMessage());
        }
    }

    /**
     *  商家拒绝用户的申请退款
     */
    public function refuse(Request $request) {
        $orders_sn = $request->param('orders_sn');

        //error_log(print_r($orders_sn,1),3,Env::get('root_path')."./logs/refund.log");

        if(empty($orders_sn) && !isset($orders_sn)) {
            throw new \Exception('订单号不能为空!');
        }

        $data = model('Refund')->where('out_refund_no',$orders_sn)->find();

        if($data['status'] == 2) {
            $this->error('商家已退款!');
        }

        if($data['status'] == 3) {
            $this->error('商家已拒绝退款!');
        }

        //更新退单状态 add by ztt 20190722
        model('Refund')->where('out_refund_no',$orders_sn)->setField('status',3);
        //回写订单主表订单状态
        model('Orders')->where('orders_sn',$data['out_trade_no'])->setField('status',12);

        $this->success('拒绝退款成功');

    }

    /**
     * 微信退款处理【用户申请退款的后续退款处理】
     */
    public function wxRefund($orders_sn)
    {

        $number = trim($orders_sn);//商户订单号

        if (!$number){
            $this->error('非法传参');
        }

        $find = model('Refund')->where('out_trade_no',$number)->find();

        if (!$find){
            $this->error('商户订单号错误');
        }

        if ($find->total_fee < $find->refund_fee){
            $this->error('退款金额不能大于订单总额');
        }

        $totalFee = $find->total_fee * 100; //订单金额
        $refundFee =  $find->refund_fee * 100;//退款金额
        $refundNumber = $find->out_refund_no;//商户退款单号

        $pay_config = config('wx_pay');

        //dump($pay_config);
        $app    = Factory::payment($pay_config);//pay_config 微信配置

        $refundData = [
            'total_fee' => $totalFee,
            'refund_fee' => $refundFee,
            'out_refund_no' => $refundNumber,
            'number' => $number
        ];
        //退单数据查询
        set_log('refundData=',$refundData,'refund');

        //根据商户订单号退款
        $result = $app->refund->byOutTradeNumber( $number, $refundNumber, $totalFee, $refundFee, $config = [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' => '取消订单退款',
            'notify_url'    => 'https' . "://" . $_SERVER['HTTP_HOST'].'/api/notify/refundBack',
        ]);

        set_log('result=',$result,'refund');
        return $result;
    }


    //退款查询
    public function refundQuery(Request $request)
    {
        $outTradeNumber = $request->param('outTradeNumber');
        $pay_config = config('wx_pay');
        $app    = Factory::payment($pay_config);//pay_config 微信配置
        $result = $app->refund->queryByOutTradeNumber($outTradeNumber);

        $this->success('success',$result);
    }
}