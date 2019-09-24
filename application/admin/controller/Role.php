<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use think\Db;

class Role extends Base
{
/**
     * 角色列表
     * @param  array  $where  条件
     */
    public function index(Request $request)
    {
        // 搜索条件
        $keyword = $request->param('keyword');
        !empty($keyword) ? $where[] = ['name','like', $keyword . "%"] : null;

        $list = Db::name("role")->where($where)->field('id,name,depict')->select();
        $this->success('获取角色列表成功',['list'=>$list]);
    }


    /**
     * 角色新增
     */
    public function insert(Request $request)
    {
        if (request()->isPost()) {
            $data = $request->param();
            // 表单校验
            $check = $this->validate($data, 'Role');
            if ($check !== true) {
                $this->error($check,201);
            }
            if (Db::name("role")->insert($data)) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        } else {
            $arr = Db::name("node")->field('id,fid,level,name')->select();
            $list = get_node($arr);
            $this->success('获取角色列表',['list'=>$list]);
        }
    }


    /**
     * 角色修改
     * @param int $id  主键值 
     */
    public function update(Request $request)
    {
        $data = $request->param(); 
        if (request()->isPost()) {
            // 表单校验
            $check = $this->validate($data, 'Role');
            if ($check !== true) {
                $this->error($check,201);
            }
            if (Db::name("role")->where(['id' => $data['id']])->update($data) !== false) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $info = Db::name("role")->find($data['id']);
            $arr = Db::name("node")->field('id,fid,level,name')->select();
            $list = get_node($arr);
            $this->success('获取角色信息成功',['info' => $info, 'list' => $list]);
        }
    }


    /**
     * 角色删除
     * @param $id
     * @param $status 
     */
    public function delete(Request $request)
    {
        $id = $request->param('id');
        $count = Db::name('admin')->where('role_id','=',$id)->count();
        if ($count) {
            $this->error('该角色下有管理员，暂不可以删除'); 
        }
        if (Db::name("admin")->where(['id' => $id])->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');            
        } 
    }
}
