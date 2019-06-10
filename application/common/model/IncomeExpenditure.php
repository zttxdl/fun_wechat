<?php


namespace app\common\model;


use think\Model;

class IncomeExpenditure extends Model
{
    /**
     * 我的资产
     * @param $shop_id
     */
    public function index($shop_id)
    {
        return $this->where('shop_id',$shop_id)->find();
    }

    /**
     * 添加商家收入明细
     * @param $data
     */
    public function add($data)
    {
        return $this->insert($data);
    }

    /**
     * 获取账户余额
     */
    public function getBalanceMoney()
    {

    }
}