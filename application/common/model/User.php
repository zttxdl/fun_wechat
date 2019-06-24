<?php

namespace app\common\model;

use think\Model;

class User extends Model
{
    /**
     * 获取一条用户记录
     * @param $uid
     */
    public function getUserInfo($uid)
    {
        $res = $this->where('id',$uid)->find();
        return $res;
    }

    /**
     * 获取用户列表
     * @param $page_no
     * @param $page_size
     * @return mixed
     */
    public function getUserList($page_no, $page_size)
    {
        $res = $this->page($page_no,$page_size)->select();
        return $res;
    }


    /**
     * 获取用户的主键值
     * @param $openid
     */
    public function getUidByOpenId($openid)
    {
        $id = $this->where('openid',$openid)->value('id');
        return $id;
    }

    /**
     * 根据用户ID获取用户昵称
     * @param $user_id
     * @return mixed
     */
    public function getUserNameById($user_id)
    {
        $user_name = $this->where('id',$user_id)->value('nickname');
        return $user_name;
    }

    /**
     * 获取当前用户的购买用户类别 
     * 
     */
    public function getNewBuy($user_id)
    {
        return $this->where('id',$user_id)->value('new_buy');
    }
     

}