<?php

namespace app\api\controller;

use think\Controller;
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
        /***************** 更新我的红包的过期状态  ******************************************************************/
        $list = Db::name('my_coupon')->where('status',1)->field('id,indate,status')->select();
        // 判断红包是否过期，并更新状态
        foreach ($list as $k => $v) {
            $indate_time = strtotime(str_replace('.','-',end(explode('-',$v['indate'])))) + 3600*24;
            if ($indate_time < time()) {
                Db::name('my_coupon')->where('id',$v['id'])->setField('status',3);
            }
        }

        /***************** 更新平台红包的过期状态 【当为平台发放时，会存在过期问题】 ******************************************************************/
        $platform_coupon_list = Db::name('platform_coupon')->where([['type','=',2],['status','in','1,2,3']])->field('id,end_time,status')->select();
        // 判断红包是否过期，并更新状态
        foreach ($platform_coupon_list as $k => $v) {
            if ($v['end_time'] < time()) {
                Db::name('platform_coupon')->where('id',$v['id'])->setField('status',5);
            }
        }

        /***************** 清除骑手提现申请的缓存记录  ******************************************************************/
        Cache::store('redis')->clear('rider_tx');

        /***************** 清除用户每天第一次进入小程序的缓存记录  ******************************************************************/
        Cache::store('redis')->clear('active_coupon');


        /***************** 待更新  ******************************************************************/

        


    }




    
}
