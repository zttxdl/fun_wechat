<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use think\Db;


/**
 * 反馈建议控制器
 * @author Mike
 * date 2019/5/27
 */
class Feedback extends Base
{
    /**
     * 反馈建议列表
     */
    public function index(Request $request)
    {
        //搜索条件
        $where = [];
        !empty($request->get('status/d')) ? $where[] = ['f.status','=',$request->get('status/d')] : null;
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 10;
        
        $list = model('feedback')->alias('f')->join('user u','f.user_id = u.id')->where($where)->order('f.id desc')->append(['mb_status'])->field('f.content,f.add_time,f.status,f.id,u.nickname,u.phone')
                ->paginate($pagesize);

        $this->success('ok',['list'=>$list]);
    }


    /**
     * 反馈详情
     * @param $id  反馈建议表主键值
     */
    public function show($id)
    {
        $info = model('feedback')->alias('f')->join('user u','f.user_id = u.id')->where('f.id',$id)->append(['mb_status'])->field('f.*,u.nickname,u.phone')->find();
        
        // 图片 【如果存在， 转化为数组】
        if ($info['imgs']) {
            $info['imgs'] = explode(',',$info['imgs']);
        } else {
            $info['imgs'] = [];
        }

        $this->success('ok',['info'=>$info]);
    }


    /**
     * 设置反馈状态
     * @param $id  反馈建议表主键值
     * @param $status  状态值
     */
    public function status($id,$status)
    {
        $result = Db::name('feedback')->where('id',$id)->setField('status',$status);
        
        if (!$result) {
            $this->error('设置失败');
        }
        
        $this->success('设置成功');
    }
     

}
