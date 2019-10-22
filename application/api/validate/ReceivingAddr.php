<?php

namespace app\api\validate;

use think\Validate;

class ReceivingAddr extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'name'          => 'require|max:10',
        'phone'         => 'require|regex:/^1[3456789]{1}\d{9}$/',
        'school_id'     => 'require',
        'area_detail'   => 'require',
        'longitude'   => 'require',
        'latitude'   => 'require',
        'hourse_id'   => 'require',

    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'          => '联系人必填',
        'name.max'              => '联系人不能超过10位',
        'phone.require'         => '手机号必填',
        'phone.regex'           => '手机号格式不对',
        'school_id.require'     => '请选择收货地址',
        'area_detail.require'   => '请填写门牌号',
        'longitude.require'   => '经度必传',
        'latitude.require'   => '纬度必传',
        'hourse_id.require'   => '楼号必传',
    ];


    /**
     * 定义方法
     */
    protected $scene = [
        'add'  =>  ['name','phone','school_id','hourse_id'],
        'save'  =>  ['name','phone','school_id','hourse_id'],
    ];
}
