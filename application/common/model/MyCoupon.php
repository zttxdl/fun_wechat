<?php

namespace app\common\model;

use think\Model;
use think\Db;

class MyCoupon extends Model
{
    //获取单条优惠券记录
    public function getOneCoupon($id)
    {
        $data = $this->where('id',$id)->find();
        return $data;
    }

    /**
     * 获取优惠券详情
     */
    public function getCouponDetail($id)
    {

    }

    /**
     * 获取优惠券信息
     */
    public function getCouponInfo($id)
    {

    }

    /**
     * 新增优惠券信息
     */
    public function addCouponInfo($data){
        return $this->insert($data);
    }

    /**
     * 获取红包记录
     */
    public function getHongbao($id)
    {
        return $this->where('id',$id)->find();
    }

    /**
     * 红包状态变更
     */
    public function updateStatus($id,$status)
    {
        return $this->where('id',$id)->setField('status',$status);
    }


}
