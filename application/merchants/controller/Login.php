<?php

namespace app\merchants\controller;

use think\Controller;
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
		$data   = $request->param();
        
	}


	/**
	 * 商家注册
	 * @param  \think\Request  $request
     * @return \think\Response
     */

	public function register(Request $request)
	{
		$data   = $request->param();
        
	}
}