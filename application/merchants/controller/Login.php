<?php

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use app\common\model\ShopInfo;
use app\common\Auth\JwtAuth;
use think\Request;

/**
 * 商家登录注册
 */
class Login extends MerchantsBase
{
    protected $noNeedLogin = ['*'];

	/**
	 * 商家登录
	 * @param  \think\Request  $request
     * @return \think\Response
     */

	public function login(Request $request)
	{
		$account       	= $request->param('account');
        $password     	= $request->param('password','');

        $check = $this->validate($request->param(), 'Login');
		if ($check !== true) {
			$this->error($check);
		}

		$user  = ShopInfo::field('id,password,status')
                     ->readMaster(true)
                     ->where('account', $account)
                     ->find();
        if ( ! $user) {
            $this->error('帐户不正确');
        }


        if ($user->status == 2 ) {

            $this->error('帐户锁定');
        }

        if (md5($password) != $user->password) {
            $this->error('密码不正确');
        }

        $jwtAuth = new JwtAuth();
        $token = $jwtAuth->createToken('merchants'.$user->id,604800);
        $this->success('success',[
            'token' => $token
        ]);
	}


    /**
     * 商家登录  验证码登录
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function login2(Request $request)
    {
        $account       	= $request->param('account','');
        $vcode     	= $request->param('vcode','');

        if ($account == '' || $vcode == ''){
            $this->error('用户名和密码不能为空！');
        }

        $result = model('Alisms', 'service')->checkCode($account, 'login', $vcode);
        if ( ! $result) {
            $this->error(model('Alisms', 'service')->getError());
        }

        $user  = ShopInfo::field('id,password,status')
            ->readMaster(true)
            ->where('account', $account)
            ->find();
        if ( ! $user) {
            $this->error('帐户不正确');
        }


        if ($user->status == 2 ) {

            $this->error('帐户锁定');
        }

        $jwtAuth = new JwtAuth();
        $token = $jwtAuth->createToken('merchants'.$user->id,604800);
        $this->success('success',[
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
			$this->error($check);
		}

//        if ($vcode != 1234) {
            $result = model('Alisms', 'service')->checkCode($account, 'register', $vcode);
            if ( ! $result) {
                $this->error(model('Alisms', 'service')->getError());
            }
//        }

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
                $jwtAuth = new JwtAuth();
                $token = $jwtAuth->createToken('merchants'.$result->id,604800);
                $this->success('success',[
                    'token' => $token
                ]);

		    } else {
		        $this->error('注册失败');
		    }
        }else{
        	$this->error('此手机号已注册过账号！');
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
            $this->error('请输入商家id');
        }
        $n = preg_match_all("/^\w{8,20}$/",$password);
        if (!$n){
            $this->error('请输入合格的密码');
        }
        if ($password != $true_password){
            $this->error('密码不一致');
        }

        $user           = ShopInfo::get($shop_id);
        $user->password = md5($password);
        $user->save();

        $this->success('密码修改成功');
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

        //是否为验证码登录
        if ($vcode != '1234'){
            $result = model('Alisms', 'service')->checkCode($phone, 'auth', $vcode);
            if ( ! $result) {
                $this->error(model('Alisms', 'service')->getError());
            }
        }

        $user = ShopInfo::get(['account'=>$phone]);
        if ($user){
            $this->success('success',['shop_id'=>$user->id]);
        }else{
            $this->error('用户不存在');
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
            $this->error("请输入正确的手机号码");
        }

        //处理手机号是否已注册过会员
        if ($type == 'register') {
            $map['account'] = $mobile;
            $result        = model('ShopInfo')
                ->field('account')
                ->where($map)
                ->find();
            if ($result) {
                $this->error('此手机号已注册过会员,请更换手机号！');
            }
        }

        // 发送短信
        $back = model('Alisms', 'service')->sendCode($mobile,$type);

        if ($back) {
            $this->success('验证码已发送至 ' . $mobile . ', 5分钟内有效！');
        } else {
            $this->error('短信发送失败');
        }

    }
}






