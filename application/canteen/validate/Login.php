<?php

namespace app\canteen\validate;

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
        'account' => ['require'],
	    'password' => 'require',
        'code' => 'require',
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
        'account.require' => '账号不能为空',
        'password.require' => '密码不能为空',
        'code.require' => '验证码不能为空',
        'old_pwd.require' => '旧密码不能为空',
        'new_pwd.require' => '新密码不能为空',
        'sure_pwd.require' => '确认密码不能为空',
    ];

    /**
     * 定义场景验证
     * 格式：'方法名'	=>	['参数1',参数2]
     *
     * @var array
     */	
    protected $scene = [
        'login'  =>  ['account','password','code'],
        'updatePwd'  =>  ['old_pwd','new_pwd','sure_pwd'],
    ];
}
