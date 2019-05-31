<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\captcha\Captcha;


/**
 * 用户登录控制器
 */
class Login extends Controller
{
    /**
     * 方法名 
     * 
     */
    public function index()
    {
        print_r($_SERVER);
    }
     
}
