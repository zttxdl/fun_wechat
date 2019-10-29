<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/3
 * Time: 2:37 AM
 */

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use app\common\model\Orders;
use app\common\service\FeieYun;
use EasyWeChat\Factory;
use think\Exception;
use think\Request;
use think\Db;
use think\Model;

class Order extends MerchantsBase
{

    protected $noNeedLogin = ['notifyAccept'];

    /**
     * 订单管理
     */
    public function index(Request $request)
    {
        $status = $request->param('status','');//1:订单待支付;2等待商家接单;3商家已接单;4商家拒绝接单;5骑手取货中;6骑手配送中;7订单已送达;8订单已完成;9订单已取消;10骑手待取餐
        $page_no = $request->param('page');
        $page_size = $request->param('pageSize',20);
        $shop_id = $this->shop_id;

        $map = [];

        if($status) {
            if($status == '2') {//新订单
                $map[] = ['status','=',$status];
            }elseif($status == '3'){//处理中
                $map[] = ['status','in',[3,5]];
            }else{//已完成
                $map[] = ['status','in',[6,7,8]];
            }
        }else{
           //全部
            $map[] = ['status','in',[2,3,4,5,6,7,8]];
        }

        if(!$shop_id) {
            $this->error('缺少必要参数');
        }

        $map[] = ['shop_id','=',$shop_id];

        $orders = Orders::where($map)->whereTime('add_time','today')->order('add_time DESC')->paginate($page_size)->toArray();


        if(!$orders) {
            $this->error('暂无订单');
        }

        $data = [];
        foreach ($orders['data'] as $key => &$row)
        {
            $data[] = [
                'orders_sn' => $row['orders_sn'],
                'add_time' => date('m-d H:i',$row['add_time']),//下单时间
                'address' => $row['address'],
                'message' => $row['message'],
                'box_money' => $row['box_money'],
                'ping_fee' => $row['ping_fee'],
                'num'=> $row['num'],
                'money' => $row['money'],
                'meal_sn'=> $row['meal_sn'],
                'rider_tel'=> Model('RiderInfo')->getPhoneById($row['rider_id']),
                'rider_name'=> Model('RiderInfo')->getNameById($row['rider_id']),
                'issuing_status' => $row['issuing_status'],
                'type' => $this->getShopType($row['status']),
                'status' => $row['status'],
                'detail' => $this->detail($row['id']),

            ];
        }


        $auto_receive = Db::name('shop_info')->where('id','=',$this->shop_id)->value('auto_receive');

        $result['list'] = $data;
        $result['auto_receive'] = $auto_receive;
        $result['count'] = $orders['total'];
        $result['page'] = $orders['current_page'];
        $result['pageSize'] = $orders['per_page'];
        $this->success('获取成功',$result);

    }

    /**
     * 店铺订单查询
     */
    public function query(Request $request)
    {
        $status = $request->param('status','');//1:订单待支付;2等待商家接单;3商家已接单;4商家拒绝接单;5骑手取货中;6骑手配送中;7订单已送达;8订单已完成;9订单已取消;10骑手待取餐
        $page_no = $request->param('page');
        $page_size = $request->param('pageSize',20);
        $shop_id = $this->shop_id;
        $date = $request->param('date',date('Ymd'));//默认时间是当天

        //构建查询表达式
        $map = [];

        if($shop_id) {
            $map[] = ['shop_id','=',$shop_id];
        }
        if($status) {
            $map[] = ['status','=',$status];
        }else{
            $map[] = ['status','notin',[1,9,10,11,12]];
        }

        if($date) {
            $start = strtotime($date.'00:00:00');
            $end = strtotime($date.'23:59:59');
            $map[] = ['add_time','between time',[$start,$end]];
        }

        $orders = model('orders')
            ->where($map)
            ->order('add_time DESC')
            ->paginate($page_size)->toArray();

        //var_dump($result);exit;
        if(empty($orders)) {
            $this->error('暂无订单');
        }

        //$result = [];
        $data = [];
        foreach ($orders['data'] as $row)
        {
            $type = $this->getShopType($row['status']);
            $data[] = [
                'orders_sn' => $row['orders_sn'],
                'orders_id' => $row['id'],
                'address' => $row['address'],
                'add_time' => date('Y-m-d H:i',$row['add_time']),
                'money' => $row['money'],
                'status' => $row['status'],
                'type'=>$type,
                'meal_sn'=> $row['meal_sn'],
                'rider_tel'=> Model('RiderInfo')->getPhoneById($row['rider_id']),
                'issuing_status' => $row['issuing_status']//出餐状态 0:未出餐 1:已出餐
            ];

        }
        //写入缓存
        //Cache::store('redis')->set($key,$orders);


        //var_dump($result);
        //var_dump($orders);
        $result['list'] = $data;
        $result['count'] = $orders['total'];
        $result['page'] = $orders['current_page'];
        $result['pageSize'] = $orders['per_page'];
        $this->success('获取成功',$result);

    }

