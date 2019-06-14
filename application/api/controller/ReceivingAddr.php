<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\ReceivingAddr as ReceiveAddr;
use app\common\model\School;
use app\common\controller\ApiBase;

class ReceivingAddr extends ApiBase
{
    protected  $noNeedLogin = [];


    /**
     * 地址列表 
     * 
     */
    public function index(Request $request)
    {
        $school_id = $request->param('school_id','');
        if ($school_id == ''){
            $list = model('ReceivingAddr')->getReceivingAddrList($this->auth->id);

        }else{
            $list = model('ReceivingAddr')->getReceivingAddrList($this->auth->id);
            foreach ($list as &$value) {
                $value['beyond'] = 0;
                if ( $value['school_id'] == $school_id){
                    $value['beyond'] = 1;
                }
            }
        }


        $this->success('获取收货地址成功',['list'=>$list]);
    }


    /**
     * 保存新增收货地址 
     * 
     */
    public function create(Request $request)
    {
        $data = $request->param();
        $data['user_id'] = $this->auth->id;
        $data['add_time'] = time();
        // 验证表单数据
        $check = $this->validate($data, 'ReceivingAddr');
        if ($check !== true) {
            $this->error($check,201);
        }
        // 将物理地址逆解析为经纬度
        $school_id = $request->param('school_id');
        $school_name = model('School')->getNameById($school_id);
        $address = $school_name.$request->param('area_detail');
        $location = get_location($address);
        if (empty($location)) {
            $this->error('地址无法定位哦，请认真填写',201);            
        }
        $data['latitude'] = $location['lat'];
        $data['longitude'] = $location['lng'];

        // 提交新增表单
        $result = ReceiveAddr::create($data,true);
        if (!$result) {
            $this->error('添加失败',201);
        }

        $this->success('添加成功');
    }


    /**
     * 展示编辑收货页面 
     * @param $id  收货地址表主键值
     * 
     */
    public function edit($id)
    {
        $info = ReceiveAddr::get($id);
        $school_model = new School();
        $info['school_name'] = $school_model->getNameById($info['school_id']);
        
        $this->success('获取地址信息成功',['info'=>$info]);
    }


    /**
     * 保存修改收货地址
     * 
     */
    public function update(Request $request)
    {
        $data = $request->param();
        $data['user_id'] = $this->auth->id;

        // 验证表单数据
        $check = $this->validate($data, 'ReceivingAddr');
        if ($check !== true) {
            $this->error($check,201);
        }

        // 将物理地址逆解析为经纬度
        $school_id = $request->param('school_id');
        $school_name = model('School')->getNameById($school_id);
        $address = $school_name.$request->param('area_detail');
        $location = get_location($address);
        if (empty($location)) {
            $this->error('地址无法定位哦，请认真填写',201);            
        }
        $data['latitude'] = $location['lat'];
        $data['longitude'] = $location['lng'];
        
        // 提交表单
        $result = ReceiveAddr::update($data);
        if (!$result) {
            $this->error('修改失败',201);
        }
        
        $this->success('修改成功');

    }


    /**
     * 删除收货地址 
     * 
     */
    public function delete($id)
    {
        $result = ReceiveAddr::destroy($id);

        if (!$result) {
            $this->error('删除失败',201);
        }
        
        $this->success('删除成功');
    }
     
     
     
}
