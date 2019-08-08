<?php

namespace app\admin\validate;

use think\Validate;

class AdvertPosition extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'name'      => 'require|max:30',
        'white'    => 'require|number',
        'height'    => 'require|number',
        'num'    => 'require|number',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'      => '广告位名称不能为空',
        'name.max'              => '广告位名称不能超过30位',
        'white.require'     => '广告位宽度不能为空',
        'white.number'     => '广告位宽度为正整数',
        'height.require'     => '广告位高度不能为空',
        'height.number'     => '广告位高度为正整数',
        'num.require'     => '广告位数量不能为空',
        'num.number'     => '广告位数量为正整数',
    ];
}
