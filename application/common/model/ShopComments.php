<?php

namespace app\common\model;

use think\Model;

class ShopComments extends Model
{
    //获取商家评分
    public function getStar($shop_id)
    {
        $where[] = ['shop_id','=',$shop_id];
        $count = $this->where($where)->count();
        $sum = $this->where($where)->sum('star');
        // 此处评分计算， 可将控制语句删除，因流程问题，如果走到这个方法的话， $count 是不可能存在为 0 的情况的
        if ($count != 0){
            $star = round($sum / $count,2);
        }else{
            $star = 5;
        }

        return $star;

    }
}