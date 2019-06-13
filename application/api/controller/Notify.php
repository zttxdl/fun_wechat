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
        $xml = XML::parse(strval($request->getContent()));
        //转成数组
//        $result = json_decode($xml, true);
        trace($xml,'info');

        $options = [
            'app_id' => $xml['app_id'],
            'mch_id' => config('wx_pay')['mch_id'],
            'key' => config('wx_pay')['key'],
            'notify_url' => 'http' . "://" . $_SERVER['HTTP_HOST'].'/api/notify/index'
        ];

        $payment = Factory::payment($options);

        $response = $payment->handlePaidNotify(function ($message, $fail)
        {
            // 根据返回的订单号查询订单数据
            $order = $this->order->findBy('order_num', $message['out_trade_no']);

            if (!$order) {
                $fail('Order not exist.');
            }

            if ($order->pay_status  == 1) {
                return true;
            }

            // 支付成功后的业务逻辑
            if($message['result_code'] === 'SUCCESS')
            {
                $this->returnResult($message['out_trade_no'],$message['transaction_id']);

            }

            return true;
        });

        $response->send();;
    }

    //微信支付回调
    public function wxNotify(){
        //获取返回的xml
        $xml = file_get_contents("php://input");
        $log = './uploads/'.date('Ymd').'.txt';
        // FILE_APPEND 不写第三个参数默认是覆盖，写的话是追加
        file_put_contents($log,date('Y-m-d H:i:s')."\n".$xml."\n",FILE_APPEND);
        //将xml转化为json格式
        $jsonxml = json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA));
        //转成数组
        $result = json_decode($jsonxml, true);
        file_put_contents($log,date('Y-m-d H:i:s')."\n".print_r($result,1)."\n",FILE_APPEND);
        if($result){
            //如果成功返回了
            if($result['return_code'] == 'SUCCESS' && $result['result_code']=="SUCCESS"){

                $this->returnResult($result['out_trade_no'],$result['transaction_id']);
                echo "success";
            }else{
                echo "fail";
            }
        }else{
            echo "fail";
        }
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
            $this->error($e->getMessage());
        }

        return true;
    }

}