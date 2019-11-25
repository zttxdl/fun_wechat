<?php

namespace app\admin\validate;

use think\Validate;

class ShopDiscounts extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'shop_id'    => 'require|number',
        'threshold'          => 'require|number',
        'face_value'      => 'require|number|elt:threshold',
        'platform_assume'    => 'number|elt:face_value',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'shop_id.require'     => '店铺主键值不能为空',
        'shop_id.number'     => '店铺主键值格式不正确',
        'face_value.require'     => '满减金额不能为空',
        'face_value.number'     => '满减金额格式不正确',
        'face_value.elt'  => '满减金额应小于或等于满减门槛',
        'threshold.require'      => '满减门槛不能为空',
        'threshold.number'      => '满减门槛格式不正确',
        'platform_assume.number' => '平台承担格式不正确',
        'platform_assume.elt'  => '平台承担额应小于或等于满减金额',
    ];
}
