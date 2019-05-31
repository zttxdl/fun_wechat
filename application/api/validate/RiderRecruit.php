<?php

namespace app\api\validate;

use think\Validate;

class RiderRecruit extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'school_id'            => 'require',
        'phone'                => 'require|regex:/^1[3456789]{1}\d{9}$/',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'school_id.require'             => '请选择意向兼职学校',
        'phone.require'                 => '手机号必填',
        'phone.regex'                   => '手机号格式不对',
    ];
}
