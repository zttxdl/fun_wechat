<?php

namespace app\canteen\controller;

use think\Request;
use app\common\controller\Base;

class Login extends Base
{
    //登录
    public function login(Request $request)
    {
        $account = $request->param('account');
        $password = $request->param('password');
        $code = $request->param('code');

        $data = [
            'account' => $account,
            'password' => $password,
            'code' => $code,
        ];

        //第一种方法
        $result = $this->validate($data,'Login');

        if(true !== $result)
        {
           $this->error($result);//输出错误信息
        }
        $data = captcha_check($code);

        if(!$data)
        {
            $this->error('验证码错误');//输出错误信息
        }

        $user = model('canteen')->where('account',$account)->find();

        if(!$user)
        {
            $this->error('用户不存在');
        }

        if(md5($password) != $user->password)
        {
            $this->error('密码不正确');
        }

        session('canteen.id',$user->id);

        //记录登录时间
        model('canteen')->where('account',$account)->setField('last_login_time',time());

        $this->success('登录成功');
    }

    /**
     * 修改密码
     */
    public function updatePwd(Request $request)
    {
        $id = session('canteen.id');
        $old_pwd = $request->param('old_pwd');
        $new_pwd = $request->param('new_pwd');
        $sure_pwd = $request->param('sure_pwd');


        //参数校验
        $check = $this->validate($request->param(), 'Account');
        if ($check !== true) {
            $this->error($check);
        }

        $result  = model('canteen')
            ->field('password')
            ->where('id',$id)
            ->find();

        if(md5($old_pwd) != $result->password) {
            $this->error('旧密码不正确,请重新输入');
        }

        if($sure_pwd != $new_pwd) {
            $this->error('两次密码不一致,请重新输入');
        }

        $result->password = md5($new_pwd);
        $ret = $result->save();
        if($ret) {
            $this->success('更新成功');
        }       
    }

    /**
     * 获取验证码
     */
    public function verify(){
        return captcha('',config('captcha'));
    }

    /**
     * 退出登录
     */
    public function loginOut()
    {
        session('canteen',null);
        $this->success('退出成功');
    }
}
