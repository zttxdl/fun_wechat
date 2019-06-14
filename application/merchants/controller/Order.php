<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/3
 * Time: 2:37 AM
 */

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use EasyWeChat\Factory;
use think\Request;
use think\Db;

class Order extends MerchantsBase
{

    protected $noNeedLogin = ['refund'];

    /**
     * 店铺订单查询
     */
    public function show(Request $request)
    {
        $status = $request->param('status','');//订单状态 2:新订单 3：处理中 4:商家拒绝接单 5:骑手已接单 6:骑手已配送 7:商家出单 8订单已送达 9:已完成
        $page_no = $request->param('page');
        $page_size = $request->param('pageSize',20);

        if(!$page_no ) {
            $this->error('非法传参');
        }

        $map = '';

        if($status) {
            $map = ['status' => $status];
        }

        $result = model('orders')->where($map)->page($page_no,$page_size)->select();

        if(empty($result)) {
            $this->error('暂无订单');
        }

        $orders = [];
        foreach ($result as $row)
        {
            $orders[] = [
                'orders_sn' => $row['orders_sn'],
                'add_time' => date('Y-m-d H:i',$row['add_time']),
                'address' => $row['address'],
                'remark' => $row['message'],
                'ping_fee' => $row['ping_fee'],
                'money' => $row['money'],
                'detail' => $this->detail($row['id'])
            ];
        }

        $this->success('获取成功',$orders);

    }


    /**
     * 获取店铺订单详情
     */
    public function detail($id)
    {
        $detail = Db::name('Orders_info')->where('orders_id','=',$id)->select();
        return $detail;
    }

    /**
     * 店铺订单详情
     */
    public function OrderDetail(Request $request)
    {
        $order_sn = $request->param('orders_sn');
        $result = model('Orders')->getOrder($order_sn);
        //dump($result);exit;

        $orders = [];
        foreach ($result as $row) {
            $orders = [
                'orders_sn' => $row['orders_sn'],
                'add_time' => date('Y-m-d H:i',$row['add_time']),
                'address' => $row['address'],
                'remark' => $row['message'],
                'box_money' => $row['box_money'],
                'ping_fee' => $row['ping_fee'],
                'money' => $row['money'],
                'discount_money' => $row['shop_discounts_money'] + $row['platform_coupon_money'],
                'detail' => model('Orders')->getOrderDetail($row['id'])
            ];
        }

        $this->success('获取成功',$orders);
    }
    /**
     * 拒单 接单处理
     */
    public function receipt(Request $request)
    {
        $status = $request->param('status');//3:接单 4:拒单 7:确认送出
        $orders_sn = $request->param('orders_sn');

        $result = model('Orders')->where('orders_sn',$orders_sn)->update(['status'=>$status]);

        $this->success('success',$result);
    }

    /**
     * 退款处理
     */
    public function refund(Request $request)
    {

        $number = $request->param('number');//商户订单号
        $refundNumber = $request->param('refundNumber');//生成唯一商户退款单号
        $totalFee = $request->param('totalFee');//订单金额
        $refundFee = $request->param('refundFee');//退款金额

        if (!$number || !$refundNumber){
            $this->error('非法传参');
        }

        if ($totalFee < $refundFee){
            $this->error('退款金额不能大于订单总额');
        }

        $totalFee = $totalFee*100;
        $refundFee = $refundFee*100;

        $pay_config = config('wx_pay');
        $app    = Factory::payment($pay_config);//pay_config 微信配置

        //根据商户订单号退款
        $result = $app->refund->byOutTradeNumber( $number, $refundNumber, $totalFee, $refundFee, $config = [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' => '取消订单退款',
            'notify_url'    => 'http' . "://" . $_SERVER['HTTP_HOST'].'/api/notify/refundBack',
        ]);


        $this->success('success',$result);
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