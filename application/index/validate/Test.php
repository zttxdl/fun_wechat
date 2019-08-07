<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/8/4
 * Time: 7:20 PM
 */

namespace app\index\validate;

use think\Validate;

class Test extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'UserName'          => 'require',
        'Password'          => 'require',
//        'MACAddress'          => 'require|regex:/^[A-F0-9]{2}([-:]?[A-F0-9]{2})([-:.]?[A-F0-9]{2})([-:]?[A-F0-9]{2})([-:.]?[A-F0-9]{2})([-:]?[A-F0-9]{2})$/',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'UserName.require'          => '用户名不能为空',
        'Password.require'              => '密码不能为空',
        'MACAddress.regex'              => 'MAC 地址规则不正确',
    ];
}
