<?php

namespace app\api\controller;

use think\Request;
use app\common\model\User;
use app\common\controller\ApiBase;

/**
 * 用户个人中心控制器
 * @author Mike
 * date 2019/5/30
 */
class Member extends ApiBase
{
    protected  $noNeedLogin = [];


    /**
     * 我的资料
     * 
     */
    public function index()
    {
        $info = model('User')->getUserInfo($this->auth->id);
        set_log('token_',$this->auth,'token_');
        $this->success('获取用户信息成功',['info'=>$info]);

    }

    
    /**
     * 校验绑定的手机号 
     * 
     */
    public function BindUserPhone(Request $request)
    {
        $phone = $request->param('phone');
        $type = $request->param('type');
        $code = $request->param('code');

        // 校验验证码
        $result = model('Alisms', 'service')->checkCode($phone, $type, $code);
        if (!$result) {
            $this->error(model('Alisms', 'service')->getError());
        }

        // 校验当前手机号真实性
        $sql_phone = model('User')->where('id','=',$this->auth->id)->value('phone');

        if ($sql_phone != $phone) {
            $this->error('校验失败,当前手机号非绑定手机号');
        }
        $this->success('校验成功');

    }


    /**
     * 更换手机号【保存】
     * 
     */
    public function setUserPhone(Request $request)
    {
        $uid = $this->auth->id;
        $phone = $request->param('phone');
        $code  = $request->param('code');
        $type  = $request->param('type');

        // 校验验证码
        $result = model('Alisms', 'service')->checkCode($phone, $type, $code);
        if (!$result) {
            $this->error(model('Alisms', 'service')->getError());
        }

        // 更新数据
        $user = User::get($uid);
        $user->phone = $phone;
        $res = $user->save();
        if (!$res) {
            $this->error('更换失败');
        }
        $this->success('更换成功');
        
    }
}
