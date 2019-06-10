<?php

namespace app\admin\controller;

use app\common\Auth\JwtAuth;
use app\common\Response\ResponseJson;
use think\Controller;
use think\Request;

/**
 * 后台管理员类
 */
class Auth extends Controller
{
    use ResponseJson;

    /**
     * @param Request $request
     * @return false|string
     */
    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        // 去数据看库或者缓存验证该用户 用户信息的uid


        $jwtAuth = JwtAuth::getInstance();
        $token = $jwtAuth->setUid(1)->encode()->getToken();

        $this->succes([
            'token' => $token
        ]);
    }
}