<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\ReceivingAddr as ReceiveAddr;
use app\common\model\School;

class ReceivingAddr extends Controller
{
    /**
     * 地址列表 
     * 
     */
    public function index($uid,$lat='',$lng='')
    {
        if ($lat == '' & $lng == ''){
            $list = model('ReceiveAddr')->getReceivingAddrList($uid);

        }else{
            $list = model('ReceiveAddr')->getReceivingAddrList($uid);
            foreach ($list as &$value) {
                $value['beyond'] = 0;
                $distance = pc_sphere_distance($lat,$lng,$value['latitude'],$value['longitude']);
                if ($$distance > 3000){
                    $value['beyond'] = 1;
                }
            }
        }


        return json_success('获取收货地址成功',['list'=>$list]);
    }


    /**
     * 保存新增收货地址 
     * 
     */
    public function create(Request $request)
    {
        $data = $request->param();
        $data['add_time'] = time();
        // 验证表单数据
        $check = $this->validate($data, 'ReceivingAddr');
        if ($check !== true) {
            return json_error($check,201);
        }

        $school_id = $request->param('school_id');
        $school_name = model('School')->getNameById($school_id);
        $address = $school_name.$request->param('area_detail');
        $location = get_location($address);
        $data['latitude'] = $location['lat'];
        $data['longitude'] = $location['lng'];

        // 提交新增表单
        $result = $user = ReceiveAddr::create($data,true);
        if (!$result) {
            return json_error('添加失败',201);
        }

        return json_success('添加成功');
    }


    /**
     * 展示收货页面 
     * @param $id  收货地址表主键值
     * 
     */
    public function edit($id)
    {
        $info = ReceiveAddr::get($id);
        $school_model = new School();
        $info['school_name'] = $school_model->getNameById($info['school_id']);
        
        return json_success('获取地址信息成功',['info'=>$info]);
    }


    /**
     * 保存修改收货地址
     * 
     */
    public function update(Request $request)
    {
        $data = $request->param();

        // 验证表单数据
        $check = $this->validate($data, 'ReceivingAddr');
        if ($check !== true) {
            return json_error($check,201);
        }
        
        // 提交表单
        $result = ReceiveAddr::update($data);
        if (!$result) {
            return json_error('修改失败',201);
        }
        
        return json_success('修改成功');

    }


    /**
     * 删除收货地址 
     * 
     */
    public function delete($id)
    {
        $result = ReceiveAddr::destroy($id);

        if (!$result) {
            return json_error('删除失败',201);
        }
        
        return json_success('删除成功');
    }
     
     
     
}
