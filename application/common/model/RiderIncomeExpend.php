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
        return $this->where([['rider_id','=',$id],['type','=',2],['status','in','1,3']])->sum('current_money');

    }


    /**
     * 未结算收入[今日订单为未结算收入，也称‘待结算金额’ ] 
     * 
     */
    public function getNotJsMoney($id)
    {
        return $this->where([['rider_id','=',$id],['type','=',1],['status','=',0],['add_time','>=',strtotime(date('Y-m-d'))]])->sum('current_money');
    }


    /**
     * 总配送单数 
     * 
     */
    public function allNums($id)
    {
        return $this->where([['rider_id','=',$id],['type','=',1]])->count('id');

    }


    /**
     * 本月配单数 
     * 
     */
    public function mouthNums($id)
    {
        // 本月第一天：
        $start = date('Y-m-01', strtotime(date("Y-m-d")));
        // 本月最后一天：
        $end= date('Y-m-d', strtotime("$start +1 month -1 day"));

        return $this->where([['rider_id','=',$id],['type','=',1]],['add_time','between',[strtotime($start),strtotime($end)]])->count('id');
        
    }


    /**
     * 获取提现处理状态【方法很好用，但是前段需要status的原始数值，所以需要另写一个字段来标注】
     * 
     */
    public function getMbStatusAttr($value,$data)
    {
        $status = ['1' => '待审核','2' => '已拒绝','3' => '已提现'];
        return $status[$data['status']];
    }

     
     

    
     
}
