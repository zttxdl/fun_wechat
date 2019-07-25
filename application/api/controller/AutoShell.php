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
            if ($indate_time < (time() - 10)) {   // 为防止网络延时，将时间延后10秒
                Db::name('my_coupon')->where('id',$v['id'])->setField('status',3);
            }
        }

        /***************** 更新平台红包的过期状态 【当为平台发放时，会存在过期问题】 ******************************************************************/
        $platform_coupon_list = Db::name('platform_coupon')->where([['type','=',2],['status','in','1,2,3']])->field('id,end_time,status')->select();
        // 判断红包是否过期，并更新状态
        foreach ($platform_coupon_list as $k => $v) {
            if ($v['end_time'] < (time() - 10)) {  // 为防止网络延时，将时间延后10秒
                Db::name('platform_coupon')->where('id',$v['id'])->setField('status',5);
            }
        }

        /***************** 清除骑手提现申请的缓存记录  ******************************************************************/
        Cache::store('redis')->clear('rider_tx');

        /***************** 清除用户每天第一次进入小程序的缓存记录  ******************************************************************/
        Cache::store('redis')->clear('active_coupon');

        /***************** 清除用户每天商户今日访客量  ******************************************************************/
        Cache::store('redis')->rm('shop_uv_count');

        /***************** 清除用户每天商户的取餐单号  ******************************************************************/
        Cache::store('redis')->rm('shop_meal_sn');


        /***************** 待更新  ******************************************************************/

        


    }

    /**
     * 超时订单自动取消 付款减库存需要加上库存（每分钟/次）
     */
    public function cancelOrders()
    {
        $orderlist=Db::table('fun_orders')->where('add_time','<',time()-15*60)->where('pay_status',0)->where('status',1)->select();

        foreach ($orderlist as $k => $v) {
            Db::table('fun_orders')->where('id',$v['id'])->update(['trading_closed_time'=>time(),'status'=>9]);
            //付款减库存的商品
            $goodslist=Db::table('fun_orders_info')->where('orders_id',$v['id'])->field('product_id,num')->select();

            //如果使用红包 状态回滚
            if($v['platform_coupon_money'] > 0){
                Db::table('fun_my_coupon')->where('id',$v['platform_coupon_id'])->setField('status',1);

            }

            foreach ($goodslist as $key => $value) {
                $today = date('Y-m-d',time());
                //加库存
                Db::table('fun_today_deals')
                    ->where('product_id',$value['product_id'])
                    ->where('today',$today)
                    ->setInc('num',$value['num']);
            }
        }

        $num = count($orderlist);

        echo $num;
    }


    
}
