<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use think\Db;

class Admin extends Base
{

    /**
     * 管理员列表
     * @param  array  $where  条件
     */
    public function index(Request $request)
    {
        // 搜索条件
        $keyword = $request->param('keyword');
        !empty($keyword) ? $where[] = ['a.phone|a.name','like', $keyword . "%"] : null;
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 10;
        
        $list = model("admin")->alias('a')->join('role r ', 'r.id = a.role_id')->order('a.role_id,a.id')->where($where)->field("a.id,a.name,a.phone,a.create_time,a.last_login_time,r.name as r_name")->paginate($pagesize);
        $this->success('获取管理员列表成功',['list'=>$list]);
    }


    /**
     * 管理员新增
     */
    public function insert(Request $request)
    {
        if (request()->isPost()) {
            $data = $request->param();
            // 表单校验
            $check = $this->validate($data, 'Admin.edit');
            if ($check !== true) {
                $this->error($check,201);
            }
            $data['create_time'] = time();
            $data['password'] = md5('DaiGeFan@888');
            if (Db::name("admin")->insert($data)) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        } else {
            $list = Db::name("role")->field('id,name')->select();
            $school_list = model('School')->where('level','=',2)->field('id,name')->select();
            $this->success('获取角色列表',['list'=>$list,'school_list'=>$school_list]);
        }
    }


    /**
     * 管理员修改
     * @param int $id  主键值 
     */
    public function update(Request $request)
    {
        $data = $request->param(); 
        if (request()->isPost()) {
            // 表单校验
            $check = $this->validate($data, 'Admin.edit');
            if ($check !== true) {
                $this->error($check,201);
            }
            if (Db::name("admin")->where('id','=',$data['id'])->update($data) !== false) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $info = Db::name("admin a")->join('role r','a.role_id = r.id')->field('a.id,a.name,a.phone,a.role_id,r.name as role_name')->where('a.id','=',$data['id'])->find();
            $list = Db::name("role")->field('id,name')->select();
            $school_list = model('School')->where('level','=',2)->field('id,name')->select();
            $this->success('获取管理员信息成功',['info' => $info, 'list' => $list,'school_list'=>$school_list]);
        }
    }


    /**
     * 管理员更改状态
     * @param $id
     * @param $status 
     */
    public function delete(Request $request)
    {
        $id = $request->param('id');
        if (Db::name("admin")->where('id','=',$id)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');            
        } 
    }


    /**
     * 修改密码
     * @param $id
     * @param $status 
     */
    public function setPassword(Request $request)
    {
        $data = $request->param();
        $id = $request->param('id');

        $check = $this->validate($data, 'Admin.pwd');
        if ($check !== true) {
            $this->error($check,201);
        }
        $old_pwd = Db::name('admin')->where('id','=',$id)->value('password');
        if ($old_pwd != md5($data['old_password'])) {
            $this->error('原密码错误');
        }

        if (Db::name("admin")->where('id','=',$id)->setField('password',md5($data['password'])) !== false) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败');            
        } 
    }

}
