<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/5/23
 * Time: 1:44 PM
 */
namespace app\merchants\validate;

use think\Validate;

class Merchants extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'shop_name'   => 'require',
        'logo_img'    => 'require',
        'school_id'    => 'require',
        'manage_category_id'    => 'require',
        'link_name'    => 'require',
        'link_tel'    => 'require|max:11|/^1[3456789]{1}\d{9}$/',
        'business_license'    => 'require',
        'proprietor'    => 'require',
        'hand_card_front'    => 'require',
        'hand_card_back'    => 'require',
        'user_name'    => 'require',
        'identity_num'    => 'require|min:18',
        'sex'    => 'require',
        'licence'    => 'require',
        'branch_back'    => 'require',
        'back_hand_name'    => 'require',
        'back_card_num'    => 'require|max:19|min:16',
        'account_type'    => 'require',
        'address'    => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'shop_name.require'   => '门店名称不能为空',
        'logo_img.require'    => '商标图不能为空',
        'school_id.require'    => '所在学校不能为空',
        'manage_category_id.require'    => '经营品类不能为空',
        'link_name.require'    => '联系人不能为空',
        'link_tel.require'    => '联系人电话不能为空',
        'link_tel.max'    => '联系人电话不能超过11位',
        'link_tel./^1[3456789]{1}\d{9}$/'    => '电话号码格式错误',
        'business_license.require'    => '营业执照不能为空',
        'proprietor.require'    => '法人/经营人不能为空',
        'hand_card_front.require'    => '身份证正面照不能为空',
        'hand_card_back.require'    => '身份证反面照不能为空',
        'user_name.require'    => '姓名不能为空',
        'identity_num.require'    => '身份证不能为空',
        'identity_num.min'    => '身份证不能少于18位',
        'sex.require'    => '性别不能为空',
        'licence.require'    => '许可证不能为空',
        'branch_back.require'    => '开户银行不能为空',
        'back_hand_name.require'    => '开户人不能为空',
        'back_card_num.require'    => '银行卡号不能为空',
        'back_card_num.max'    => '银行卡号最小16位',
        'back_card_num.min'    => '银行卡号最大19位',
        'account_type.require'    => '账号类型不能为空',
        'address.require'    => '地址不能为空',
    ];
}
