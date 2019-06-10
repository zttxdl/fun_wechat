<?php

namespace app\rider\controller;

use think\Controller;
use think\Request;
use app\common\model\RiderInfo;


/**
 * 骑手个人中心控制器
 * @author Mike
 * date 2019/6/10
 */
class Member extends Controller
{
    /**
     * 我的资料
     * 
     */
    public function index($rid)
    {
        $info = model('RiderInfo')->getRiderInfo($rid);
        return json_success('获取骑手信息成功',['info'=>$info]);

    }

    
    /**
     * 更换手机号【保存】
     * 
     */
    public function setRiderPhone(Request $request)
    {
        $uid = $request->param('rid');
        $phone = $request->param('phone');
        $code  = $request->param('code');
        $type  = $request->param('type');

        // 校验验证码
        $result = model('Alisms', 'service')->checkCode($phone, $type, $code);
        if (!$result) {
            return json_error(model('Alisms', 'service')->getError());
        }

        // 更新数据
        $rider = RiderInfo::get($uid);
        $rider->phone = $phone;
        $res = $rider->save();
        if (!$res) {
            return json_error('更换失败');
        }
        $rider_info = RiderInfo::get($uid);
        return json_success('更换成功',['rider_info'=>$rider_info]);
        
    }


    /**
     * 申请入驻【成为骑手】 
     * 
     */
    public function applyRider(Request $request)
    {

        return json_success('ok');
    }


    /**
     * 重新编辑申请入驻【成为骑手】 
     * 
     */
    public function edit($rid)
    {
        return json_success('ok');        
    }


    /**
     * 保存编辑后的申请入驻【成为骑手】 
     * 
     */
    public function uodate(Request $request)
    {
        return json_success('ok');        
    }
     
     
}
