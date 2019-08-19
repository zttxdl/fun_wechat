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
        $pagesize = input('pagesize',20);
        $where = [];
        !empty($name) ?  $where[] = ['title|advert_name','like','%'.$name.'%'] : null;

        $school = model('School')
            ->field('id,name')
            ->where('level',2)
            ->select()
            ->toArray();

        $schoolName = array_reduce($school,function(&$schoolName,$v){
            $schoolName[$v['id']] = $v['name'];
            return $schoolName;
        });

        $statusName = [
            '0'=>'暂未投放',
            '1'=>'投放中',
            '2'=>'暂停投放',
            '3'=>'已过期',
        ];
        $list = model('Advert')
            ->where($where)
            ->order('id', 'desc')
            ->paginate($pagesize);

        if ($list){
            foreach ($list as $val) {
                $val->time =  date('Y/m/d',$val->start_time).'-'.date('Y/m/d',$val->end_time);
                $val->coverage = $val->coverage == 0 ? '全部' : $schoolName[$val->coverage];
                $val->bool = $statusName[$val->status];
                if ($val->status != 3){
                    $val->rest = ceil(($val->end_time - time()) / 86400);
                }else{
                    $val->rest = 0 ;
                }
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
        if ($coverage != 0){
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
        if ($data['start_time'] <= time()){
            $data['status'] = 1;
        }

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

        $school = model('School')
            ->field('id,name')
            ->where('level',2)
            ->select()
            ->toArray();

        $schoolName = array_reduce($school,function(&$schoolName,$v){
            $schoolName[$v['id']] = $v['name'];
            return $schoolName;
        });

        $data = model('Advert')->where('id',$id)->find();
        $typeName = [
            '1'=>'商家广告',
            '2'=>'外链广告',
            '3'=>'静态图'
        ];
        if ($data){
            $data->start_time = date('Y-m-d',$data->start_time);
            $data->end_time = date('Y-m-d',$data->end_time);
            $data->coverage = $data->coverage == 0 ? '全部' : $schoolName[$data->coverage];
            $data->type_name = $typeName[$data->type];
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
        if ($data['end_time'] > time()) {
            $data['status'] = 1;
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

    /**
     * 获取覆盖范围
     */
    public function getSchool()
    {
        // 学校列表
        $school_list = model('school')->getSchoolList();
        $all = [
            'id'=> 0,
            'label'=>'全部',
            'value'=>'全部'
        ];
        array_unshift($school_list,$all);

        $this->success('success',$school_list);
    }

}
