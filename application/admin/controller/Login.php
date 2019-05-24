<?php


namespace app\admin\controller;


use think\App;
use think\Controller;
use think\facade\Validate;
use think\Db;
use think\captcha\Captcha;

class Login extends Controller
{
    /**
     * 登录
     */
    public function login()
    {
        $phone = $this->request->param('phone');
        $pwd = $this->request->param('pwd');
        $code = $this->request->param('code');

        if(!$phone || !$pwd || !$code) {
            return json_error('参数不能为空');
        }

        if(!Validate::regex($phone, "^1\d{10}$")) {
            return json_error('手机格式不正确', '202');
        }

        $admin_user = session('admin_user');
        $local_code = $admin_user['code'];



        $user = Db::name('admin')->where('phone',$phone)->find();


        if(!$user['phone']) {
            return json_error('用户不存在','203');
        }

        if(md5($pwd) != $user['password']){
            return json_error('密码不正确','204');
        }

        return json_success('登录成功');

    }

    /**
     * 退出登录
     */
    public function loginOut()
    {

    }

    /**
     * 验证码
     */
    public function verify()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }
}