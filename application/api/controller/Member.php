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
        $user_model = new User();
        $info = $user_model->getUserInfo($uid);

        return json_success('获取用户信息成功',['info'=>$info]);

    }

    
     
}
