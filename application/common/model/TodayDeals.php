<?php

namespace app\common\model;

use think\facade\Cache;
use think\Model;
use think\Db;

class TodayDeals extends Model
{

    /**
     * 今日特价商品库存修改
     */
    public function updateTodayProductNum($shop_id, $desc)
    {
            $redis = Cache::store('redis');
            $redisKey = 'TodayDeals';
            $today = date('Y-m-d',time());

            $today_goods = $redis->hGet($redisKey,$shop_id);

            $data = json_decode($today_goods,true);

            if(!$data) {
                $data = $this
                    ->where('shop_id',$shop_id)
                    ->where('today',$today)
                    ->find();
            }


            if($data && $data['today'] == $today) {
                
               $db = $this
                    ->where('shop_id',$shop_id)
                    ->where('today',$today);

               if($desc == 'inc') {//加库存
                    $db->setInc('num',1);
               }else{
               		$db->setDec('num',1);
               }
                return true;
            }
            return false;
    }
}
