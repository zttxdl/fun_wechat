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
}
