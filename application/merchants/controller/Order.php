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
use think\Exception;
use think\facade\Cache;
use think\facade\Env;
use think\Model;
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
        $status = $request->param('status','');//1:订单待支付;2等待商家接单;3商家已接单;4商家拒绝接单;5骑手取货中;6骑手配送中;7订单已送达;8订单已完成;9订单已取消;10骑手待取餐
        $page_no = $request->param('page');
        $page_size = $request->param('pageSize',20);
        $shop_id = $this->shop_id;
        $date = $request->param('date');

        /*if(!$status ) {
            $this->error('非法传参');
        }*/

        $map = '1=1';

        //从缓存中获取
        /*$key = "shop_info:shop_id:$shop_id:status:$status";
        $orders = Cache::store('redis')->get($key);

        if($orders) {
            $this->success('获取成功',$orders);
        }*/

        //构建查询表达式
        $map = [];

        if($shop_id) {
            $map[] = ['shop_id','=',$shop_id];
        }
        if($status) {
            $map[] = ['status','=',$status];
        }

        if($date) {
            //$map[] = ['add_time','>',$date];
        }

        $result = model('orders')
            ->where($map)
            //->whereBetweenTime('','','')
            ->page($page_no,$page_size)->select();

        if(empty($result)) {
            $this->error('暂无订单');
        }

        $orders = [];
        foreach ($result as $row)
        {
            $orders[] = [
                'orders_sn' => $row['orders_sn'],
                'orders_id' => $row['id'],
                'address' => $row['address'],
                'add_time' => date('Y-m-d H:i',$row['add_time']),
                'money' => $row['money'],
                'status' => $row['status'],
            ];
        }

        //写入缓存
        //Cache::store('redis')->set($key,$orders);

        $this->success('获取成功',$orders);

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
        }
        return $detail;
    }

    /**
     * 店铺订单详情
     */
    public function OrderDetail(Request $request)
    {
        $order_sn = $request->param('orders_sn');
        $result = model('Orders')->getOrder($order_sn);

        //dump($result);


        if(!$result) {
            $this->error('订单明细不存在!');
        }


        $order_info = [
            'orders_sn' => $result['orders_sn'],
            'orders_id' => $result['id'],
            'add_time' => date('Y-m-d H:i',$result['add_time']),
            'address' => $result['address'],
            'remark' => $result['message'],
            'total_money' => $result['total_money'],
            'box_money' => $result['box_money'],
            'ping_fee' => $result['ping_fee'],
            'discount_money' => $result['shop_discounts_money'] + $result['platform_coupon_money'],
            'money' => $result['money'],
            'detail' => $this->detail($result['id'])
        ];

        $this->success('获取成功',$order_info);
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

        if($order_info['status'] == 2) {
            $this->error('商家已退款');
        }

        try{
            $res = $this->wxRefund($orders_sn);

            if('SUCCESS' == $res['data']['return_code'] && 'SUCCESS' == $res['data']['result_code']) {

                $result = model('Orders')->where('orders_sn',$orders_sn)->setField('status',4);

                if($result) {
                    $this->success('拒单成功');
                }
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

        //根据商户订单号退款
        $result = $app->refund->byOutTradeNumber( $number, $refundNumber, $totalFee, $refundFee, $config = [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' => '取消订单退款',
            'notify_url'    => 'https' . "://" . $_SERVER['HTTP_HOST'].'/api/notify/refundBack',
        ]);


        return $result;
    }





}