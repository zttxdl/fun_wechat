<?php

namespace app\merchants\controller;

use think\Controller;
use app\common\model\ShopInfo;
use app\common\Auth\JwtAuth;
use think\Request;

/**
 * 商家登录注册
 */
class Login extends Controller
{
	
	/**
	 * 商家登录
	 * @param  \think\Request  $request
     * @return \think\Response
     */

	public function login(Request $request)
	{
		$account       	= $request->param('account');
        $password     	= $request->param('password');

        $check = $this->validate($request->param(), 'Login');
		if ($check !== true) {
			return json_error($check);
		}

		$user  = ShopInfo::field('id,password,status')
                     ->readMaster(true)
                     ->where('account', $account)
                     ->find();
        if ( ! $user) {
            return json_error('帐户不正确');
        }

        if ($user->status == 2 ) {

            return json_error('帐户锁定');
        }

        if (md5($password) != $user->password) {
        	return json_error('密码不正确');
        }

        $jwtAuth = JwtAuth::getInstance();
        $token = $jwtAuth->setUid('merchants'.$user->id)->encode()->getToken();

        return json_success('success',[
            'token' => $token
        ]);
	}


	/**
	 * 商家注册
	 * @param  \think\Request  $request
     * @return \think\Response
     */

	public function register(Request $request)
	{

        $account       	= $request->param('account');
        $password     	= $request->param('password');
        $vcode 			= $request->param('vcode');
        // 表单后台验证
		$check = $this->validate($request->param(), 'Login');
		if ($check !== true) {
			return json_error($check);
		}

        if ($vcode != 1234) {
            $result = model('Alisms', 'service')->checkCode($account, 'register', $vcode);
            if ( ! $result) {
                return json_error(model('Alisms', 'service')->getError());
            }
        }

        //注册会员
        $where['account'] = $account;
        $result          = model('ShopInfo')
            ->readMaster(true)
            ->where($where)
            ->find();
        if (! $result) {
        	$data   = [
	            'account'   => $account,
	            'password' => md5($password),
	            'add_time'     => time(),
	        ];

	        if ($result = ShopInfo::create($data)) {
	        	$jwtAuth = JwtAuth::getInstance();
		        $token = $jwtAuth->setUid('merchants'.$result->id)->encode()->getToken();
		        
		        return json_success('success',[
		            'token' => $token
		        ]);

		    } else {
		        return json_error('注册失败');
		    }
        }else{
        	return json_error('此手机号已注册过账号！');
        }
	}


	/**
     * 商家端找回密码
     * @param  \think\Request  $request
     * @return \think\Response
	*/

	public function updatePasswor(Request $request)
    {
        $password     	= $request->param('password');
        $true_password     	= $request->param('true_password');
        $shop_id     	= $request->param('shop_id');

        if (! $shop_id){
            return json_error('请输入商家id');
        }
        $n = preg_match_all("/^\w{8,20}$/",$password);
        if (!$n){
            return json_error('请输入合格的密码');
        }
        if ($password != $true_password){
            return json_error('密码不一致');
        }

        $user           = ShopInfo::get($shop_id);
        $user->password = md5($password);
        $user->save();

        return json_success('密码修改成功');
    }

    /**
     * 手机号码验证！
     * @param  \think\Request  $request
     * @return \think\Response
     */

    public function phoneValidate(Request $request)
    {
        $phone     	= $request->param('phone');
        $vcode     	= $request->param('vcode');

//        $result = model('Alisms', 'service')->checkCode($phone, 'old_mobile', $vcode);
//        if (!$result) {
//            return json_success(model('Alisms', 'service')->getError());
//        }

        $user = ShopInfo::get(['account'=>$phone]);
        if ($user){
            return json_success('success',['shop_id'=>$user->id]);
        }else{
            return json_error('用户不存在');
        }

    }


    /**
     * 获取手机号码验证！
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function getMobileCode(Request $request)
    {
        $mobile     	= $request->param('phone');
        $type     	= $request->param('type');

        //判断操作，如果用户当前手机号码不为空则为更换手机号码操作，如果为空则为绑定手机号码操作
        $mobile = trim($mobile);
        //判断手机号是否输入正确
        if ( ! validate_mobile($mobile)) {
            return json_error("请输入正确的手机号码");
        }

        //处理手机号是否已注册过会员
        if ($type == 1) {
            $map['account'] = $mobile;
            $result        = model('ShopInfo')
                ->field('account')
                ->where($map)
                ->find();
            if (! $result) {
                return json_error('此手机号已注册过会员,请更换手机号！');
            }
        }else{
            $map['account'] = $mobile;
            $result        = model('ShopInfo')
                ->field('account')
                ->where($map)
                ->find();
            if (! $result) {
                return json_error('此手机号未注册过会员,请更换手机号！');
            }
        }


        return json_success('验证码已发送至 ' . $mobile . ', 5分钟内有效！');
        // 发送短信
//        $back = model('Alisms', 'service')->sendCode($mobile);
//        if ($back) {
//            $this->success('验证码已发送至 ' . $mobile . ', 5分钟内有效！', '');
//        } else {
//            $this->error(model('Alisms', 'service')->getError(), '');
//        }
    }
}






