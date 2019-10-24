<?php

namespace app\merchants\validate;

use think\Validate;

class JgDevice extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        // 'shop_id'                => 'require|unique:jg_device',//  unique:表名
        'device_type'               => 'require',
        'device_sn'              => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        // 'shop_id.require'                 => '缺少店铺主键值',
        // 'shop_id.unique'                  => '店铺主键值重复',
        'device_type.require'               => '缺少设备类型',
        'device_sn.require'               => '缺少设备型号'
    ];
}
