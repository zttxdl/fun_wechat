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
        $list = Db::name('my_coupon')->field('id,indate,status')->select();
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
        Cache::store('redis')->del('rider_tx_num');

        /***************** 清除骑手可提现金额的缓存记录  ******************************************************************/
        Cache::store('redis')->del('rider_can_tx_money');

        /***************** 清除用户每天第一次进入小程序的缓存记录 【此功能后续删除】   ******************************************************************/
        // Cache::store('redis')->del('homepage_active_coupon');

        /***************** 清除用户每天商户今日访客量  ******************************************************************/
        Cache::store('redis')->del('shop_uv_count');

        /***************** 清除用户每天商户的取餐单号  ******************************************************************/
        Cache::store('redis')->del('shop_meal_sn');

        /***************** 清除商家提现申请的缓存记录  ******************************************************************/
        Cache::store('redis')->del('shop_tx_key');

        /***************** 清除商家可提现金额的缓存记录  ******************************************************************/
        Cache::store('redis')->del('shop_balance_key');

        /***************** 清除骑手抢单超时未取餐记录  ******************************************************************/
        Cache::store('redis')->del('rider_overtime_number');

        /***************** 清除统计店铺日订单量记录  ******************************************************************/
        Cache::store('redis')->del('shop_day_order_count');

        /***************** 清除统计店铺日取消订单量记录  ******************************************************************/
        Cache::store('redis')->del('shop_day_cancel_order_count');

        /***************** 清除每天的用户活跃身份  ******************************************************************/
        Cache::store('redis')->del('user_active_openid');

        /***************** 清除每天的创业加盟统计量  ******************************************************************/
        Cache::store('redis')->del('user_join_us_count');

        /***************** 待更新  ******************************************************************/

        


    }

    /**
     * 超时订单自动取消 付款减库存需要加上库存（每分钟/次）
     */
    public function cancelOrders_mysql()
    {
        $orderlist=Db::table('fun_orders')->where('add_time','<',time()-15*60)->where('pay_status',0)->where('status',1)->select();

        foreach ($orderlist as $k => $v) {
            Db::table('fun_orders')->where('id',$v['id'])->update(['trading_closed_time'=>time(),'status'=>9]);
            //付款减库存的商品
            $goodslist=Db::table('fun_orders_info')->where('orders_id',$v['id'])->field('product_id,num')->select();

            //如果使用红包 状态回滚
            if($v['platform_coupon_money'] > 0){
                $my_coupon_id = model('MyCoupon')->where([['user_id','=',$v['user_id']],['platform_coupon_id','=',$v['platform_coupon_id']]])->value('id');
                Db::table('fun_my_coupon')->where('id',$my_coupon_id)->setField('status',1);

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


    /**
     * 测试redis 超时15分钟取消订单 【2019-11-14更新】
     */
    public function cancelOrders()
    {
        $redis = Cache::store('redis');
        $redis_key = "order_cacle";

        $order_cancel = $redis->hGETALL($redis_key);
        // 如果存在订单缓存，进行下一步的时间判断
        if($order_cancel) { 
           foreach ($order_cancel as $k => $v) {
               if (time() > $v) {
                   // TODO 逻辑
                   $info = Db::name('orders')->where('orders_sn','=',$k)->find();
                   
                   # 修改该订单的状态【由未支付改为订单已取消】
                   Db::name('orders')->where('id',$info['id'])->update(['trading_closed_time'=>time(),'status'=>9]);

                   // 红包状态回滚【如果使用】
                   if($info['platform_coupon_money'] > 0){
                       $my_coupon_id = Db::name('my_coupon')->where([['user_id','=',$info['user_id']],['platform_coupon_id','=',$info['platform_coupon_id']]])->value('id');
                       Db::name('my_coupon')->where('id',$my_coupon_id)->setField('status',1);
                    }
                    
                    # 商品的库存回滚【限今日特价商品（今日特价商品存在库存）】
                    $goodslist=Db::name('orders_info')->where('orders_id',$info['id'])->field('product_id,num')->select();
                    foreach ($goodslist as $key => $value) {
                       $today = date('Y-m-d',time());
                       // 加库存
                       Db::name('today_deals')
                           ->where('product_id',$value['product_id'])
                           ->where('today',$today)
                           ->setInc('num',$value['num']);
                    }

                   # 删除该redis记录
                   $redis->hDel($redis_key,$k);
               }
           }
        }
    }

    /**
     * 更新广告的状态
     */
    public function updateAdvert()
    {
        $list = Db::name('advert')
            ->field('id,start_time,end_time,status')
            ->where('status','<>',3)
            ->select();

        foreach ($list as $item) {
            if ($item['start_time'] < time() && $item['status'] == 0){          // 设置广告已开启
                Db::name('advert')->where('id','=',$item['id'])->update(['status'=>1]);
            }
            if ($item['end_time'] < time()){
                Db::name('advert')->where('id','=',$item['id'])->update(['status'=>3]);     // 设置广告已过期
            }
        }

        echo 'success';
    }


    /**
     * 食堂余额更新
     * @return [type] [description]
     */
    public function canteen()
    {
        $list = model('Canteen')->field('id,withdraw_cycle')->where('id',2)->select();
        foreach ($list as $val) {
            $balance = model('CanteenIncomeExpend')->getAcountMoney($val->id,$val->withdraw_cycle);
            model('Canteen')->where('id',$val->id)->update(['can_balance'=>$balance]);
        }
        echo "success";
    }


    /**
     * 骑手抢完单后，超过指定时间还未到商家取餐
     */
    public function riderOvertimeOrder()
    {
        $orderlist=Db::name('takeout')->where([['fetch_time','<',time()],['status','=',3]])->field('id,school_id,rider_id')->select();

        // 没有数据时
        if (!$orderlist) {
            return false;
        }
        
        $school_ids = array_unique(array_column($orderlist,'school_id'));
        $takeout_ids = array_column($orderlist,'id');
        $rider_ids = array_column($orderlist,'rider_id');
        //实例化socket
        $socket = model('PushEvent','service');
        // 订单返回到骑手抢单状态
        $res = Db::name('takeout')->where('id','in',$takeout_ids)->update(['status'=>1,'single_time'=>0]);

        // 推送socket
        foreach ($school_ids as $kk => $vv) {
            // 已成为骑手的情况
            $map1 = [
                ['school_id', '=', $vv],
                ['open_status', '=', 1],
                ['status', '=', 3]
            ];
            // 暂未成为骑手的情况
            $map2 = [
                ['school_id', '=', $vv],
                ['status', 'in', [0,1,2]]
            ];  

            $r_list = model('RiderInfo')->whereOr([$map1, $map2])->select();

            foreach ($r_list as $item) {
                $rid = 'r'.$item->id;
                $socket->setUser($rid)->setContent('refresh')->push();
            }
        }

        // 存入缓存，每个骑手最多两次取消接单情况【2019-11-25 此功能暂时去除】
        // $redis = Cache::store('redis');
        // $key = "rider_overtime_number";
        // foreach ($rider_ids as $k => $v) {
        //     if ($redis->hExists($key,$v)) {
        //         $redis->hIncrby($key,$v,1);
        //     } else {
        //         $redis->hSet($key,$v,1);
        //     }
        // }

    }
}
