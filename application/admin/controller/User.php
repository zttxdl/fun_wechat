<?php


namespace app\admin\controller;


use think\Db;
use think\facade\Request;

class User
{
    public function getList()
    {

       $user_list = Db::name('user')->order('id','desc')->select();

    }

    public function getDetail()
    {
        $uid = Request::param('uid');

        $detail['user_list'] = Db::name('user')->where('id',$uid)->find();

        return $detail;
    }
}