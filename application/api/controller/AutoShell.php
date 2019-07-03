<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\facade\Cache;

/**
 * Created by PhpStorm.
 * User: lxk
 * Date: 2019/6/18
 * Time: 7:55 PM
 */

class AutoShell extends Controller
{
    /**
     * 每天凌晨0点定时执行的脚本
     *
     * @return \think\Response
     */
    public function zeroExecute()
    {
        /***************** 更新红包的过期状态  ******************************************************************/
        $list = Db::name('my_coupon')->where('status',1)->field('id,indate,status')->select();
        // 判断红包是否过期，并更新状态
        foreach ($list as $k => $v) {
            $indate_time = strtotime(str_replace('.','-',end(explode('-',$v['indate'])))) + 3600*24;
            if ($indate_time < time()) {
                Db::name('my_coupon')->where('id',$v['id'])->setField('status',3);
            }
        }

        /***************** 清除骑手提现申请的缓存记录  ******************************************************************/
        Cache::store('redis')->clear('rider_tx');

        /***************** 清除用户每天第一次进入小程序的缓存记录  ******************************************************************/
        Cache::store('redis')->clear('active_coupon');


        /***************** 待更新  ******************************************************************/

        


    }




    
}
