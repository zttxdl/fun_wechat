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
        'completion_time'      => 'require|number',  
        'rider_extract'      => 'require',

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
        'completion_time.require'  => '订单预估送达时间必传',
        'completion_time.number'  => '订单预估送达时间必须为正整数',
        'fetch_time.require'  => '骑手约定取餐时间值必传',
        'fetch_time.number'  => '骑手约定取餐时间值必须为正整数',
        'rider_extract.require'  => '骑手配送费抽成必填',
    ];

}
