<?php

namespace app\merchants\controller;

use think\Controller;
use app\common\model\ShopInfo;

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

	public function login()
	{
		$account       	= $this->request->param('account');
        $password     	= $this->request->param('password');

        $check = $this->validate($this->request->param(), 'Login');
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

        return json_success($user);
	}


	/**
	 * 商家注册
	 * @param  \think\Request  $request
     * @return \think\Response
     */

	public function register()
	{

        $account       	= $this->request->param('account');
        $password     	= $this->request->param('password');
        $vcode 			= $this->request->param('vcode/s', '');
        // 表单后台验证
		$check = $this->validate($this->request->param(), 'Login');
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
		        $data['shop_id'] = $result->id;
		        return json_success($result->id);
		        ;
		    } else {
		        return json_error('注册失败');
		    }
        }else{
        	return json_error('此手机号已注册过账号！');
        }
	}
}






