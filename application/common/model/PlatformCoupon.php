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

        // 当红包时间暂未开始时，不需发放
        foreach ($list as $k => &$v) {
            if ($v['type'] == 2 && $v['start_time'] > time()) {
                // array_splice($list,$k,1); // 删除数组元素后，新数组会自动重新建立索引
                unset($list[$k]);
            }
        }
        return $list;
    }
     
}
