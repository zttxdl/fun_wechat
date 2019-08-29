<?php


namespace app\common\model;


use think\Model;

class ShopDiscounts extends Model
{
	// 获取商家优惠券信息
	public function getDiscountsList($shopId)
	{
		$list = $this->field('face_value,threshold')
			->where('shop_id',$shopId)
			->where('delete',0)
			->order('threshold','asc')
			->select();
		return $list;
	}
}