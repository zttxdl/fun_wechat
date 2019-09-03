<?php

namespace app\canteen\validate;

use think\Validate;

class Account extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'old_pwd' => 'require',
        'new_pwd' => 'require',
        'sure_pwd' => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'old_pwd.require' => '旧密码不能为空',
        'new_pwd.require' => '新密码不能为空',
        'sure_pwd.require' => '确认密码不能为空'
    ];
}
