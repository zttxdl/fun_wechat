<?php
namespace app\index\controller;

use think\Controller;
use think\Request;

class Index extends Controller
{
    public function index()
    {
        $data = ['aaa','dddd'];
        return json($data);
    }


    // 获取用户信息
    public function read($id = 0)
    {
        $user = ['aaaa','111'];
        if ($user) {
            return json($user);
        } else {
            // 抛出HTTP异常 并发送404状态码
            abort(404,'用户不存在');
        }
    }

}
