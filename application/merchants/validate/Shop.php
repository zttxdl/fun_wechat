<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/27
 * Time: 8:32 PM
 */

namespace app\merchants\validate;


use think\Validate;

class Shop extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    // 验证规则
    protected $rule = [
        'phone' => 'require|max:11|/^1[3456789]{1}\d{9}$/',
        'code'    => 'require|max:6',
        'new_password'    => 'require|min:6|max:20',
        'sure_password'    => 'require|min:6|max:20',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'phone.require' => '手机号必填',
        'phone.max' => '最多不能超过11位',
        'phone./^1[3456789]{1}\d{9}$/' => '手机格式错误',
        'code.require' => '验证码必填',
        'code.max' => '验证码最多6位',
        'new_password.require'   => '新密码必填',
        'new_password.min'   => '新密码最少不能低于6位',
        'new_password.max'   => '新密码最多不能超过20位',
        'sure_password.require'   => '确认密码必填',
        'sure_password.min'   => '确认密码最少不能低于6位',
        'sure_password.max'   => '确认密码最多不能超过20位',

    ];
}