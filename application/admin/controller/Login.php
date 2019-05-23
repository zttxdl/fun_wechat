<?php


namespace app\admin\controller;

use app\common\Auth\JwtAuth;
use think\Request;

class Login
{
    public function index()
    {

    }

    public function login(Request $request)
    {
        $username = $request->param('username');
        $password = $request->param('password');

        // 去数据库或者缓存中验证该用户 获取用户信息的 uid

        $uid = 1;

        $jwtAuth = JwtAuth::getInstance();
        $token = $jwtAuth->setUid($uid)->encode()->getToken();

        return json_success('200',[
            'token' => $token
        ]);
    }
}