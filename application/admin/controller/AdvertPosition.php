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
    protected  $status = [
        '1'=>'是',
        '2'=>'否'
     ];

    /**
     * 显示资源列表
     */
    public function index()
    {
        $name = input('name','');
        !empty($name) ? $where[] = ['name','like','%'.$name.'%'] : null;
        $list = model('AdvertPosition')->where($where)->order('id', 'desc')->select();

        if ($list){
            foreach ($list as $val){
                $val->bool = $this->status[$val->status];
            }
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

        // 验证表单数据
        $check = $this->validate($data, 'AdvertPosition');
        if ($check !== true) {
            $this->error($check,201);
        }

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
        if ($data){
            $data->bool = $this->status[$data->status];
        }

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
        // 验证表单数据
        $check = $this->validate($data, 'AdvertPosition');
        if ($check !== true) {
            $this->error($check,201);
        }
                
        $ret = model('AdvertPosition')->where('id',$id)->update($data);

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
        $data = model('Advert')->get(['advert_id'=>$id]);
        if ($data){
            $this->error('禁止删除，该广告位下有广告');
        }

        $ret = model('AdvertPosition')->destroy($id);
        if (!$ret){
            $this->error('删除失败');
        }
        $this->success('success');
    }

    /**
     * 广告位名称
     */
    public function getAdvertList()
    {
        $list = model('AdvertPosition')
            ->field('id,name')
            ->where('status',1)
            ->order('id', 'desc')
            ->select();

        $this->success('success',$list);
    }
}
