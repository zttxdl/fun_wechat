<?php

namespace app\common\model;

use think\Model;

class TodayDeals extends Model
{

    /**
     * 获取今日特价主键
     */

    public function  getTodayProduct($shop_id)
    {
        $today = date('Y-m-d',time());
        $where[] = ['today','=',$today];
        $where[] = ['shop_id','=',$shop_id];
        $where[] = ['end_time','>=',time()];
        $where[] = ['start_time','<=',time()];
        $id = $this->where($where)->value('product_id');

        return $id;
    }

    /**
     * 今日特价商品库存修改
     */
    public function updateTodayProductNum($shop_id, $desc,$id)
    {

        $today = date('Y-m-d',time());
        $where[] = ['today','=',$today];
        $where[] = ['shop_id','=',$shop_id];
        $where[] = ['product_id','=',$id];

       if($desc == 'inc') {//加库存
           $this->where($where)->setInc('num',1);
       }else{
           $this->where($where)->where('num','>',0)->setDec('num',1);
       }

        return true;

    }
}
