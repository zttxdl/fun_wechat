<?php

namespace app\admin\controller;

use app\common\controller\Base;
use app\common\model\User;
use think\Request;

class Stand extends Base
{
    /**
     * 合伙人看台 
     * 【默认展示 7 天的数据统计信息】
     * 
     */
    public function investorIndex(Request $request)
    {
        $school_ids = session('admin_user.school_ids');

        if (!$school_ids) {
            $this->error('非法请求');
        }

        $time = $request->param('times');
        $search_time = json_decode($time,true);

        dump($search_time);die;
        // 获取新增用户量
        $user_num = User::getNewUsersCount($search_time);
        


        $this->success('这就是合伙人看台的初始页面了');

    }


    /**
     * 老板看台 
     * 
     */
    public function BossIndex()
    {
        $this->success('这就是老板看台的初始页面了');

    }

     
}
