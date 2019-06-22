<?php

namespace app\common\model;

use think\Model;

class PlatformCoupon extends Model
{
    /**
     * 获取当前学校下的可用的平台红包或自主领取红包列表 
     * 
     */
    public function getSchoolCouponList($school_id)
    {
        $list = $this->where('school_id','=',$school_id)->where('status','=',2)->where('type','in','1,2')->where('surplus_num','>',0)
                ->field('id,face_value,threshold,start_time,end_time,other_time,type,name,coupon_type')
                ->select()->toArray();

        return $list;
    }
     
}