    /**
     * 商家端状态展示
     */

    public function getShopType($status)
    {
        //商家端状态
        if(in_array($status,[2])) {//等待处理
            $type = '等待处理';
        }

        if(in_array($status,[4])) {//等待处理
            $type = '商家已拒单';
        }

        if(in_array($status,[3,5])) {//已接单
            $type = '已接单';
        }

        if(in_array($status,[6])) {//配送中
            $type = '配送中';
        }

        if(in_array($status,[7,8])) {//已完成
            $type = '已完成';
        }

        return $type;
    }


    /**
     * 获取店铺订单详情
     */
    public function detail($id)
    {
        $detail = Db::name('Orders_info')
            ->field('id,orders_id,product_id,num,ping_fee,box_money,attr_ids,total_money,old_money')
            ->where('orders_id','=',$id)
            ->select();

        foreach ($detail as &$row)
        {
            $row['attr_names'] = model('Shop')->getGoodsAttrName($row['attr_ids']);
            $row['name'] = model('Product')->getNameById($row['product_id']);
        }
        return $detail;
    }

    /**
     * 店铺订单详情
     */
    public function OrderDetail(Request $request)
    {
        $order_sn = $request->param('orders_sn');
        $orders = model('Orders')->getOrder($order_sn);

        //dump($result);


        if(!$orders) {
            $this->error('订单明细不存在!');
        }
        $result = [];

        $result['orders'] = [
            'orders_sn' => $orders['orders_sn'],
            'orders_id' => $orders['id'],
            'add_time' => date('Y-m-d H:i',$orders['add_time']),
            'address' => $orders['address'],
            'remark' => $orders['message'],
            'total_money' => $orders['total_money'],
            'box_money' => $orders['box_money'],
            'ping_fee' => $orders['ping_fee'],
            'discount_money' => $orders['shop_discounts_money'] + $orders['platform_coupon_money'],
            'money' => $orders['money'],
            'type' => $this->getShopType($orders['status'])
        ];
        $result['detail'] = $this->detail($orders['id']);


        $this->success('获取成功',$result);
    }

