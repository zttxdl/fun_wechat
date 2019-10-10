<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;

class Statistics extends Base
{
    /**
     * 用户活跃指标
     *
     */
    public function user(Request $request)
    {
        $time = $request->param('times');
        // 调取条件
        $data = conditions($time);
        $search_time = $data['search_time'];
        $res = $data['res'];
        $nums = $data['nums'];
        $temp_time = $data['temp_time'];
        $result['today_active'] = model('UserActive')->where('save_time','=',date('Y-m-d'))->value('count');
        $result['user_new'] = model('UserNew')->getUserNewCount($search_time,$res,$nums);
        $result['user_active'] = model('UserActive')->getUserActiveCount($search_time,$res,$nums);
        $result['all_user'] = model('User')->count('*');
        $newNum = model('User')->where('add_time','between',$search_time)->count('*');
        $result['new_user_percent'] = floatval(sprintf("%.2f", $newNum / $result['all_user'] * 100)) ;
        $result['time'] = implode('~',$temp_time);
        // 待更新、、、


        
        $this->success('获取数据成功',["list"=>$result]);

    }


    /**
     * 订单统计
     *
     */
    public function order()
    {
        //
    }


    /**
     * 财务统计
     *
     */
    public function finance()
    {
        //
    }


    /**
     * 优惠券统计
     *
     */
    public function coupon()
    {
        //
    }

    
}
