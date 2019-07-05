<?php


namespace app\merchants\validate;


use think\Validate;

class ShopInfo extends  Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'shop_name'   => 'require',
        'link_tel'    => 'require|max:11|regex:/^1[3456789]{1}\d{9}$/',
        'open_time'   =>  'require',
//        'open_type'   =>  'require',
        'ping_fee'   =>  'require',
        'up_to_send_money'   =>  'require',
        'notice'   =>  'require',
        'info'   =>  'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'shop_name.require'   => '门店名称不能为空',
        'link_tel.require'    => '联系人电话不能为空',
        'link_tel.regex'    => '联系人电话格式不正确',
        'link_tel.max'    => '联系人电话号码不能超过11位',
        'open_time.require'    => '营业时间不能为空',
//        'open_type.require'    => '配送方式不能为空',
        'ping_fee.require'    => '配送价不能为空',
        'up_to_send_money.require'    => '起送价不能为空',
        'notice.require'    => '商家公告不能为空',
        'info.require'    => '商家信息不能为空',
    ];
}