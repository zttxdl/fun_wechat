<?php

namespace app\merchants\validate;

use think\Validate;

class Login extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	// 验证规则
    protected $rule = [
        'account' => 'require|max:11|/^1[3456789]{1}\d{9}$/',
        'password'    => 'require|min:8|max:20',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
      'account.require' => '手机号必填',
      'account.max' => '最多不能超过11位',
      'account./^1[3456789]{1}\d{9}$/' => '格式错误',
      'password.require'   => '密码必填',
      'password.min'   => '最少不能低于8位',
      'password.max'   => '最多不能超过20位',
    ];
}
