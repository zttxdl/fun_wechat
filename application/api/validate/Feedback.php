<?php

namespace app\api\validate;

use think\Validate;

class Feedback extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'content'          => 'require|max:200',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'content.require'          => '反馈内容必填',
        'content.max'              => '反馈内容不能超过200位',
    ];
}
