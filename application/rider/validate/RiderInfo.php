<?php

namespace app\Rider\validate;

use think\Validate;

class RiderInfo extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'name'           => 'require|max:10',
        'identity_num'   => 'require',
        'school_id'      => 'require',
        'card_img'       => 'require',
        'back_img'       => 'require',
        'hand_card_img'  => 'require'
    ];
    

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'                  => '姓名必填',
        'name.max'                      => '姓名不能超过10位',
        'identity_num.require'          => '请填写身份证号码',
        'school_id.require'             => '请选择配送所在学校',
        'card_img.require'              => '请上传身份证照正面',
        'back_img.require'              => '请上传身份证照反面',
        'hand_card_img.require'         => '请上传手持身份证照'
    ];


    // 验证场景
    protected $scene = [	
        'join'       =>  ['name', 'school_id'],
        'apply'    =>  ['name', 'identity_num', 'card_img','back_img','hand_card_img','school_id']
    ];



}
