<?php

namespace app\common\model;

use think\Model;

class Orders extends Model
{
    // 设置json类型字段
    protected $json = ['address'];
    //
    /**
     * 获取会员累计消费金额、次数
     * @param $uid
     */
    public function getUserConsume($uid)
    {
        $data = model('Orders')->where('user_id','=',$uid)->field('SUM(money) as total_money,count(id) as count_num')->find();
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
        return $this->name('orders')->where('orders_sn',$order_sn)->setField(['status'=>$status,'cancle_time'=>time()]);
    }

    /**
     * 获取订单
     */
    public function getOrderById($order_id)
    {
        return $this->where('id',$order_id)->find();
    }

    /**
     * 订单详情
     */
    public function getOrderDetail($order_id)
    {
        $data = $this->name('orders_info')->where('orders_id',$order_id)->select();
        return $data;
    }

    /**
     * 获取订单
     */
    public function getOrder($order_sn)
    {
        return $this->where('orders_sn',$order_sn)->find();
    }



    /**
     * 获取订单编号
     */
    public function getOrderSnById($order_id)
    {
        return $this->where('id',$order_id)->value('orders_sn');
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
