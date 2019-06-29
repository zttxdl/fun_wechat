<?php

namespace app\common\model;

use think\Model;

class RiderIncomeExpend extends Model
{
    /**
     * 已结算收入[今天之前的所有收入] 
     * 
     */
    public function getAlreadyJsMoney($id)
    {
        return $this->where([['rider_id','=',$id],['type','=',1],['status','=',0],['add_time','<',strtotime(date('Y-m-d')) ]])->sum('current_money');

    }
    

    /**
     * 提现过程中的金额【包括 `已提现`，`申请提现`】 
     * 
     */
    public function getTxMoney($id)
    {   
        return $this->where([['rider_id','=',$id],['type','=',2],['status','in','1,2']])->sum('current_money');

    }


    /**
     * 未结算收入[今日订单为未结算收入] 
     * 
     */
    public function getNotJsMoney($id)
    {
        return $this->where([['rider_id','=',$id],['type','=',1],['status','=',0],['add_time','>=',strtotime(date('Y-m-d'))]])->sum('current_money');
    }

    
     
}
