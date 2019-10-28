<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/8/12
 * Time: 4:45 PM
 */

namespace app\merchants\controller;


use app\common\controller\MerchantsBase;
use think\facade\Cache;
use think\Request;
use think\Db;

class PrintReceipt extends MerchantsBase
{
    /**
     * 商家联
     */
    public function getShopReceipt(Request $request)
    {
        $orders_sn = $request->param('orders_sn');
        $shop_id = $this->shop_id;

        if(!$shop_id) {
            $this->error('非法传参');
        }

        $orderInfo = Model('Orders')->getOrder($orders_sn);

        $result['add_time'] = date('Y-m-d H:i:s',$orderInfo['add_time']);

        $result['message'] = $orderInfo['message'];

        $result['money'] = $orderInfo['money'];

        $result['orders_sn'] = $orderInfo['orders_sn'];

        $result['user_address'] = $orderInfo['address'];

        $result['shop_name'] = Db::name('shop_info')->where('id','=',$orderInfo['shop_id'])->value('shop_name');

        $result['meal_sn'] = $orderInfo['meal_sn'];

        $result['ping_fee'] = $orderInfo['ping_fee'];
        $result['box_money'] = $orderInfo['box_money'];
        $result['dis_money'] = $orderInfo['platform_coupon_money'] + $orderInfo['shop_discounts_money'];

        $orderDetail = Model('Orders')->getOrderDetail($orderInfo['id']);



        foreach ($orderDetail as $row)
        {
            $data['name'] = Model('Product')->getNameById($row['product_id']);
            $data['num'] = $row['num'];
            $data['old_price'] = Model('Product')->getGoodsOldPrice('product_id');
            $data['price'] = $row['price'];
            $result['goods_detail'][] = $data;
        }


        $this->success('获取成功',$result);

    }

    /**
     * 客户联
     */
    public function getCustomerReceipt(Request $request)
    {
        $orders_sn = $request->param('orders_sn');
        $shop_id = $this->shop_id;

        if(!$shop_id) {
            $this->error('非法传参');
        }

        $orderInfo = Model('Orders')->getOrder($orders_sn);

        $result['add_time'] = date('m-d H:i:s',$orderInfo['add_time']);

        $result['message'] = $orderInfo['message'];

        $result['money'] = $orderInfo['money'];

        $result['orders_sn'] = $orderInfo['orders_sn'];

        $result['user_address'] = $orderInfo['address'];

        $result['shop_name'] = Db::name('shop_info')->where('id','=',$orderInfo['shop_id'])->value('shop_name');

        $result['meal_sn'] = $orderInfo['meal_sn'];

        $result['ping_fee'] = $orderInfo['ping_fee'];
        $result['box_money'] = $orderInfo['box_money'];
        $result['dis_money'] = $orderInfo['platform_coupon_money'] + $orderInfo['shop_discounts_money'];

        $orderDetail = Model('Orders')->getOrderDetail($orderInfo['id']);



        foreach ($orderDetail as $row)
        {
            $data['name'] = Model('Product')->getNameById($row['product_id']);
            $data['num'] = $row['num'];
            $data['old_price'] = Model('Product')->getGoodsOldPrice('product_id');
            $data['price'] = $row['price'];
            
            $result['goods_detail'][] = $data;
        }

        $result['address'] = Db::name('shop_info')->where('id','=',$orderInfo['shop_id'])->value('address');
        $result['link_tel'] = Db::name('shop_info')->where('id','=',$orderInfo['shop_id'])->value('link_tel');


        $this->success('获取成功',$result);
    }
}