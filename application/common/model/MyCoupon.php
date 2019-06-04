<?php

namespace app\common\model;

use think\Model;

class Coupon extends Model
{
    //获取单条优惠券记录
    public function getOneCoupon($coupon_cdoe)
    {
        $data = Db::name('coupon')->where('coupon_code',$coupon_cdoe)->find();
        return $data;
    }

    /**
     * 获取优惠券详情
     */
    public function getCouponDetail($coupon_code)
    {

    }

    /**
     * 获取优惠券信息
     */
    public function getCouponInfo($coupon_code)
    {

    }

}
