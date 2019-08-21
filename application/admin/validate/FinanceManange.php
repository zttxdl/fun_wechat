<?php

namespace app\admin\validate;

use think\Validate;

class FinanceManange extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'status'      => 'require',
        'source'    => 'require',
        'id'    => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'status.require'      => '审核状态不能为空',
        'source.require'     => '来源不能为空',
        'id.require'     => '提现ID不能为空',
    ];
}
