<?php

namespace app\common\model;

use think\Model;

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

}