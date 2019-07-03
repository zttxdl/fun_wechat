<?php

namespace app\common\model;

use think\Model;

class ShopInfo extends Model
{
    //获取周边5公里的学校
	public function getDistance($lat,$lng,$page=1,$pagesize=15)
    {
        set_log('modellat==',$lat,'index');
        set_log('modellng==',$lng,'index');

        $data = model('School')->field("id,name,ROUND(6371 * acos (cos ( radians($lat)) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( $lng) ) + sin ( radians( $lat) ) * sin( radians( latitude ) ) ),1 ) AS distance ")
            ->having('distance < 5')
            ->where('level',2)
            ->order('distance asc')
            ->find();

        if ($data){
            $list = $this->field("id,shop_name,logo_img,marks,sales,up_to_send_money,run_time,
            address,manage_category_id,ping_fee,school_id")
                ->where('school_id',$data->id)
                ->page($page,$pagesize)
                ->select()
                ->toArray();

            return $list;
        }else{
            return false;
        }

    }

    //获取商家营业状态
    public function getBusiness($run_time)
    {
        $arr = explode(',',$run_time);
        $count = count($arr);
        $day = date('H:i');
        $business = 0;
        if ($count == 1){
            $date = explode('-',$arr[0]);
            if($date[0] < $day && $date[1] > $day){
                $business = 1;
            }

        }elseif ($count == 2) {
            $date = explode('-',$arr[0]);
            if($date[0] < $day && $date[1] > $day){
                $business = 1;
            }

            $date1 = explode('-',$arr[1]);

            if($date1[0] < $day && $date1[1] > $day){
                $business = 1;
            }

        }elseif ($count == 3){
            $date = explode('-',$arr[0]);
            if($date[0] < $day && $date[1] > $day){
                $business = 1;
            }

            $date1 = explode('-',$arr[1]);

            if($date1[0] < $day && $date1[1] > $day){
                $business = 1;
            }

            $date2 = explode('-',$arr[2]);

            if($date2[0] < $day && $date2[1] > $day){
                $business = 1;
            }
        }

        return $business;
    }

    /**
     * 获取店铺销售总额
     */
    public function getCountSales($shop_id)
    {
        $data = $this->name('orders')->where('status',8)
            ->where('shop_id',$shop_id)->fetchSql()
            ->sum('money');

        return $data;

    }
}