<?php


namespace app\admin\model;


use think\Model;
use think\Db;

class Order extends Model
{
    private $table_name = 'orders';

    /**
     * 获取会员消费记录
     * @param $uid
     */
    public function getUserConsume($uid)
    {
        $data['total_money'] = Db::name($this->table_name)->where('user_id',$uid)->where('status',1)->count('money');
        $data['order_num'] = Db::name($this->table_name)->where('user_id',$uid)->where('status',1)->count('id');
        return $data;
    }
}