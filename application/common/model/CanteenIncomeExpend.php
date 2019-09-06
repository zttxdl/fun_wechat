<?php

namespace app\common\model;

use think\Model;

class CanteenIncomeExpend extends Model
{
	// 定义的 3 种状态
    const STATUS_FUNDING = 1;
    const STATUS_FAIL = 2;
    const STATUS_SUCCESS = 3;

    public static $statusMap = [
        self::STATUS_FUNDING   => '待审核',
        self::STATUS_FAIL => '提现失败',
        self::STATUS_SUCCESS => '提现成功',
    ];

     /**
     * @param $canteen_id 食堂ID
     * @param string $cycle 多少天以前开始计算的时间
     * @return string
     * 获取账户余额【可提现金额】
     */
    public function getAcountMoney($canteen_id,$cycle)
    {
        // 提现规则
        $startTime = date('Y-m-d',strtotime("-$cycle days")).'23:59:59';
        //收入
        $shouru_money = $this->getIncome($canteen_id,$startTime);
        //支出
        $zc_money = $this->getExpenditure($canteen_id,$startTime);

        //账户余额等于收入-支出
        $acount_money = $shouru_money - $zc_money;

        return sprintf("%.2f",$acount_money);
    }


    /**
     * 支出
     * @param $canteen_id
     * @param $startTime 开始时间
     * @param string $endTime 结束时间
     * @return string
     */
    public function getExpenditure($canteen_id,$startTime)
    {
        //提现过程中的金额【包括 `已提现`，`申请提现`】支出计算时间
        $tx_money = $this->where([['canteen_id','=',$canteen_id],['type','=',2],['status','in','1,3']])
            ->sum('money');

        //总支出 过滤提现
        $zc_money = $this->where([['canteen_id','=',$canteen_id],['type','=',3]])
        	->whereTime('add_time', '<',$startTime)
            ->sum('money');
            
        return sprintf("%.2f",$tx_money + $zc_money);
    }


    /**
     * 收入
     * @param $canteen_id
     * @param $startTime 开始时间
     * @param string $endTime 结束时间
     * @return string
     */
    public function getIncome($canteen_id,$startTime)
    {
        $shouru_money = $this->where([['canteen_id','=',$canteen_id],['type','=',1]])
            ->whereTime('add_time', '<',$startTime)
            ->sum('money');
        
        return sprintf("%.2f",$shouru_money);
    }

}
