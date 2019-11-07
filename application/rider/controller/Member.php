<?php

namespace app\rider\controller;

use think\Db;
use think\Request;
use app\common\model\RiderInfo;
use app\common\controller\RiderBase;
use app\common\Auth\JwtAuth;


/**
 * 骑手个人中心控制器
 * @author Mike
 * date 2019/6/10
 */
class Member extends RiderBase
{
    protected  $noNeedLogin = [];

    
    /**
     * 骑手加入表单状态【前端用】
     */
    public function checkJoin()
    {
        $check_join = model('RiderInfo')->where('id',$this->auth->id)->value('check_join');
        return json_success('获取加入状态成功',['check_join'=>$check_join]);
    }


    /**
     * 判断当前身份绑定的状态 
     * 
     */
    public function checkIdentityStatus()
    {
        $info = Db::name('rider_info r')->join('school s','r.school_id = s.id')->where('r.id',$this->auth->id)->field('r.name,r.identity_num,r.card_img,r.back_img,r.hand_card_img,s.name as school_name,r.phone,r.remark,r.status')->find();
        if ($info['status'] == 2) {
            $info['mb_remark'] = Db::name('check_status')->where('type','=',2)->where('id','in',$info['remark'])->column('name');
            unset($info['remark']);
        }
        
        $this->success('获取当前身份绑定状态成功',['info'=>$info]);
    }


    /**
     * 我的资料
     * 
     */
    public function index()
    {
        $info = Db::name('rider_info')->where('id',$this->auth->id)->field('id,headimgurl,nickname,phone,status,open_status')->find();
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
        $sql_phone = model('RiderInfo')->where('id','=',$this->auth->id)->value('phone');

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
        $rider->phone = $phone;
        $res = $rider->save();
        if (!$res) {
            return json_error('更换失败');
        }
        return json_success('更换成功');
        
    }


    /**
     * 欢迎加入 
     * 
     */
    public function toJoin(Request $request)
    {
        $data = $request->post();
        // 验证表单数据
        $check = $this->validate($data, 'RiderInfo.join');
        if ($check !== true) {
            $this->error($check,201);
        }
        $data['check_join'] = 1;

        // 更新数据
        $result = RiderInfo::where('id','=',$this->auth->id)->update($data);
        if (!$result) {
            $this->error('加入失败',201);
        }
        $rider_info = RiderInfo::where('id','=',$this->auth->id)->field('id,school_id,status,open_status')->find();

        // 将该学校下的所有楼信息，存表
        $school_id = $data['school_id'];
        $hourse_ids_arr = Db::name('hourse')->where('school_id','=',$school_id)->column('id');
        if ($hourse_ids_arr) {
            $hourse_ids_str = implode(',',$hourse_ids_arr);
            Db::name('rider_info')->where('id',$this->auth->id)->setField('hourse_ids','0,'.$hourse_ids_str);
        }

        $jwtAuth = new JwtAuth();
        $token = $jwtAuth->createToken($rider_info,31104000);

        $this->success('加入成功',['token'=>$token]);
    }
     

    /**
     * 申请入驻【成为骑手】 【第一次添加或重新编辑】
     * 
     */
    public function applyRider(Request $request)
    {
        $data = $request->param();
        $data['status'] = 1;
        $data['add_time'] = time();
        $data['overtime'] = time() + 24*7*3600;
        
        // 校验手机号
        $phone = $request->param('phone');
        $code  = $request->param('code');
        $type  = $request->param('type');

        // 校验验证码
        $result = model('Alisms', 'service')->checkCode($phone, $type, $code);
        if (!$result) {
            return json_error(model('Alisms', 'service')->getError());
        }

        // 验证表单数据
        $check = $this->validate($data, 'RiderInfo.apply');
        if ($check !== true) {
            $this->error($check,201);
        }

        // 更新数据
        $result = RiderInfo::where('id','=',$this->auth->id)->update($data);
        if (!$result) {
            $this->error('申请失败',201);
        }
        
        $rider_info = RiderInfo::where('id','=',$this->auth->id)->field('id,school_id,status,open_status')->find();

        // 将该学校下的所有楼信息，存表
        $school_id = $data['school_id'];
        $hourse_ids_arr = Db::name('hourse')->where('school_id','=',$school_id)->column('id');
        if ($hourse_ids_arr) {
            $hourse_ids_str = implode(',',$hourse_ids_arr);
            Db::name('rider_info')->where('id',$this->auth->id)->setField('hourse_ids','0,'.$hourse_ids_str);
        }

        $jwtAuth = new JwtAuth();
        $token = $jwtAuth->createToken($rider_info,31104000);

        $this->success('申请成功',['token'=>$token]);
    }


    /**
     * 重新编辑申请入驻【成为骑手】 
     * 
     */
    public function edit()
    {
        $info = model('RiderInfo')->where('id',$this->auth->id)->field('id,name,identity_num,card_img,back_img,hand_card_img,school_id,phone')->find();
        $info['school_name'] = model('school')->getNameById($info['school_id']);
        $this->success('获取成功',['info'=>$info]);        
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

    /**
     * 我的评价
     */
    public function getEvaluation(Request $request)
    {
        $page = $request->param('page',1);
        $pagesize = $request->param('pagesize',20);
        $type = $request->param('type','');

        $where[] = ['rider_id','=',$this->auth->id];
        if ($type){
            $where[] = ['star','=',$type];
        }

        $count = Db::name('rider_comments')->where('rider_id',$this->auth->id)->count();
        $count1 = Db::name('rider_comments')->where('star',1)->where('rider_id',$this->auth->id)->count();
        $count2 = Db::name('rider_comments')->where('star',2)->where('rider_id',$this->auth->id)->count();
        $count3 = Db::name('rider_comments')->where('star',3)->where('rider_id',$this->auth->id)->count();
        $list = Db::name('rider_comments')
                ->field('a.star,a.content,a.add_time,b.headimgurl,b.nickname')
                ->alias('a')
                ->join('user b','a.user_id = b.id')
                ->where($where)
                ->page($page,$pagesize)
                ->select();
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d',$item['add_time']);
        }

        $data['count']['all'] = $count;
        $data['count']['cz'] = $count3;
        $data['count']['yb'] = $count2;
        $data['count']['cp'] = $count1;
        $data['list'] = $list;

        $this->success('success',$data);

    }


    /**
     * 获取当前骑手绑定学校的经纬度 
     * 
     */
    public function getSchoolLatLong()
    {
        $info = Db::name('rider_info r')->join('school s','r.school_id = s.id')->where('r.id','=',$this->auth->id)->field('s.longitude,s.latitude,s.name')->find();

        $this->success('获取成功',['info'=>$info]);
    }


    /**
     * 更新用户数据信息 
     * 
     */
    public function updateRiderInfo(Request $request)
    {
        $rider_id = $this->auth->id;
        $data = $request->param();

        $res = Db::name('rider_info')->where('id','=',$rider_id)->update($data);
        if ($res !== false) {
            $this->success('更新骑手数据信息成功');
        }
        $this->error('更新骑手数据信息失败');
    }
     
     
     
}
