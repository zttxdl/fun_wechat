<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Db;
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
        $role_info = Db::name('role')->where('id','=',session('admin_user.role_id'))->field('name')->find();
        // 获取当前角色的所有权限信息
        $node_list_1 = Db::name('node')->where([['id','in',$role_info['node_ids']],['level','=',1]])->order('fid,sort')->select();
        $node_list_2 = Db::name('node')->where([['id','in',$role_info['node_ids']],['level','=',2]])->order('fid,sort')->select();

        $arr = [];
        foreach ($node_list_1 as $k => $v) {
            $arr[$k]['id'] = $v['id'];
            $arr[$k]['name'] = $v['name'];
            $arr[$k]['linu_url'] = $v['linu_url'];
            $arr[$k]['fid'] = $v['fid'];
            $arr[$k]['level'] = $v['level'];
            $arr[$k]['sort'] = $v['sort'];
            $arr[$k]['sort'] = $v['sort'];
            $arr[$k]['show'] = false;

            foreach ($node_list_2 as $kk => $vv) {
                if ($v['id'] == $vv['fid']) {
                    $arr[$k]['children'][] = $vv;
                }
            }
        }
        $role_info['id'] = session('admin_user.id');
        
        // var_dump($arr);die;
        // 记录登录日志【暂时不清楚日志的具体存储内容，此块功能先屏蔽】
        // $data = ['login_time' => time(), 'login_ip' => Request::instance()->ip(), 'name' => session('admin_user.name'), 'phone' => session('admin_user.phone'),'role_name' => $role_info['name']];
        // Db::name("login_log")->insert($data);
 
        $this->success('获取权限成功',['role_info'=>$role_info,'node_list'=>$arr]);


    }

}
