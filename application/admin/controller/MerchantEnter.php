<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;


/**
 * 意向商家控制器
 * @author Mike
 * date 2019/5/27
 */
class MerchantEnter extends Controller
{
    /**
     * 意向商家列表
     *
     */
    public function index(Request $request)
    {
        //搜索条件
        $where = [];
        !empty($request->get('status/d')) ? $where[] = ['m.status','=',$request->get('status/d')] :  null;
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 10;

        $list = model('MerchantEnter')->alias('m')->join('manage_category mc','m.manage_category_id = mc.id')
                ->join('user u','m.user_id = u.id')->join('school s','m.school_id = s.id')
                ->field('m.id,mc.name as mc_name,m.name,m.phone,m.add_time,m.status,u.nickname,s.name as school_name')->append(['mb_status'])->order('m.id desc')
                ->where($where)->paginate($pagesize);
        $this->success('ok',['list'=>$list]);
    }

    /**
     * 设置意向商家的状态
     *
     */
    public function status($id,$status)
    {
        $result = Db::name('merchant_enter')->where('id','=',$id)->setField('status',$status);

        if (!$result) {
            $this->error('设置失败');
        }
        
        $this->success('ok');

    }

}
