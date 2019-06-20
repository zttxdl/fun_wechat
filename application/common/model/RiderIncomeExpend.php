<?php

namespace app\common\model;

use think\Model;

class RiderIncomeExpend extends Model
{
    /**
     * 已结算收入 
     * 
     */
    public function getAlreadyTxMoney($id)
    {
        return $this->where([['rider_id','=',$id],['type','=',2],['status','=',2]])->sum('current_money');
    }
    

    /**
     * 可提现金额 
     * 
     */
    public function getCanTxMoney($id)
    {
        return $this->where([['rider_id','=',$id],['type','=',1],['status','=',0],['add_time','<',time()-3600*24*7]])->sum('current_money');
    }


    /**
     * 未结算收入 
     * 
     */
    public function getNotTxMoney($id)
    {
        return $this->where([['rider_id','=',$id],['type','=',1],['status','=',0],['add_time','>=',time()-3600*24*7]])->sum('current_money');
    }

    
     
}
