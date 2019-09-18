<?php

namespace app\common\model;

use think\Model;

class RiderInfo extends Model
{

    /**
     * 获取处理状态【方法很好用，但是前段需要status的原始数值，所以需要另写一个字段来标注】
     * 
     */
    public function getMbStatusAttr($value,$data)
    {
        $status = ['0' => '未注册','1' => '待审核','2' => '未通过','3' => '通过','4' => '禁用'];
        return $status[$data['status']];
    }

    /**
     * 获取一条骑手记录
     * @param $uid
     */
    public function getRiderInfo($uid)
    {
        $res = $this->where('id',$uid)->find();
        return $res;
    }

    /**
     * 获取骑手列表
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
     * 获取骑手的主键值
     * @param $openid
     */
    public function getRidByOpenId($openid)
    {
        $id = $this->where('openid',$openid)->value('id');
        return $id;
    }

    /**
     * 获取骑手的手机号
     * @param $openid
     */
    public function getPhoneById($id)
    {
        return $this->where('id',$id)->value('phone');
    }

    /**
     * 获取骑手名称
     * @param $id
     * @return mixed
     */
    public function getNameById($id)
    {
        return $this->where('id',$id)->value('name');
    }

}
