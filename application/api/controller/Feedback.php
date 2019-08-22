<?php

namespace app\api\controller;

use think\Request;
use think\facade\Cache;
use app\common\model\Feedback as FeedbackModel;
use app\common\controller\ApiBase;

class Feedback extends ApiBase
{

    protected  $noNeedLogin = [];


    /**
     * 保存意见反馈表单 
     * 
     */
    public function create(Request $request)
    {
        $data = $request->post();
        $data['add_time'] = time();
        $data['user_id'] = $this->auth->id;

        // 验证表单数据
        $check = $this->validate($data, 'Feedback');
        if ($check !== true) {
            $this->error($check,201);
        }
        
        // 存入缓存，每个用户最多可反馈三次
        $redis = Cache::store('redis');
        var_dump($redis);die;
        $key = "user_feedback";

        if($redis->hExists($key,$data['user_id'])) {
            $count = $redis->hGet($key,$data['user_id']);
            if($count >= 3){  
                $this->error('您已提交多次，我们会竭力改进',202);
            } 
            $redis->hIncrby($key,$data['user_id'],1);
        }else{
            $redis->hSet($key,$data['user_id'],1);
        }
        
        // 提交新增表单
        $result = FeedbackModel::create($data,true);
        if (!$result) {
            $this->error('添加失败',201);
        }

        $this->success('添加成功');
    }
     
}