    /**
     * 商家接单
     */
    public function accept(Request $request)
    {
        $orders_sn = $request->param('orders_sn');

        $order_info = Db::name('orders')->where('orders_sn',$orders_sn)->find();

        if(empty($order_info)) {
            $this->error('订单不存在');
        }

        if($order_info['status'] == 3) {
            $this->error('商家已接单');
        }

        if($order_info['status'] == 9) {
            $this->error('订单已取消!');
        }

        $shop_info = Model('Shop')->getShopDetail($order_info['shop_id']);


        $shop_address = [
            'shop_name' => $shop_info['shop_name'],
            'address_detail' => $shop_info['address'],
            'phone' => $shop_info['link_tel'],
            'name' => $shop_info['link_name'],
            'longitude' => $shop_info['longitude'],
            'latitude' => $shop_info['latitude'],
        ];

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
                'expected_time' => time()+30*60,//预计送达时间
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
            $this->error($e->getMessage());
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
            $this->success('接单成功');
        } else {
            $this->error('打印小票出错',205);
        }
    }

    /**
     * 商家拒单
     */
    public function refuse(Request $request)
    {
        $orders_sn = $request->param('orders_sn');
        $order_info = Db::name('orders')->where('orders_sn',$orders_sn)->find();

        if(!$order_info) {
            $this->error('订单不存在!');
        }

        if($order_info['status'] == '3') {
            $this->error('订单已接单,无法拒单');
        }

        if($order_info['status'] == '4') {
            $this->error('订单已拒单,请勿重复提交!');
        }

        if($order_info['status'] == '9') {
            $this->error('订单已取消!');
        }

        //去微信查一下订单是否退款,没有退款在走下面的退款接口
        $res = $this->refundQuery($orders_sn);

        if($res['result_code'] == 'SUCCESS' && $res['return_code'] == 'SUCCESS') {
            $this->error('订单已拒单,请勿重复提交!');
        }

        try{
            $res = $this->wxRefund($orders_sn);//商家拒绝接单把钱退给用户

            if($res['result_code'] == 'SUCCESS' && $res['return_code'] == 'SUCCESS') {
                $result = model('Orders')->where('orders_sn',$orders_sn)->update(['status'=>4,'shop_receive_time'=>time()]);

                //如果使用红包 状态回滚
                if($order_info['platform_coupon_money'] > 0){
                    $data['status'] = 1;//未使用
                    $my_coupon_id = model('MyCoupon')->where([['user_id','=',$order_info['user_id']],['platform_coupon_id','=',$order_info['platform_coupon_id']],['status','=','2']])->value('id');
                    Model('MyCoupon')->updateStatus($my_coupon_id,$data);
                }

                if(!$result) {
                    throw new Exception('拒单失败');
                }
            }else{
                throw new Exception($res['return_msg']);
            }

            //今日特价商品逻辑
            $id = model('TodayDeals')->getTodayProduct($this->shop_id);
            if ($id){
                $order_detail =  model('Orders')->getOrderDetail($order_info['id']);
                $product  = array_column($order_detail,'product_id');
                if (in_array($id,$product)){
                    model('TodayDeals')->updateTodayProductNum($order_info['shop_id'],'inc',$id);
                }
            }

        }catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('拒单成功');
    }

    /**
     * 微信退款处理【商家拒单的退款处理】
     */
    public function wxRefund($orders_sn)
    {

        $request['number'] = trim($orders_sn);//商户订单号

        if (!$request['number']){
            $this->error('非法传参');
        }

        $find = model('Orders')->where('orders_sn',$request['number'])->find();

        if (!$find){
            $this->error('商户订单号错误');
        }
        $money = intval((string)($find->money * 100));
        $request['totalFee'] = $money;
        $request['refundFee'] = $money;

        $request['refundNumber'] = build_order_no('T');

        set_log('request==',$request,'wexRefund');

        $pay_config = config('wx_pay');

        $app    = Factory::payment($pay_config);//pay_config 微信配置

        //根据商户订单号退款
        $result = $app->refund->byOutTradeNumber( $request['number'], $request['refundNumber'], $request['totalFee'], $request['refundFee'], $config = [
            // 可在此处传入其他参数，详细参数见微信支付文档

            'refund_desc' => '取消订单退款',
//            'notify_url'    => 'https' . "://" . $_SERVER['HTTP_HOST'].'/api/notify/refundBack',

        ]);


        return $result;
    }

    /**
     * 微信退款查询
     * @param Request $request
     */
    public function refundQuery($orders_sn)
    {
        $outTradeNumber = $orders_sn;
        $pay_config = config('wx_pay');
        $app    = Factory::payment($pay_config);//pay_config 微信配置
        $result = $app->refund->queryByOutTradeNumber($outTradeNumber);
        return $result;
    }

    /**
     * 商家确认送出
     */
    public function songChu(Request $request)
    {
        $order_sn = $request->param('orders_sn');

        $res = Db::name('orders')->where('orders_sn',$order_sn)->update(['issuing_time'=>time(),'issuing_status'=>1]);

        if($res){
            $this->success('success',['issuing_status'=>1]);
        }
        $this->error('fail',201,['issuing_status'=>0]);
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
        }
        else{
            //服务器返回的JSON字符串，建议要当做日志记录起来
            write_log($client->getContent(),'log');
            return true;
        }
    }



    /**
     * 单独调用打印小票【飞鹅云打印】 
     * 
     */
    public function printFeieOrder(Request $request)
    {
        $orders_sn = $request->param('orders_sn');
        $print_device_sn = Db::name('shop_info')->where('id','=',$this->shop_id)->value('print_device_sn');
        // 调用打印
        $printOrderInfo = get_order_info_print($orders_sn,14,6,3,6);

        $res = $this->feieyunPrint($print_device_sn,$printOrderInfo,1);
        if ($res) {
            $this->success('打印小票成功');
        } else {
            $this->error('打印小票出错',205);
        }
    }


    public function notifyAccept($orders_sn)
    {
        write_log('进来回调接单了','log');
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
                'expected_time' => time()+30*60,//预计送达时间
                'user_address' => $order_info['address'],//收货地址
                'shop_address' => json_encode($shop_address,JSON_UNESCAPED_UNICODE),//商家地址
                'hourse_id' => $order_info['hourse_id']//楼栋ID
            ];

            //外卖数据入库
            $ret = Db::name('takeout')->insert($takeout_info);
            write_log('写入外卖表返回状态'.$ret,'log');

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
        write_log('走到这里了么','log');

        //实例化socket
        $socket = model('PushEvent','service');
        write_log($socket,'log');

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
        write_log('商家打印设备号：'.$shop_info['print_device_sn'],'log');
        write_log('打印信息内容：'.$printOrderInfo,'log');
        $res = $this->feieyunPrint($shop_info['print_device_sn'],$printOrderInfo,1);

        if ($res) {
            return 1;
        } else {
            return 2;
        }
    }
     


}