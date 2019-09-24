<?php

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'name'                 => 'require|max:10|unique:admin', //  unique:表名
        'phone'                => 'require|regex:/^1[3456789]{1}\d{9}$/|unique:admin',
        'role_id'               => 'require',
        'password'              => 'require|min:8|max:20|confirm',
        'password_confirm'      => 'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'                  => '管理员名称必填',
        'name.max'                      => '管理员名称不能超过10位',
        'name.unique'                   => '管理员名称不能重复',
        'phone.require'                 => '手机号必填',
        'phone.regex'                   => '手机号格式不对',
        'phone.unique'                  => '手机号不能重复',
        'role_id.require'               => '请选择管理员角色',
        'password.require'               => '密码不能为空',
        'password.min'                  => '请设置8-20位的密码',
        'password.max'                  => '请设置8-20位的密码',
        'password.confirm'               => '两次密码不一致',
    ];

    protected $scene = [
        'edit'  =>  ['name','phone','role_id'],
        'pwd'  =>  ['password','password_confirm']
    ];
}
