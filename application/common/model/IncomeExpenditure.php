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
    public function getBalanceMoney($shop_id)
    {
        return $this->where('shop_id',$shop_id)->limit(1)->order('add_time','DESC')->value('balance_money');
    }

    /**
     * 更新商家收支明细
     */
    public function addIncomeExpenditure($orders_sn)
    {
        $orders = model('orders')->where('orders_sn',$orders_sn)->find();

        //更新商家收支明细
        $init_money = $this->getBalanceMoney($orders['shop_id']);

        if(empty($init_money)) {
            $init_money = '0.00';
        }

        $data = [
            'shop_id' => $orders['shop_id'],
            'current_money' => $orders['money'],
            'type' => 1,
            'balance_money' => $init_money + $orders['money'],
            'serial_number' => $orders['orders_sn'],
            'add_time' => time()
        ];
        return $this->add($data);
    }

}