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

        // 当用户在一天内提交多余三次，提示“您已提交多次，我们会竭力改进”
        $key = 'feedback_'.$this->auth->id;
        $check = Cache::store('redis')->has($key);  
        if($check){  
            Cache::store('redis')->inc($key);  
            $count = Cache::store('redis')->get($key);  
            if($count > 3){  
                $this->error('您已提交多次，我们会竭力改进',202);
            }  
        }else{   
            Cache::store('redis')->set($key,1,3600*24);  
        }  
        
        // 提交新增表单
        $result = FeedbackModel::create($data,true);
        if (!$result) {
            $this->error('添加失败',201);
        }

        $this->success('添加成功');
    }
     
}
