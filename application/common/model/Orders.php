<?php

namespace app\common\model;

use think\Model;

class Orders extends Model
{
    //
    /**
     * 获取会员消费记录
     * @param $uid
     */
    public function getUserConsume($uid)
    {
        $data['total_money'] = $this->name('orders')->where('user_id',$uid)->where('status',1)->count('money');
        $data['order_num'] = $this->name('orders')->where('user_id',$uid)->where('status',1)->count('id');
        return $data;
    }

    /**
     * 获取唯一订单号
     */
    public function getOrderSn()
    {
        return date('YmdHis') . substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    /**
     * 新增订单
     */
    public function addOrder($data)
    {
        return $this->name('orders')->insertGetId($data);
    }

    /**
     * 取消订单
     */
    public function cancelOrder()
    {

    }

    /**
     * 订单列表
     */
    public function orderList()
    {

    }

    /**
     * 订单详情
     */
    public function orderDetail()
    {

    }

    /**
     * 添加订单详情
     */
    public function addOrderDetail($data)
    {
        return $this->name('orders_info')->insertAll($data);
    }


    public function getOrderList($page_no, $page_size)
    {
        return $this->name('orders')->page($page_no,$page_size)->select();
    }
}
