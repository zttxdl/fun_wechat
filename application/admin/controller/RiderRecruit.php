<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use think\Db;


/**
 * 意向骑手控制器
 * @author Mike
 * date 2019/5/27
 */
class RiderRecruit extends Base
{
    /**
     * 意向骑手列表
     *
     */
    public function index(Request $request)
    {
        //搜索条件
        $where = [];
        !empty($request->get('name/s')) ? $where[] = ['u.nickname','like',$request->get('name/s').'%'] : null;
        !empty($request->get('status/d')) ? $where[] = ['r.status','=',$request->get('status/d')] : null;
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 10;

        $list = model('RiderRecruit')->alias('r')->join('user u','r.user_id = u.id')->join('school s','r.school_id = s.id')
                ->field('r.id,r.phone,r.add_time,r.status,u.nickname,s.name as school_name')->append(['mb_status'])->order('r.id desc')
                ->where($where)->paginate($pagesize);
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
        
        $this->success('设置成功');
    }

}
