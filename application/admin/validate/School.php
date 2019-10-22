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
        'fetch_time'      => 'require|number',
        'fid'      => 'require|number',
        'name'      => 'require|max:30',

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
        'completion_time.number'  => '骑手约定取餐时间值必须为正整数',
        'fid.require' => '上级楼栋名不能为空', 
        'fid.number' => '上级楼栋ID必须为正整数', 
        'name.require' => '楼栋名称不能为空', 
        'name.max' => '楼栋名称不能超过30位', 
    ];

    /**
     * 定义方法
     */
    protected $scene = [
        'update'  =>  ['name','longitude','latitude','completion_time','fetch_time'],
        'insert'  =>  ['name','longitude','latitude','completion_time','fetch_time'],
        'addHourse'  =>  ['fid','name'],
    ];
}
