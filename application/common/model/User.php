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
     

    /**
     * 获取创建时间字符串格式 
     * 
     */
    public function getAddTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 获取最近登录时间字符串格式 
     * 
     */
    public function getLastLoginTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 获取会员类型
     * 
     */
    public function getTypeAttr($value,$data)
    {
        $type = ['1' => '普通会员'];
        return $type[$data['type']];
    }

    /**
     * 获取会员手机号
     * 
     */
    public function getPhoneById($user_id) 
    {
        
    }


    /**
     * 获取相关时间搜索的新增用户量 
     * 
     */
    public function getNewUsersCount($time)
    {
        $user_num = $this->whereTime('add_time',$time)->count();
        return $user_num;
    }
     


}