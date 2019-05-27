<?php


namespace app\admin\controller;


use think\Db;
use think\facade\Request;
use think\facade\Validate;

class User
{
    public function getList()
    {

       $user_list = Db::name('user')->order('id','desc')->select();

    }

    public function getDetail()
    {
        $validate = new Validate();
        if($validate->test($data)){
            $result = $validate->getError();
        }
        $uid = Request::param('uid');

        if(!$uid) {
            return json_error('Uid 不能为空');
        }
        $result = [];

        $result['detail'] = Db::name('user')->where('id',$uid)->field('nickname,phone')->find();

        $result['user_address'] = Db::name('receiving_addr')->where('id',$uid)->find();

        $result['user_coupon'] = Db::name('my_coupon')->where('user_id',$uid)->select();

         return json($result);
    }
}