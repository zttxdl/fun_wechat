<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\RiderRecruit as RiderRecruitModel;

class RiderRecruit extends Controller
{
    /**
     * 保存骑手招募申请 
     * 
     */
    public function create(Request $request)
    {
        $data = $request->post();
        $data['add_time'] = time();

        // 验证表单数据
        $check = $this->validate($data, 'RiderRecruit');
        if ($check !== true) {
            return json_error($check,201);
        }

        // 判断当前用户是否已有提交
        $res = RiderRecruitModel::where('user_id','=',$data['user_id'])->value('id');
        if ($res) {
            return json_error('您已提交过申请');
        }

        // 提交新增表单
        $result = RiderRecruitModel::create($data,true);
        if (!$result) {
            return json_error('添加失败',201);
        }

        return json_success('添加成功');

    }
     
}
