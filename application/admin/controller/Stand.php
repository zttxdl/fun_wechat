<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;

class Stand extends Base
{
    /**
     * 合伙人看台 
     * 
     */
    public function investorIndex()
    {
        $school_ids = session('admin_user.school_ids');

        if (!$school_ids) {
            $this->error('非法请求');
        }

        $this->success('这就是合伙人看台的初始页面了');

    }
     
}
