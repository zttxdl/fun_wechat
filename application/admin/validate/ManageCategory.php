<?php

namespace app\admin\validate;

use think\Validate;

class ManageCategory extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'name'      => 'require|max:10',
        'sort'    => 'require|number',
        'img'          => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'      => '品类名称不能为空',
        'name.max'              => '品类名称不能超过10位',
        'sort.require'     => '品类排序不能为空',
        'sort.number'     => '品类排序为正整数',
        'img.require'      => '请上传品类图标',
    ];
}
