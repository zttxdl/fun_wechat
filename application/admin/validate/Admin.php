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
        'name'                 => 'require|max:10',
        'phone'                => 'require|regex:/^1[3456789]{1}\d{9}$/',
        'role_id'                => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'                  => '联系人必填',
        'name.max'                      => '联系人不能超过10位',
        'phone.require'                 => '手机号必填',
        'phone.regex'                   => '手机号格式不对',
        'role_id.require'               => '请选择管理员角色',
    ];
}
