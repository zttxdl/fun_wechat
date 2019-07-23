<?php

namespace app\common\model;

use think\Model;

class ReceivingAddr extends Model
{
    /**
     * 获取收货地址 
     * 
     */
    public function getReceivingAddrList($uid)
    {
        $list = $this->where('user_id',$uid)->order('add_time desc')->select()->toArray();
        
        return $list;
    }
     
}
