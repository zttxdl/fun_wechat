<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;


/**
 * 意向骑手控制器
 * @author Mike
 * date 2019/5/27
 */
class RiderRecruit extends Controller
{
    /**
     * 意向骑手列表
     *
     */
    public function index(Request $request)
    {
        //搜索条件
        $where = [];
        !empty($request->get('status/d')) ? $where[] = ['r.status','=',$request->get('status/d')] : null;

        $list = Db::name('rider_recruit r')->join('user u','r.user_id = u.id')->join('school s','r.school_id = s.id')
                ->field('r.id,r.phone,r.add_time,r.status,u.nickname,s.name as school_name')->order('r.id desc')
                ->where($where)->paginate(10)->each(function ($item, $key) {
                    $item['add_time'] = date('Y-m-d H:i:s',$item['add_time']);
                    $item['mb_status'] = config('dispose_status')[$item['status']];
                    return $item;
                });
        $this->success('ok',['list'=>$list]);
    }

    /**
     * 设置意向骑手的状态
     * @param $id  骑手意向表主键值
     * @param $status  状态值
     */
    public function status($id,$status)
    {
        $result = Db::name('rider_recruit')->where('id',$id)->setField('status',$status);
        
        if (!$result) {
            $this->error('设置失败');
        }
        
        $this->success('ok');
    }

}
