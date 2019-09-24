<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;

class Index extends Base
{
    /**
     * 首页展示
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        // 获取当前角色的记录
        $role_info = Db::name('role')->where('id','=',session('admin_account.role_id'))->field('node_ids,name')->find();

        // 获取当前角色的所有权限信息
        $node_list = Db::name('node')->where('id','in',$role_info['node_ids'])->order('fid,sort')->select();
        
        // 记录登录日志【暂时不清楚日志的具体存储内容，此块功能先屏蔽】
        // $data = ['login_time' => time(), 'login_ip' => Request::instance()->ip(), 'name' => session('admin_account.name'), 'phone' => session('admin_account.phone'),'role_name' => $role_info['name']];
        // Db::name("login_log")->insert($data);

        $this->success('获取权限成功',['role_info'=>$role_info,'node_list'=>$node_list]);


    }

}
