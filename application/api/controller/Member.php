<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\User;


/**
 * 用户个人中心控制器
 * @author Mike
 * date 2019/5/30
 */
class Member extends Controller
{
    /**
     * 我的资料
     * 
     */
    public function index($uid)
    {
        $info = model('User')->getUserInfo($uid);
        $this->succes('获取用户信息成功',['info'=>$info]);

    }

    
    /**
     * 更换手机号【保存】
     * 
     */
    public function setUserPhone(Request $request)
    {
        $uid = $request->param('uid');
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
        $user_info = User::get($uid);
        $this->succes('更换成功',['user_info'=>$user_info]);
        
    }
}
