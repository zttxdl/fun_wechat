<?php

namespace app\test\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\facade\Cache;

class User extends Controller
{
    /**
     * 添加用户【测试】
     *
     */
    public function addUser(Request $request)
    {
        $data = [];
        for ($i=15001; $i <= 20000; $i++) { 
            $data[] = [
                'openid'    =>  "test_openid_".sprintf('%05d',$i),
                'nickname'  =>  'test_nickname_'.sprintf('%05d',$i),
                'headimgurl'=>  'https://thumbs.dreamstime.com/t/%E4%BA%BA%E5%8A%A8-%E7%89%87%E6%99%BA%E8%83%BD%E6%89%8B%E6%9C%BA%E6%A0%B7%E5%93%81%E6%B5%8B%E8%AF%95-83155177.jpg',
                'sex'   =>  '1',
                'phone' =>  '188888'.sprintf('%05d',$i),
                'type'  =>  1,
                'add_time'  =>  time(),
                'new_buy'   =>  1
            ];

        }
        Db::name('user')->insertAll($data);
    }



    /**
     * 测试骑手新订单提醒 
     * 
     */
    public function riderOrder($school_id)
    {
        $map1 = [
            ['school_id', '=', $school_id],
            ['open_status', '=', 1],
            ['status', '=', 3]
        ];
        // 暂未成为骑手的情况
        $map2 = [
            ['school_id', '=', $school_id],
            ['status', 'in', [0,1,2]]
        ];  

        $r_list = model('RiderInfo')->whereOr([$map1, $map2])->fetchSql()->select();
        dump($r_list);
    }


    /** 订单超时未支付相关逻辑处理 ************************************************************************************************* */
    /**
     * 存入 redis 缓存 【测试redis版】 
     * 生成订单时处理
     */
    public function order_create()
    {
        $redis = Cache::store('redis');
        $key = "order_cacle";
        $order_id = build_order_no('Test');  // 测试订单号
        $time = time() + 60; // 订单超时时间
        $redis->hSet($key, $order_id, $time);
        return 'create ok';
    }


    /**
     * 判断订单是否超时15分钟未支付 【测试redis版】 
     * 每分钟自动执行的脚本
     * 
     */
    public function order_overtime()
    {
        $redis = Cache::store('redis');
        $key = "order_cacle";

        $order_pay = $redis->hGETALL($key);

        // 如果存在订单缓存，进行下一步的时间判断
        if($order_pay) { 
           foreach ($order_pay as $k => $v) {
               if (time() > $v) {
                   // TODO 逻辑
                   # 修改该订单的状态【由未支付改为订单已取消】

                   # 商品的库存回滚【限今日特价商品（今日特价商品存在库存）】
                   
                   # 红包状态回滚【如果使用】
                   
                   # 删除该redis记录
                   $redis->hDel($key,$k);
                   return 'update ok';
               }
           }
        }

    }


    /**
     * 当支付成功后，删除该 redis 缓存 【测试redis版】  
     * 支付异步回调中处理
     * 
     */
    public function order_del($order_sn)
    {
        $redis = Cache::store('redis');
        $key = "order_cacle";
        $order = $redis->hGet($key,$order_sn);
        if ($order) {
            $redis->hDel($key,$order_sn);
            return 'del ok';
        }

    }


     /** 骑手超时未取餐 【此版本存在缺陷：1.骑手推送socket以学校为单位进行推送、2.更改订单也可批量更改】 ************************************************************************************************* */
     
     /**
      * 存储 redis  【测试redis版】  
      * 骑手抢单成功时存储
      * 
      */
    public function rider_getorder_create()
    {
    $redis = Cache::store('redis');
    $key = "rider_order_cacle";
    $order_sn = build_order_no('Rider');  // 测试骑手抢单号（外卖表号）
    $time = time() + 15*60; // 设定骑手超时时间
    $redis->hSet($key, $order_sn, $time);
    return 'create ok';
    }


    /**
     * 判断骑手是否超时还未接单 【测试redis版】  
     * 定时脚本，每分钟执行一次
     * 
     */
    public function rider_order_overtime()
    {
        $redis = Cache::store('redis');
        $key = "rider_order_cacle";

        $rider_order = $redis->hGETALL($key);

        // 如果存在订单缓存，进行下一步的时间判断
        if($rider_order) { 
           foreach ($rider_order as $k => $v) {
               if (time() > $v) {
                   // TODO 逻辑
                   # 修改该订单的状态【由已抢单改为重新抢单】

                   # 推送socket
                   
                   # 存入缓存，每个骑手最多两次取消接单情况
                   
                   # 删除该redis记录
                   $redis->hDel($key,$k);
                   return 'update ok';
               }
           }
        }
    }

       
    /**
     * 当骑手接单后，删除该 redis 缓存 【测试redis版】  
     * 在骑手接单成功时处理
     * 
     */
    public function rider_order_del($order_sn)
    {
        $redis = Cache::store('redis');
        $key = "rider_order_cacle";
        $order = $redis->hGet($key,$order_sn);
        if ($order) {
            $redis->hDel($key,$order_sn);
            return 'del ok';
        }

    }



}
