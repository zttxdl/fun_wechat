<?php

namespace app\rider\controller;

use think\Request;
use app\common\model\RiderInfo;
use app\common\controller\RiderBase;

/**
 * 骑手个人中心控制器
 * @author Mike
 * date 2019/6/10
 */
class Member extends RiderBase
{
    protected  $noNeedLogin = [];

    
    /**
     * 我的资料
     * 
     */
    public function index()
    {
        $info = Db::name('rider_info')->where('id',$this->auth->id)->field('id,headimgurl,link_tel,nickname,remark,status,open_status')->find();
        return json_success('获取骑手信息成功',['info'=>$info]);

    }


    /**
     * 校验绑定的手机号
     * 
     */
    public function BindRiderPhone(Request $request)
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
        $sql_phone = model('RiderInfo')->where('id','=',$this->auth->id)->value('link_tel');

        if ($sql_phone != $phone) {
            $this->error('校验失败,当前手机号非绑定手机号');
        }
        $this->success('校验成功');
    }


    /**
     * 更换手机号【保存】
     * 
     */
    public function setRiderPhone(Request $request)
    {
        $rid = $this->auth->id;
        $phone = $request->param('phone');
        $code  = $request->param('code');
        $type  = $request->param('type');

        // 校验验证码
        $result = model('Alisms', 'service')->checkCode($phone, $type, $code);
        if (!$result) {
            return json_error(model('Alisms', 'service')->getError());
        }

        // 更新数据
        $rider = RiderInfo::get($rid);
        $rider->link_tel = $phone;
        $res = $rider->save();
        if (!$res) {
            return json_error('更换失败');
        }
        return json_success('更换成功');
        
    }


    /**
     * 申请入驻【成为骑手】 
     * 
     */
    public function applyRider(Request $request)
    {
        $data = $request->post();
        $data['status'] = 1;
        $data['add_time'] = time();

        // 验证表单数据
        $check = $this->validate($data, 'RiderInfo');
        if ($check !== true) {
            $this->error($check,201);
        }

        // 更新数据
        $result = RiderInfo::where('id','=',$this->auth->id)->update($data);
        if (!$result) {
            $this->error('更新失败',201);
        }
        $this->success('更新成功');
    }


    /**
     * 重新编辑申请入驻【成为骑手】 
     * 
     */
    public function edit()
    {
        $info = model('RiderInfo')->where('id',$this->auth->id)->field('id,name,link_tel,identity_num,card_img,back_img,hand_card_img,school_id')->find();
        $info['school_name'] = model('school')->getNameById($info['school_id']);
        $this->success('获取成功',['info'=>$info]);        
    }


    /**
     * 保存编辑后的申请入驻【成为骑手】 
     * 
     */
    public function update(Request $request)
    {
        $data = $request->post();

        // 验证表单数据
        $check = $this->validate($data, 'RiderInfo');
        if ($check !== true) {
            $this->error($check,201);
        }
        
        // 更新数据
        $result = RiderInfo::where('id','=',$this->auth->id)->update($data);;

        if (!$result) {
            $this->error('更新失败',201);
        }
        $this->success('更新成功');
    }


    /**
     * 设置骑手开工状态 
     * 
     */
    public function openStatus(Request $request)
    {
        $status = $request->get('status');
        $rid = $this->auth->id;
        $result = RiderInfo::where('id','=',$rid)->setField('open_status',$status);

        if (!$result) {
            $this->error('设置失败');
        }
        $this->success('设置成功');
        
    }
     
     
     
}
