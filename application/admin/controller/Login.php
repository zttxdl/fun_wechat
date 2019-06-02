<?php


namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\facade\Validate;
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

        $data = [
            'phone' => $phone,
            'password' => $pwd,
            'code' => $code,

        ];

        //第一种方法
        $result = $this->validate($data,'app\admin\validate\Login');

        if(true !== $result)
        {
           return json_error($result);//输出错误信息
        }

        /*第二种方法
         $validate = new \app\admin\validate\Login;
        if(!$validate->check($data)){
            $result = $validate->getError();
            return json_error($result);
        }*/

        /*if(!$phone || !$pwd || !$code) {
            return json_error('参数不能为空');
        }
        if(!Validate::regex($phone, "^1\d{10}$")) {
            return json_error('手机格式不正确', '202');
        }*/
        $data = captcha_check($code);
        /*if(!$data)
        {
            return json_error('验证码错误');//输出错误信息
        }*/


        $user = model('admin')->where('phone',$phone)->find();


        if(!$user)
        {
            return json_error('用户不存在');
        }

        if(md5($pwd) != $user->password)
        {
            return json_error('密码不正确');
        }

        session('admin_user.phone',$phone);

        //记录登录时间
        model('admin')->where('phone',$phone)->setField('last_login_time',date("Y-m-d H:i:s",time()));

        return json_success('登录成功');

    }

    /**
     * 退出登录
     */
    public function loginOut()
    {
        session('admin_user',null);
        return json_success('退出成功');
    }

    /**
     * 验证码
     */
    public function verify()
    {
        return captcha('',config('captcha'));
    }

    /**
     * 获取验证码
     * @param $num
     * @return string
     */
    public function getCode($num)
    {
        $chars_array = array(
            "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z",
        );
        $charsLen = count($chars_array) - 1;

        $outputstr = "";
        for ($i=0; $i<$num; $i++)
        {
            $outputstr .= $chars_array[mt_rand(0, $charsLen)];
        }
        return $outputstr;
    }
}