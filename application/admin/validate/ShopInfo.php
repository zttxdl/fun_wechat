<?php

namespace app\admin\validate;

use think\Validate;

class ShopInfo extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'segmentation'   => 'require',
        'price_hike'     => 'require',
        'sort'     => 'require',
        'withdraw_cycle'     => 'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'segmentation.require'  => '平台抽成不能为空',
        'price_hike.require'  => '商家调价不能为空',
        'sort.require'  => '商家排序不能为空',
        'withdraw_cycle.require'  => '提现周期不能为空',
    ];
}
