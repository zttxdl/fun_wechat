<?php

namespace app\common\model;

use think\Model;

class RiderInfo extends Model
{
    /**
     * 获取一条用户记录
     * @param $uid
     */
    public function getRiderInfo($uid)
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
    public function getRiderList($page_no, $page_size)
    {
        $res = $this->page($page_no,$page_size)->select();
        return $res;
    }


    /**
     * 获取用户的主键值
     * @param $openid
     */
    public function getRidByOpenId($openid)
    {
        $id = $this->where('openid',$openid)->value('id');
        return $id;
    }
}
