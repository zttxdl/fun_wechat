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
use think\facade\Cache;
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

        if(!$status ) {
            $this->error('非法传参');
        }

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
        $type = $request->param('status');//3:接单 4:拒单 7:确认送出
        $orders_sn = $request->param('orders_sn');

        if($type == 'jd') {

        }elseif ($type == 'jd')

        $result = model('Orders')->where('orders_sn',$orders_sn)->update(['status'=>$status]);

        $this->success('success',$result);
    }



}