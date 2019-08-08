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
        $page = input('page',1);
        $pagesize = input('pagesize',20);
        if ($name == ''){
            $list = model('Advert')
                ->order('id', 'desc')
                ->page($page,$pagesize)
                ->select();
        }else{
            $list = model('Advert')
                ->where('title|advert_name','like','%'.$name.'%')
                ->order('id', 'desc')
                ->page($page,$pagesize)
                ->select();
        }
        if ($list){
            foreach ($list as $val){
                $val->start_time = date('Y/m/d',$val->start_time);
                $val->end_time = date('Y/m/d',$val->end_time);

            }
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
        $advert_id = $request->param('advert_id');
        $coverage = $request->param('coverage');
        $start_time = $request->param('start_time');
        $end_time = $request->param('end_time');
        //获取该广告位已增加数量
        if ($coverage !== 0){
            $where[] = ['coverage','in',['0',$coverage]];
        }

        $where[] = ['advert_id','=',$advert_id];
        $count = model('Advert')->where($where)->count();
        $num = model('AdvertPosition')->where('id',$advert_id)->value('num');

        if ($count >= $num){
            $this->error('超出广告位限制',202);
        }

        $data['start_time'] = strtotime($start_time);
        $data['end_time'] = strtotime($end_time);
        $data['add_time'] = time();
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
        if ($data){
            $data->start_time = date('Y/m/d',$data->start_time);
            $data->end_time = date('Y/m/d',$data->end_time);
        }

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
        $start_time = $request->param('start_time');
        $end_time = $request->param('end_time');
        if ($start_time){
            $data['start_time'] = strtotime($start_time);
        }

        if ($end_time){
            $data['end_time'] = strtotime($end_time);
        }

        $ret = model('Advert')->where('id',$id)->update($data);

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
