<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;
/**
 * Created by PhpStorm.
 * User: lxk
 * Date: 2019/6/18
 * Time: 7:55 PM
 */

class AutoShell extends Controller
{
    /**
     * 每天凌晨0点定时更新红包的过期状态
     *
     * @return \think\Response
     */
    public function setMyCouponOvertime()
    {
        $list = Db::name('my_coupon')->where('status',1)->field('id,indate,status')->select();

        // 判断红包是否过期，并更新状态
        foreach ($list as $k => $v) {
            $indate_time = strtotime(str_replace('.','-',end(explode('-',$v['indate'])))) + 3600*24;
            if ($indate_time < time()) {
                Db::name('my_coupon')->where('id',$v['id'])->setField('status',3);
            }
        }
    }

    
}
