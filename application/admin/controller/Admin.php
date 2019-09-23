<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;

class Admin extends Base
{

/**
     * 管理员列表
     * @param  array  $where  条件
     */
    public function index(Request $request)
    {
        // 搜索条件
        !empty(input('name')) ? $where["a.name"] = ['like', $request->param("name") . "%"] : null;
        !empty(input('phone')) ? $where["a.phone"] = ['like', $request->param("phone") . "%"] : null;

        $list = Db::name("admin a")->join('role r ', 'r.id = a.role_id')->order('a.role_id')->where($where)->field("a.*,r.name as r_name")->paginate();
        $this->success('获取管理员列表成功',['list'=>$list]);
    }


    /**
     * 管理员新增
     */
    public function insert(Request $request)
    {
        if (request()->isPost()) {
            $data = $request->param();
            if (Db::name("admin")->where(['name' => $data['name']])->value('id')) {
                $this->error('管理员名称已存在！');
            } else {
                // 表单校验
                $check = $this->validate($data, 'Admin');
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
            }
        } else {
            $list = Db::name("role")->select();
            $this->success('获取角色列表',['list'=>$list]);
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
            if (Db::name("admin")->where(['name' => $data['name'], 'id' => ['neq', $data['id']]])->value('id')) {
                return json(['code' => 201, 'msg' => '用户名已存在！']);
            } else {
                // 表单校验
                $check = $this->validate($data, 'Admin');
                if ($check !== true) {
                    $this->error($check,201);
                }
                if (Db::name("admin")->where(['id' => $data['id']])->update($data) !== false) {
                    $this->success('修改成功');
                } else {
                    $this->error('修改失败');
                }
            }
        } else {
            $info = Db::name("admin")->find($data['id']);
            $list = Db::name("role")->select();
            return $this->fetch('', ['info' => $info, 'list' => $list]);
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
        if (Db::name("admin")->where(['id' => $id])->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');            
        } 
    }



}
