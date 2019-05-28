<?php

namespace app\admin\validate;

use think\Validate;

class Login extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'phone' => ['require','regex'=>'^1\d{10}$'],
	    'password' => 'require',
	    'code' => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'phone.require' => '手机号不能为空',
        'phone.regex' => '手机号格式不正确',
        'password.require' => '密码不能为空',
        'code.require' => '验证吗不能为空'
    ];
}
