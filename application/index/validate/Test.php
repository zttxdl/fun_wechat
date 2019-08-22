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
        'AccountType'          => ['require','number','regex'=>'^[1-2]{1}$'],
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
        'AccountType.require'              => '账号类型不能为空',
        'AccountType.number'              => '账号类型必须是数字',
        'AccountType.regex'              => '账号类型非法数值',
    ];
}
