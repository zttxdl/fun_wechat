<?php

namespace app\admin\validate;

use think\Validate;

class Advers extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'name'      => 'require|max:30',
        'link_url'  => 'require',
        'img'       => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'link_url.require'  => '广告指向链接不能为空',
        'name.require'      => '广告名称不能为空',
        'name.max'          => '广告名称不能超过30位',
        'img.require'       => '请上传广告图片',
    ];
}
