<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/6/3
 * Time: 9:50 AM
 */
namespace app\api\controller;

use EasyWeChat\Factory;
use EasyWeChat\Kernel\Support\XML;
use think\Collection;
use think\Db;
use think\Request;


class Notify extends Collection
{

    public function index(Request $request)
    {
        //转成数组
        $xml = XML::parse(strval($request->getContent()));
        //日志记录
        trace($xml,'info');
        $config = config('wx_pay');

        $payment = Factory::payment($config);

        $response = $payment->handlePaidNotify(function ($message, $fail)
        {
            // 根据返回的订单号查询订单数据
            $order = model('Orders')->getOrder($message['out_trade_no']);
            if (!$order) {
                $fail('Order not exist.');
            }

            if ($order->pay_status  == 1) {
                return true;
            }
            if ($message['return_code'] === 'SUCCESS') {
                // 支付成功后的业务逻辑
                if ($message['result_code'] === 'SUCCESS') {
                    $this->returnResult($message['out_trade_no'], $message['transaction_id']);

                } elseif ($message['result_code'] === 'FAIL') {
                    model('orders')->where('orders_sn',$message['out_trade_no'])
                        ->update(['pay_status'=>2]);
                }
            }else {
                return $fail('通信失败，请稍后再通知我');
            }

            return true;
        });

        $response->send();;
    }

    //微信支付回调处理业务
    public function returnResult($orders_sn,$wx_id)
    {
        Db::startTrans();
        try {
            $orders = model('orders')->where('orders_sn',$orders_sn)->find();

            //处理的业务逻辑，更新订单
            model('orders')
                ->where('orders_sn',$orders_sn)
                ->update(['status'=>2,'pay_status'=>1,'pay_time'=>time(),'trade_no'=>$wx_id]);


            if($orders['platform_coupon_id']) {
                model('myCoupon')->where('platform_coupon_id',$orders['platform_coupon_id'])
                    ->update(['status'=>'2','order_sn'=>$orders_sn]);
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            trace($e->getMessage(),'error');
        }

        return true;
    }

}