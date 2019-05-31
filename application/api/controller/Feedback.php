<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\facade\Cache;
use app\common\model\Feedback as FeedbackModel;

class Feedback extends Controller
{
    /**
     * 保存意见反馈表单 
     * 
     */
    public function create(Request $request)
    {
        $data = $request->post();
        $data['add_time'] = time();

        // 验证表单数据
        $check = $this->validate($data, 'Feedback');
        if ($check !== true) {
            return json_error($check,201);
        }

        // 当用户在一天内提交多余三次，提示“您已提交多次，我们会竭力改进”
        $key = 'feedback_'.$data['user_id'];
        $check = Cache::store('redis')->has($key);  
        if($check){  
            Cache::store('redis')->inc($key);  
            $count = Cache::store('redis')->get($key);  
            if($count > 3){  
                return json_error('您已提交多次，我们会竭力改进');
            }  
        }else{   
            Cache::store('redis')->set($key,1,3600*24);  
        }  
        
        // 提交新增表单
        $result = FeedbackModel::create($data,true);
        if (!$result) {
            return json_error('添加失败',201);
        }

        return json_success('添加成功');
    }
     
}
