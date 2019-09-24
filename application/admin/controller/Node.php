<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Db;
use think\Request;

class Node extends Base
{
    /**
     * 菜单列表
     */
    public function index(Request $request)
    {
        $arr = Db::name("node")->order('fid,sort')->select();
        $list = get_node($arr);
        foreach ($list as $k => $v) {
            if ($list[$k]['fid'] == 0) {
                $list[$k]['fid'] = "无上级分类";
            } else {
                $list[$k]['fid'] = Db::name("node")->where("id",'=', $list[$k]['fid'])->value("name");
            }
        }
        $this->success('获取菜单成功', ['list' => $list]);
    }


    /**
     * 菜单新增
     */
    public function insert(Request $request)
    {
        if (request()->isPost()) {
            $data = $request->param();
            if ($data['fid'] == 0) {
                $data['level'] = 1;
            } else {
                $num = Db::name("node")->where('id','=',$data['fid'])->value("level");
                $data['level'] = $num + 1;
            }
            if (Db::name("node")->insert($data)) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        } else {
            $arr = Db::name("node")->field('id,fid,name')->select();
            $list = get_node($arr);
            $this->success('获取菜单列表',['list'=>$list]);
        }
    }


    /**
     * 菜单修改
     * @param $id
     */
    public function update(Request $request)
    {
        $data = $request->param();
        if (request()->isPost()) {
            if ($data['fid'] == 0) {
                $data['level'] = 1;
            } else {
                $num = Db::name("node")->where("id","=",$data['fid'])->value("level");
                $data['level'] = $num + 1;
            }
            if (Db::name("node")->update($data) !== false) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $arr = Db::name("node")->field('id,fid,name')->select();
            $list = get_node($arr);
            $info = Db::name("node")->find($data['id']);
            $this->success('获取菜单信息成功',['info' => $info, 'list' => $list]);
        }
    }


    /**
     * 菜单删除
     * @param $id
     */
    public function delete(Request $request)
    {
        $id = $request->param('id');
        if (Db::name("node")->where('fid','=',$id)->count()) {
            $this->error('下面有子类，无法删除');
        } else {
            if (Db::name("node")->delete($id)) {
                $this->success('删除成功');                
            } else {
                $this->error('删除失败');
            }
        }
    }


    /**
     * 菜单排序
     */
    public function sortUpdate(Request $request)
    {
        $sorts = $request->param('sorts');
        $data = json_decode($sorts,true);

        // 启动事务
        Db::startTrans();
        try {
            foreach ($data as $k => $v) {
                Db::name('node')->update($v);
            }
            // 提交事务
            Db::commit();
            $this->success("分类排序更新成功");
         } catch (\think\Exception\DbException $e) {
             // 回滚事务
             Db::rollback();
             $this->error("分类排序更新失败");
         }

    }
        
}
