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
     * 获取当前用户的首单红包
     */
    public function getUserCoupon($pt_coupon_ids,$uid)
    {
        $list = Db::name('my_coupon m')
                        ->join('platform_coupon p','m.platform_coupon_id = p.id')
                        ->where([['m.platform_coupon_id','in',$pt_coupon_ids],['m.user_id','=',$uid]])
                        ->field('p.face_value,p.threshold,p.shop_ids')
                        ->select();
        return $list;
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
    public function updateStatus($id,$data)
    {
        return $this->where('id',$id)->setField($data);
    }


    /**
     * 优惠券的使用情况
     * 
     */
    public function getStatusAttr($value,$data)
    {
        $status = ['1' => '未使用','2' => '已使用','3' => '已过期',];
        return $status[$data['status']];
    }


}
