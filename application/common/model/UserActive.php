<?php

namespace app\common\model;

use think\Model;

class UserActive extends Model
{
    
    /**
     * 获取相关时间搜索的用户活跃情况 
     * 
     */
    public function getUserActiveCount($time,$res,$nums)
    {
        $user_active_list = $this->whereTime('save_time',$time)->field('count,save_time')->order('save_time')->select()->toArray();

        // 获取新增用户量数组补零处理
        array_walk($user_active_list, function ($value, $key) use ($res, &$nums) {
            $index = array_search($value['save_time'],$res);
            $nums[$index] = $value['count'];
        });

        $result['x'] = $res;
        $result['y'] = $nums;
        $result['sum'] = array_sum($nums);

        return $result;
    }
}
