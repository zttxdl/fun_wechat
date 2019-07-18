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
use app\common\service\PushEvent;

class Notify extends Collection
{

    /**
     * 支付成功回调
     * @param Request $request
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function index(Request $request)
    {
        //转成数组
        $xml = XML::parse(strval($request->getContent()));
        //日志记录
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
                    $this->returnResult($message['out_trade_no'], $message['transaction_id'],$order['shop_id']);
                }
            }else {
                return $fail('通信失败，请稍后再通知我');
            }

            return true;
        });

        $response->send();
    }

    //微信支付回调处理业务
    public function returnResult($orders_sn,$wx_id,$shop_id)
    {
        Db::startTrans();
        try {
            //处理的业务逻辑，更新订单
            model('orders')->where('orders_sn',$orders_sn)->update(['status'=>2,'pay_status'=>1,'pay_time'=>time(),'trade_no'=>$wx_id]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            trace($e->getMessage(),'error');
        }

        // 向指定商家推送新订单消息
        $push = model('PushEvent','service');
        $push->setUser('s_'.$shop_id)->setContent('您有新的校园外卖订单，请及时处理')->push();

        return true;
    }

    /**
     * 退款成功回调
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function refundBack()
    {

        $pay_config = config('wx_pay');
        $app    = Factory::payment($pay_config);//pay_config 微信配置
        $response = $app->handleRefundedNotify(function ($message, $reqInfo, $fail) {

            $refund_info = model('Refund')->where('out_refund_no',$reqInfo['out_refund_no'])->find();
            if (!$refund_info || $refund_info->status== 2) {// 如果订单不存在 或者 订单已经退过款了
                $fail('Order not exist.');
                return true;
            }

            if($message['return_code']=='SUCCESS'){

                if($reqInfo['refund_status']=='SUCCESS'){
                $data= [
                    'refund_id'=> $reqInfo['refund_id'],
                    'refund_time'=>time()
                ];
                model('Refund')
                    ->where('out_refund_no',$reqInfo['out_refund_no'])
                    ->update($data);

                }

            }
            return true; // 返回 true 告诉微信“我已处理完成”
        });

        $response->send();
    }

}