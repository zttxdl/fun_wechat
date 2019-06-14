<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;


/**
 * 反馈建议控制器
 * @author Mike
 * date 2019/5/27
 */
class Feedback extends Controller
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

        $list = Db::name('feedback f')->join('user u','f.user_id = u.id')->where($where)->order('f.id desc')->field('f.content,f.add_time,f.status,f.id,u.nickname,u.phone')
                ->paginate($pagesize)->each(function ($item, $key) {
                    // 状态
                    $item['mb_status'] = config('dispose_status')[$item['status']];
                    // 日期
                    $item['add_time'] = date('Y-m-d',$item['add_time']);

                    return $item;
                });

        $this->success('ok',['list'=>$list]);
    }

    /**
     * 反馈详情
     * @param $id  反馈建议表主键值
     */
    public function show($id)
    {
        $info = Db::name('feedback f')->join('user u','f.user_id = u.id')->where('f.id',$id)->field('f.*,u.nickname,u.phone')->find();
        
        if (!$info) {
            $this->error('非法参数');
        }
        $info['mb_status'] = config('dispose_status')[$info['status']];
        $info['add_time'] = date('Y-m-d',$info['add_time']);

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
        
        $this->success('ok');
    }
     

}
