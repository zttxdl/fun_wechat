<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;

/**
 * 广告位控制器
 * @author Billy
 * date 2019/8/7
 */

class AdvertPosition extends Base
{
    /**
     * 显示资源列表
     */

    public function index()
    {
        $name = input('name','');
        if ($name == ''){
            $list = model('AdvertPosition')->order('id', 'desc')->select();
        }else{
            $list = model('AdvertPosition')
                ->where('name','like','%'.$name.'%')
                ->order('id', 'desc')
                ->select();
        }

        $this->success('success',$list);
    }

    /**
     * 新建资源
     *
     * @param  \think\Request  $request
     */
    public function save(Request $request)
    {
        $data = $request->param();
        $ret = model('AdvertPosition')->save($data);
        if (!$ret){
            $this->error('添加失败');
        }
        $this->success('success');
    }

    /**
     * 显示指定的资源
     */
    public function read($id)
    {
        if (!$id){
            $this->error('非法参数');
        }

        $data = model('AdvertPosition')->where('id',$id)->find();
        $this->success('success',$data);
    }


    /**
     * 更新资源
     * @param  \think\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id)
    {
        if (!$id){
            $this->error('非法参数');
        }

        $data = $request->param();
        $where = ['id','=',$id];
        $ret = model('AdvertPosition')->save($data,$where);
        if (!$ret){
            $this->error('修改失败');
        }

        $this->success('success');
    }

    /**
     * 删除指定资源
     */
    public function delete($id)
    {

        if (!$id){
            $this->error('非法参数');
        }

        $ret = model('AdvertPosition')->destroy($id);
        if (!$ret){
            $this->error('删除失败');
        }
        $this->success('success');
    }
}
