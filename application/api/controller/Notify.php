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
use app\merchants\controller\Order;
use app\common\service\FeieYun;
use think\Exception;
use think\facade\Cache;

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

                    $this->returnResult($message['out_trade_no'], $message['transaction_id'],$order['shop_id'],$order['user_id']);
                }
            }else {
                return $fail('通信失败，请稍后再通知我');
            }

            return true;
        });

        $response->send();
    }

    //微信支付回调处理业务
    public function returnResult($orders_sn,$wx_id,$shop_id,$user_id)
    {
        Db::startTrans();
        try {
            //处理的业务逻辑，更新订单
            model('orders')->where('orders_sn',$orders_sn)->update(['status'=>2,'pay_status'=>1,'pay_time'=>time(),'trade_no'=>$wx_id]);

            //用户下单 就更改状态
            model('User')->where('id',$user_id)->setField('new_buy',2);
            // 判断首单红包是否使用
            $id = model('MyCoupon')->where([['user_id','=',$user_id],['first_coupon','=',1],['status','=',1]])->value('id');
            if ($id) {
                model('MyCoupon')->where('id',$id)->setField('status',3);
            }
            # redis 删除 【2019-11-14更新】
            $redis = Cache::store('redis');
            $key = "order_cacle";
            if ($redis->hExists($key,$orders_sn)) {
                $redis->hDel($key,$orders_sn);
            }
            
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            trace($e->getMessage(),'error');
        }

        // 向指定商家推送新订单消息
        $push = new PushEvent();
        $push->setUser('s_'.$shop_id)->setContent($orders_sn)->push();

        // 获取当前商家的自动接单情况
        $auto_print_info = model('ShopInfo')->getAutoPrintInfo($shop_id);
        // 当是云打印机 以及 设置了自动接单、打印功能
        if ($auto_print_info['print_device_sn'] && $auto_print_info['auto_receive']) {
            $this->notifyAccept($orders_sn);
        }
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

    /**
     * 自动接单
     */
    public function notifyAccept($orders_sn)
    {
        $order_info = Db::name('orders')->where('orders_sn',$orders_sn)->find();
        $shop_info = Model('Shop')->getShopDetail($order_info['shop_id']);
        $shop_address = [
            'shop_name' => $shop_info['shop_name'],
            'address_detail' => $shop_info['address'],
            'phone' => $shop_info['link_tel'],
            'name' => $shop_info['link_name'],
            'longitude' => $shop_info['longitude'],
            'latitude' => $shop_info['latitude'],
        ];

        // 预计送达时间
        $time = model('School')->where('id',$shop_info['school_id'])->value('completion_time');
        $expected_time = time() + 60 * $time;

        //启动事务
        Db::startTrans();
        try{
            //封装外卖数据
            $takeout_info = [
                'order_id' => $order_info['id'],
                'shop_id' => $order_info['shop_id'],
                'ping_fee' => $order_info['ping_fee'],//配送费
                'school_id' => $shop_info['school_id'],
                'create_time' => time(),//商家接单时间
                'expected_time' => $expected_time,//预计送达时间
                'user_address' => $order_info['address'],//收货地址
                'shop_address' => json_encode($shop_address,JSON_UNESCAPED_UNICODE),//商家地址
                'hourse_id' => $order_info['hourse_id']//楼栋ID
            ];

            //外卖数据入库
            $ret = Db::name('takeout')->insert($takeout_info);

            if (!$ret){
                throw new Exception('接单失败0');
            } else {
                $meal_sn = getMealSn('shop_id:'.$order_info['shop_id']);
                Db::name('takeout')->where('order_id','=',$order_info['id'])->setField('meal_sn',$meal_sn);
            }
            model('Orders')->where('id',$order_info['id'])->update(['status'=>3,'plan_arrive_time'=>$takeout_info['expected_time'],'shop_receive_time'=>time(),'meal_sn'=>$meal_sn]);
            Db::commit();

        }catch (\Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage());
        }

        //实例化socket
        $socket = model('PushEvent','service');

        // 已成为骑手的情况
        $map1 = [
            ['school_id', '=', $shop_info['school_id']],
            ['open_status', '=', 1],
            ['status', '=', 3],
            ['','exp',Db::raw("FIND_IN_SET(".$order_info['hourse_id'].",hourse_ids)")]
        ];
        // 暂未成为骑手的情况
        $map2 = [
            ['school_id', '=', $shop_info['school_id']],
            ['status', 'in', [0,1,2]],
            ['','exp',Db::raw("FIND_IN_SET(".$order_info['hourse_id'].",hourse_ids)")]
        ];  

        $r_list = model('RiderInfo')->whereOr([$map1, $map2])->select();

        foreach ($r_list as $item) {
            $rid = 'r'.$item->id;
            $socket->setUser($rid)->setContent('new')->push();
        }

        // 调用打印
        $printOrderInfo = get_order_info_print($orders_sn,14,6,3,6);
        $res = $this->feieyunPrint($shop_info['print_device_sn'],$printOrderInfo,1);

        if ($res) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 飞鹅云打印 
     * 
     */
    public function feieyunPrint($printer_sn,$orderInfo,$times)
    {
        $user = config('feieyun')['user'];
        $ukey = config('feieyun')['ukey'];
        $ip = config('feieyun')['ip'];
        $port = config('feieyun')['port'];
        $path = config('feieyun')['path'];

        $time = time();			    //请求时间
		$content = array(			
			'user'=>$user,
			'stime'=>$time,
			'sig'=>sha1($user.$ukey.$time),
			'apiname'=>'Open_printMsg',
			'sn'=>$printer_sn,
			'content'=>$orderInfo,
		    'times'=>$times // 打印次数
        );

        // 调用飞鹅云打印类
        $client = new FeieYun($ip,$port);
        if(!$client->post($path,$content)){
            return false;
        }else{
            //服务器返回的JSON字符串，建议要当做日志记录起来
            write_log($client->getContent(),'log');
            return true;
        }
    }

}