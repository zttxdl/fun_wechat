<?php

namespace app\admin\validate;

use think\Validate;

class Agreement extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'title'     =>'require|max:20',
        'content'     =>'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'title.require'      => '协议标题不能为空',
        'title.max'          => '协议标题不能超过20位',
        'content.require'       => '协议内容不能为空',
    ];
}
