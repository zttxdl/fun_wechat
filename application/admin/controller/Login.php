<?php


namespace app\admin\controller;

<<<<<<< HEAD
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
=======
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
>>>>>>> ztt
    }
}