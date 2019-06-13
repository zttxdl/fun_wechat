<?php

namespace app\api\validate;

use think\Validate;

class MerchantEnter extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'school_id'            => 'require',
        'manage_category_id'   => 'require',
        'name'                 => 'require|max:10',
        'phone'                => 'require|regex:/^1[3456789]{1}\d{9}$/',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'school_id.require'             => '请选择开店所在学校',
        'manage_category_id.require'    => '请选择经营品类',
        'name.require'                  => '联系人必填',
        'name.max'                      => '联系人不能超过10位',
        'phone.require'                 => '手机号必填',
        'phone.regex'                   => '手机号格式不对',
    ];
}
