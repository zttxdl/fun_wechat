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
    public function cancelOrder($order_sn,$status)
    {
        return $this->name('orders')->where('orders_sn',$order_sn)->setField('status',$status);
    }

    /**
     * 订单详情
     */
    public function getOrderDetail($order_id)
    {
        return $this->name('orders_info')->where('orders_id',$order_id)->select();
    }

    /**
     * 订单
     */
    public function getOrder($order_sn)
    {
        return $this->name('orders')->where('orders_sn',$order_sn)->find();
    }

    /**
     * 添加订单详情
     */
    public function addOrderDetail($data)
    {
        return $this->name('orders_info')->insertAll($data);
    }

    /**
     * 获取订单列表
     * @param $page_no
     * @param $page_size
     * @return mixed
     */
    public function getOrderList($page_no, $page_size)
    {
        return $this->page($page_no,$page_size)->select()->toArray();
    }

    /**
     * 用户是否首单
     * @param $uid
     * @return bool
     */
    public function isFirstOrder($uid)
    {
        $data = $this->name('orders')->where('user_id',$uid)->find();

        return isset($data) ? true : false;
    }

    /**
     * 更新订单状态
     * @param $order_sn
     * @param $status
     * @return int
     */
    public function updateStatus($order_sn,$status)
    {
        return $this->where('orders_sn',$order_sn)->setField('status',$status);
    }
}
