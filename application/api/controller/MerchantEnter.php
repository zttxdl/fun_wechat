<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\ManageCategory;
use app\common\model\School;
use app\common\model\MerchantEnter as MerchantEnterModel;

class MerchantEnter extends Controller
{
    /**
     * 保存商家入驻申请 
     * 
     */
    public function create(Request $request)
    {
        $data = $request->post();
        $data['add_time'] = time();

        // 验证表单数据
        $check = $this->validate($data, 'MerchantEnter');
        if ($check !== true) {
            return json_error($check,201);
        }

        // 判断当前用户是否已有提交
        $res = MerchantEnterModel::get($data['user_id']);
        if ($res) {
            return json_error('您已提交过申请');
        }

        // 提交新增表单
        $result = MerchantEnterModel::create($data,true);
        if (!$result) {
            return json_error('添加失败',201);
        }

        return json_success('添加成功');

    }
     
     
}
