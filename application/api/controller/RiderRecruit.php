<?php

namespace app\api\controller;

use think\Request;
use app\common\model\RiderRecruit as RiderRecruitModel;
use app\common\controller\ApiBase;

class RiderRecruit extends ApiBase
{
    protected  $noNeedLogin = [];


    /**
     * 保存骑手招募申请 
     * 
     */
    public function create(Request $request)
    {
        $data = $request->post();
        $data['user_id'] = $this->auth->id;
        $data['add_time'] = time();

        // 验证表单数据
        $check = $this->validate($data, 'RiderRecruit');
        if ($check !== true) {
            $this->error($check,201);
        }

        // 判断当前用户是否已有提交
        $res = RiderRecruitModel::where('user_id','=',$data['user_id'])->value('id');
        if ($res) {
            $this->error('您已提交过申请，快跟客服联系吧');
        }

        // 提交新增表单
        $result = RiderRecruitModel::create($data,true);
        if (!$result) {
            $this->error('添加失败',201);
        }

        $this->success('添加成功');

    }
     
}
