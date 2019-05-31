<?php

namespace app\common\model;

use think\Model;

class ShopInfo extends Model
{
    //获取周边3公里的商家
	public function getDistance($lat,$lng,$page=0,$pagesize=15)
    {

        $list = $this->field("id,shop_name,marks,sales,up_to_send_money,run_time,
            address,manage_category_id,ping_fee,ROUND(6371 * acos (cos ( radians($lat)) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( $lng) ) + sin ( radians( $lat) ) * sin( radians( latitude ) ) ),2 ) AS distance ")
            ->having('distance < 3')
            ->page($page,$pagesize)
            ->select()
            ->toArray();

        return $list;

    }
}