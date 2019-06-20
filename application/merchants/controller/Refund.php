<?php


namespace app\merchants\controller;
use app\common\controller\MerchantsBase;
use EasyWeChat\Factory;
use think\Request;

class Refund extends MerchantsBase
{
    /*
     * 退款查询
     */
    public function index(Request $request) {
        $shop_id = $this->shop_id;
        $status = $request->param('status');//1申请退款， 2退款成功， 3退款失败

        $refund_info = Model('Refund')
            ->where('shop_id',$shop_id)
            ->where('status',$status)
            ->select();

        foreach ($refund_info as &$row) {
            $refund_detail = Model('OrdersInfo')->where('orders_id',$row['orders_id'])->select();
            $row['detail'] = $refund_detail;
        }


        return $this->success('获取成功',$refund_info);
    }

    /**
     * 商家退单 拒单操作
     */
    public function refundAction(Request $request) {
        $orders_sn = $request->param('orders_sn');
        $type = $request->param('type');

        //退单
        if($type == 'td') {

            model('Refund')->where('out_trade_no',$orders_sn)->setField('status',2);
            model('Orders')->where('orders_sn',$orders_sn)->setField('status',12);
            $msg = '退款成功';
        }elseif ($type == 'jd'){
            model('Refund')->where('out_trade_no',$orders_sn)->setField('status',3);
            model('Orders')->where('orders_sn',$orders_sn)->setField('status',13);
            $msg = '拒绝成功';
        }

        $this->success($msg);

    }

    /**
     * 退款处理
     */
    public function refund(Request $request)
    {

        $number = $request->param('orders_sn');//商户订单号

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

        dump($pay_config);
        $app    = Factory::payment($pay_config);//pay_config 微信配置

        //根据商户订单号退款
        $result = $app->refund->byOutTradeNumber( $number, $refundNumber, $totalFee, $refundFee, $config = [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' => '取消订单退款',
            'notify_url'    => 'https' . "://" . $_SERVER['HTTP_HOST'].'/api/notify/refundBack',
        ]);


        $this->success('success',$result);
    }

    /**
     * 微信退款
     */
    public function wx_refund(Request $request) {

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