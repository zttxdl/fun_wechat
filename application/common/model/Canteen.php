<?php

namespace app\common\model;

use think\Model;
use think\Db;

class Canteen extends Model
{
	/**
	 * 获取食堂名称
	 */
	public function getCanteenName($canteen_id)
	{
		$name = $this->where('id',$canteen_id)->value('name');
		return isset($name) ? $name : '';
	}


	/**
	 * 食堂收支明细模型关联
	 */
	public function canteenIncomeExpend()
    {
        return $this->hasOne('CanteenIncomeExpend','canteen_id');
    }

    /**
     * 获取食堂账户余额
     * @param $canteen_id
     * @param string $startTime 提现周期
     * @return string
     */
    public function getAcountMoney($canteen_id,$startTime='')
    {
        // 提现规则 7天前 [在测试阶段，设置10分钟之前]
        if($startTime) {
            $withdraw_cycle = Db::name('canteen')->where('id',$canteen_id)->value('withdraw_cycle');
            $startTime = date('Y-m-d',strtotime("-".$withdraw_cycle. "days")).'23:59:59';
        }
        // $startTime = time()-600;
        //收入
        $shouru_money = $this->getIncome($canteen_id,$startTime);

        //支出
        $zc_money = $this->getExpenditure($canteen_id,$startTime);

        //账户余额等于收入-支出
        $acount_money = $shouru_money - $zc_money;

        return sprintf("%.2f",$acount_money);
    }

    /**
     * 获取食堂收入
     * @param $canteen_id
     * @param string $startTime
     * @return string
     */
    public function getIncome($canteen_id,$startTime='')
    {
        if($startTime) {
            $shouru_money = Db::name('CanteenIncomeExpend')->where([['canteen_id','=',$canteen_id],['type','=',1]])
                ->whereTime('add_time', '<',$startTime)
                ->sum('money');
        }else{
            $shouru_money = Db::name('CanteenIncomeExpend')->where([['canteen_id','=',$canteen_id],['type','=',1]])
                ->sum('money');
        }

        return sprintf("%.2f",$shouru_money);
    }


    /**
     * 获取食堂支出
     * @param $canteen_id
     * @param string $startTime 提现周期
     * @return string
     */
    public function getExpenditure($canteen_id,$startTime='')
    {
        //提现过程中的金额【包括 `已提现`，`申请提现`】支出计算时间
        $tx_money = Db::name('CanteenIncomeExpend')->where([['canteen_id','=',$canteen_id],['type','=',2],['status','in','1,3']])
            ->sum('money');
        if($startTime)  {
            //总支出 过滤提现
            $zc_money = Db::name('CanteenIncomeExpend')->where([['canteen_id','=',$canteen_id],['type','=','3']])
                ->whereTime('add_time', '<',$startTime)
                ->sum('money');
        }else{
            //总支出 过滤提现
            $zc_money = Db::name('CanteenIncomeExpend')->where([['canteen_id','=',$canteen_id],['type','=','3']])
                ->sum('money');
        }
        return sprintf("%.2f",$tx_money + $zc_money);
    }



}