<?php

namespace app\admin\validate;

use think\Validate;

class Coupon extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'batch_id'      => 'require',
        'name'          => 'require|max:20',
        'face_value'    => 'require|number',
        'threshold'     => 'require|number',
        'num'           => 'require|number',
        'assume_ratio'  => 'require|number', 
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'batch_id.require'      => '优惠券批次ID不能为空',
        'name.require'          => '优惠券名称不能为空',
        'name.max'              => '优惠券名称不能超过20位',
        'face_value.require'     => '面额不能为空',
        'face_value.number'     => '面额格式不正确',
        'threshold.require'      => '使用门槛不能为空',
        'threshold.number'      => '使用门槛格式不正确',
        'num.require'            => '发行量不能为空',
        'num.number'            => '发行量必须为正整数',
        'assume_ratio.require'   => '商家承担比例不能为空',
        'assume_ratio.number'   => '商家承担比例必须为正整数',
    ];

}
