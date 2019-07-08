<?php


namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;

class Login extends Base
{
    /**
     * 登录
     */
    public function login(Request $request)
    {
        $phone = $request->param('phone');
        $pwd = $request->param('pwd');
        $code = $request->param('code');

        $data = [
            'phone' => $phone,
            'password' => $pwd,
            'code' => $code,

        ];

        //第一种方法
        $result = $this->validate($data,'app\admin\validate\Login');

        if(true !== $result)
        {
           $this->error($result);//输出错误信息
        }
        $data = captcha_check($code);

        if(!$data)
        {
            $this->error('验证码错误');//输出错误信息
        }

        $user = model('admin')->where('phone',$phone)->find();


        if(!$user)
        {
            $this->error('用户不存在');
        }

        if(md5($pwd) != $user->password)
        {
            $this->error('密码不正确');
        }

        session('admin_user.phone',$phone);

        //记录登录时间
        model('admin')->where('phone',$phone)->setField('last_login_time',time());

        $this->success('登录成功');

    }

    /**
     * 退出登录
     */
    public function loginOut()
    {
        session('admin_user',null);
        $this->success('退出成功');
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

    /**
     * 缓存清理
     */
    public function clear_all() {
        $CACHE_PATH = config('cache.runtime_path').'/cache/';
        $TEMP_PATH = config('cache.runtime_path').'/temp/';
        $LOG_PATH = config('cache.runtime_path').'/log/';
        if (delete_dir_file($CACHE_PATH) && delete_dir_file($TEMP_PATH) && delete_dir_file($LOG_PATH)) {
            $this->success('清除缓存成功!');
        } else {
            $this->error('清除缓存失败!');
        }
    }

}