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
        $data['total_money'] = Db::name($this->table_name)->where('user_id',$uid)->where('status',1)->count('money');
        $data['order_num'] = Db::name($this->table_name)->where('user_id',$uid)->where('status',1)->count('id');
        return $data;
    }
}
