<?php

namespace app\common\model;

use think\Model;

class UserNew extends Model
{
    /**
     * 获取相关时间搜索的新增用户量 
     * 
     */
    public function getUserNewCount($time,$res,$nums)
    {
        $user_new_list = $this->whereTime('save_time',$time)->field('save_time,count')->order('save_time')->select()->toArray();

        // 获取新增用户量数组补零处理
        array_walk($user_new_list, function ($value, $key) use ($res, &$nums) {
            $index = array_search($value['save_time'],$res);
            $nums[$index] = $value['count'];
        });

        $result['x'] = $res;
        $result['y'] = $nums;
        $result['sum'] = array_sum($nums);
                
        return $result;
    }
}
