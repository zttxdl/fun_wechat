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
        'back_name'          => 'require',
        'name'         => 'require',
        'back_num'     => 'require',
        'canteen_id'     => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'back_name.require'             => '开户行不能为空',
        'name.require'                 => '开户人姓名不能为空',
        'back_num.require'                   => '银行卡号不能为空',
        'canteen_id.require'                   => '用户id不能为空',
    ];
}
