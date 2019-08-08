<?php

namespace app\admin\validate;

use think\Validate;

class Canteen extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
    protected $rule = [
        'name'      => 'require|max:30',
        'cut_proportion'    => 'require|number',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'      => '食堂名称不能为空',
        'name.max'              => '食堂名称不能超过30位',
        'cut_proportion.require'     => '食堂抽成不能为空',
        'cut_proportion.number'     => '食堂抽成为正整数',
    ];
}
