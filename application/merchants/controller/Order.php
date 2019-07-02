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
use app\common\model\OrdersInfo;
use EasyWeChat\Factory;
use think\Exception;
use think\facade\Cache;
use think\facade\Env;
use think\Model;
use think\Request;
use think\Db;

class Order extends MerchantsBase
{

    protected $noNeedLogin = [];

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
            $map[] = ['status','=',$status];
        }

        if($shop_id) {
            $map[] = ['shop_id','=',$this->shop_id];
        }

        $orders = Orders::where($map)->paginate($page_size)->toArray();

        //dump($orders);

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
                'type' => $this->getShopType($row['status']),
                'detail' => $this->detail($row['id']),
            ];
        }

        if(!$orders) {
            $this->error('暂无订单');
        }


        $result['list'] = $data;
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
            ->paginate($page_size)->toArray();

        //var_dump($result);exit;
        if(empty($orders)) {
            $this->error('暂无订单');
        }

        //$result = [];
        $type = '';
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
                'type'=>$type
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

        if($order_info['status'] == 3) {
            $this->error('商家已接单');
        }

        $shop_info = Model('Shop')->getShopDetail($order_info['shop_id']);


        $shop_address = [
            'shop_name' => $shop_info['shop_name'],
            'address_detail' => $shop_info['address'],
            'phone' => $shop_info['link_tel'],
            'name' => $shop_info['link_name'],
        ];

        try{
            //封装外卖数据
            $takeout_info = [
                'order_id' => $order_info['id'],
                'shop_id' => $order_info['shop_id'],
                'ping_fee' => $order_info['ping_fee'],//配送费
                'meal_sn' => createOrderSn('shop_id:'.$order_info['shop_id']),//取餐号
                'school_id' => Model('Shop')->getSchoolIdByID($order_info['shop_id']),
                'create_time' => time(),//商家接单时间
                'expected_time' => time()+30*60,//预计送达时间
                'user_address' => $order_info['address'],//收货地址
                'shop_address' => json_encode($shop_address,JSON_UNESCAPED_UNICODE),//商家地址
            ];



            $takeout = Db::name('takeout')->where('order_id',$orders_sn)->value('order_id');

            if($takeout) {
                throw new Exception('订单ID重复');
            }
            //外卖数据入库
            Db::name('takeout')->insert($takeout_info);



            $result = model('Orders')->where('orders_sn',$orders_sn)->setField('status',3);

            return json_success('success');

        }catch (\Exception $e) {
            $this->error($e->getMessage());
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

        //去微信查一下订单是否退款,没有退款在走下面的退款接口
        $res = $this->refundQuery($orders_sn);

//        dump($res);

        if($res['result_code'] == 'SUCCESS' && $res['return_code'] == 'SUCCESS') {
            $this->error('订单已拒单,请勿重复提交!');
        }

        try{
            $res = $this->wxRefund($orders_sn);//商家拒绝接单把钱退给用户

//            dump($res);
            if($res['result_code'] == 'SUCCESS' && $res['return_code'] == 'SUCCESS') {
                $result = model('Orders')->where('orders_sn',$orders_sn)->setField('status',4);

                if($result) {
                    return json_success('拒单成功');
                }
            }else{
                throw new Exception('拒单失败');
            }

        }catch (\Exception $e) {
            $this->error($e->getMessage());
        }



    }

    /**
     * 微信退款处理
     */
    public function wxRefund($orders_sn)
    {

        $number = trim($orders_sn);//商户订单号

        if (!$number){
            $this->error('非法传参');
        }

        $find = model('Orders')->where('orders_sn',$number)->find();

        if (!$find){
            $this->error('商户订单号错误');
        }

        $totalFee = $find->money * 100; //订单金额
        $refundFee =  $find->money * 100;//退款金额
        $refundNumber = build_order_no('T');//商户退款单号

        $pay_config = config('wx_pay');

        //dump($pay_config);
        $app    = Factory::payment($pay_config);//pay_config 微信配置

        //根据商户订单号退款
        $result = $app->refund->byOutTradeNumber( $number, $refundNumber, $totalFee, $refundFee, $config = [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' => '取消订单退款',
            //'notify_url'    => 'https' . "://" . $_SERVER['HTTP_HOST'].'/api/notify/refundBack',
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

        //$this->success('success',$result);
        return $result;
    }


}