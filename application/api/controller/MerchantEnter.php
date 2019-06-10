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
            $this->error($check,201);
        }

        // 判断当前用户是否已有提交
        $res = MerchantEnterModel::where('user_id','=',$data['user_id'])->value('id');
        if ($res) {
            $this->error('您已提交过申请');
        }

        // 提交新增表单
        $result = MerchantEnterModel::create($data,true);
        if (!$result) {
            $this->error('添加失败',201);
        }

        $this->success('添加成功');

    }
     
     
}
