<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/6/4
 * Time: 10:08 AM
 */
namespace app\common\model;

use think\Model;

class Invitation extends Model
{

    //获取邀请人数量
    public function getUserCount($id)
    {
        $num = $this->where('referee_user_id', $id)->count();
        return $num;
    }

    //获取红包收益数量
    public function getLuckyMoney($id)
    {
        $money = $this->where('referee_user_id', $id)->sum('lucky_money');

        return $money;

    }
}