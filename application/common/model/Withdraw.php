<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/26
 * Time: 4:49 PM
 */

namespace app\common\model;


use think\Model;

class Withdraw extends Model
{
    /**
     * 获取账户余额
     */
    public function getAcountMoney($shop_id)
    {
        //已结算收入
        $shouru_money = $this->where([['shop_id','=',$shop_id],['status','=',1],['type','=',1],['add_time','<',strtotime(date('Y-m-d')) ]])
            ->sum('money');

        //提现过程中的金额【包括 `已提现`，`申请提现`】
        $tx_money = $this->where([['shop_id','=',$shop_id],['type','=',2],['status','in','1,3']])->sum('money');

        $acount_money = $shouru_money + $tx_money;

        return $acount_money;
    }

}