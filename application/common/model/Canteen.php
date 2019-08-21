<?php

namespace app\common\model;

use think\Model;

class Canteen extends Model
{
	public function getCanteenName($canteen_id)
	{
		$name = $this->where('id',$canteen_id)->value('name');
		return isset($name) ? $name : '';
	}
}