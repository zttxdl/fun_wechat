<?php

namespace app\admin\validate;

use think\Validate;

class School extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'name'      => 'require|max:30',
        'longitude'      => 'require',
        'latitude'      => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'      => '学校名称不能为空',
        'name.max'          => '学校名称不能超过30位',
        'longitude.require' => '经度必传',
        'latitude.require'  => '纬度必传',
    ];
}
