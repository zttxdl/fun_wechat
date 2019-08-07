<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;

/**
 * 广告控制器
 * @author Billy
 * date 2019/8/7
 */

class Advert extends Base
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        $name = input('name','');
        if ($name == ''){
            $list = model('Advert')->order('id', 'desc')->select();
        }else{
            $list = model('Advert')
                ->where('title','like','%'.$name.'%')
                ->order('id', 'desc')
                ->select();
        }

        $this->success('success',$list);
    }


    /**
     * 保存新建的资源
     * @param  \think\Request  $request
     */
    public function save(Request $request)
    {
        $data = $request->param();
        $ret = model('Advert')->save($data);
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

        $data = model('Advert')->where('id',$id)->find();
        $this->success('success',$data);
    }


    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        if (!$id){
            $this->error('非法参数');
        }

        $data = $request->param();
        $where = ['id','=',$id];
        $ret = model('Advert')->save($data,$where);
        if (!$ret){
            $this->error('修改失败');
        }

        $this->success('success');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id){
            $this->error('非法参数');
        }

        $ret = model('Advert')->destroy($id);
        if (!$ret){
            $this->error('删除失败');
        }
        $this->success('success');
    }
}
