<?php

namespace app\admin\validate;

use think\Validate;

class Role extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'name'      => 'require|max:20|unique:role',
        'depict'    => 'require',
        'node_ids'  => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'     => '角色名必填',
        'name.max'         => '角色名不能超过20位',
        'name.unique'      => '角色名不能重复',
        'depict.require'   => '描述必填',
        'node_ids.require' => '菜单节点必选',
    ];
}
