<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;

class Index extends Base
{
    /**
     * 首页展示
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        //时间 0 今天; 1 7天内; 2 一月内
        $time = $request->param('time');


    }

}
