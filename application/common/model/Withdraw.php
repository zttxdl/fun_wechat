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
        $data = $this->where('shop_id',$shop_id)
            ->select()->toArray();

        $acount_money = 0;
        foreach ($data as $key=>$row)
        {
            if($row['type'] == 2) {//支出
                if(in_array($row['status'],[1,2])) {
                    unset($data[$key]);
                }
            }

        }

        foreach ($data as $row)
        {
            $acount_money += $row['money'];
        }

        return $acount_money;
    }

}