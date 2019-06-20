<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use EasyWeChat\Factory;


class Transfer extends Controller
{

    /**
     * 企业付款到用户零钱
     * 
     * @param  $amount       [发送的金额（分）目前发送金额不能少于1元]
	 * @param  $openid       [发送人的 openid]
	 * @param  $withdraw_sn  [商户订单号]
	 * @param  $desc         [企业付款描述信息 (必填)]
	 * @param  $check_name   [收款用户姓名 (选填)]
	 * @return [type]        [description]
     */
    public function sendMoney($amount,$openid,$withdraw_sn,$desc='提现',$check_name='')
    {
        $config = config('wx_pay');
        $payment = Factory::payment($config);
        $result = $payment->transfer->toBalance([
            'partner_trade_no' => $withdraw_sn, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
            'openid' => $openid,
            'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
            're_user_name' => $check_name, // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
            'amount' => $amount * 100, // 企业付款金额，单位为分
            'desc' => $desc, // 企业付款操作说明信息。必填
        ]);

        var_dump($result);die;

        

        
    }

     

}
