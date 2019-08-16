<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use think\Db;

class School extends Base
{
    
    /**
     *  学校列表
     * 
     */
    public function index(Request $request)
    {
        // 搜索条件
        $where[] = ['level','=',2];
        !empty($request->param('name')) ? $where[] = ['name','like',"%".$request->param('name')."%"] : null;
        !empty($request->param('fid')) ? $where[] = ['fid','=',$request->param('fid')] : null;
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 10;

        $list = Db::name('school')->where($where)->order('id','asc')->field('id,name,fid')->paginate($pagesize)->each(function ($item, $key) {
            // 区域名称
            $item['area'] = Db::name('school')->where('id','=',$item['fid'])->value('name');
            // 包含食堂
            $item['canteen'] = implode(',',Db::name('canteen')->where('school_id','=',$item['id'])->column('name'));
           
            return $item;
        });

        $this->success('获取学校列表成功',['list'=>$list]);
    }


    /**
     * 展示新增学校 
     * 
     */
    public function add()
    {
        $list = Db::name('school')->where('level','=',1)->field('id,name')->select();
        
        $this->success('获取学校区域成功',['list'=>$list]);
    }
     

    /**
     *  保存新增学校
     * 
     */
    public function insert(Request $request)
    {
        $data['fid'] = $request->param('fid');
        $data['name'] = $request->param('name');
        $canteen = $request->param('canteen');

        $canteen_list = json_decode($canteen,true);

        var_dump($canteen);
        var_dump($canteen_list);die;

        // 验证表单数据
        $check = $this->validate($data, 'School');
        if ($check !== true) {
            $this->error($check,201);
        }
        
        // 获取经纬度信息
        $area_name = Db::name('school')->where('id','=',$data['fid'])->value('name');
        $long_lat = get_location('南京市'.$area_name.$data['name']);
        if (empty($long_lat)) {
            $this->error('地址解析出现问题，请确认学校名称填写是否正确');
        }
        $data['longitude'] = $long_lat['lng'];
        $data['latitude'] = $long_lat['lat'];
        
        // 启动事务
        Db::startTrans();
        try {
            // 添加学校
            $school_id = Db::name('school')->insertGetId($data);
            $canteen_list = json_decode($canteen,true);
            if (!empty($canteen_list)) {
                foreach ($canteen_list as $k => &$v) {
                    $v['school_id'] = $school_id;
                }
                unset($v);
                // 添加食堂
                Db::name('canteen')->insertAll($canteen_list);
            }
            
            // 提交事务
            Db::commit();
            $this->success("添加学校成功");
        } catch (\think\Exception\DbException $e) {
            // 回滚事务
            Db::rollback();
            $this->error("添加学校失败");
        }
        
    }


    /**
     *  展示编辑学校
     * 
     */
    public function edit($id)
    {
        $info = Db::name('school')->where('id','=',$id)->field('id,name,fid')->find();
        $area_list = Db::name('school')->where('level','=',1)->field('id,name')->select();
        $canteen_list = Db::name('canteen')->where('school_id','=',$id)->select();

        $this->success('获取编辑学校信息成功',['info'=>$info,'area_list'=>$area_list,'canteen_list'=>$canteen_list]);
    }

    /**
     *  保存编辑学校
     * 
     */
    public function update(Request $request)
    {
        $data['id'] = $request->param('id');
        $data['fid'] = $request->param('fid');
        $data['name'] = $request->param('name');
        $canteen = $request->param('canteen');
        
        // 验证表单数据
        $check = $this->validate($data, 'School');
        if ($check !== true) {
            $this->error($check,201);
        }
        
        // 获取经纬度信息
        $area_name = Db::name('school')->where('id','=',$data['fid'])->value('name');
        $long_lat = get_location('南京市'.$area_name.$data['name']);
        if (empty($long_lat)) {
            $this->error('地址解析出现问题，请确认学校名称填写是否正确');
        }
        $data['longitude'] = $long_lat['lng'];
        $data['latitude'] = $long_lat['lat'];
        
        // 启动事务
        Db::startTrans();
        try {
            // 修改学校
            Db::name('school')->update($data);
            $canteen_list = json_decode($canteen,true);
            if (!empty($canteen_list)) {
                foreach ($canteen_list as $k => $v) {
                    // 修改食堂
                    Db::name('canteen')->update($v);
                }
            }
            
            // 提交事务
            Db::commit();
            $this->success("修改学校成功");
        } catch (\think\Exception\DbException $e) {
            // 回滚事务
            Db::rollback();
            $this->error("修改学校失败");
        }
    }


    /**
     * 学校详情 
     * 
     */
    public function show($id)
    {
        $info = Db::name('school')->where('id','=',$id)->field('fid,name')->find();
        $info['area'] = Db::name('school')->where('id','=',$info['fid'])->value('name');
        $canteen_list = Db::name('canteen')->where('school_id','=',$id)->field('id,name,cut_proportion')->select();

        $this->success('获取编辑学校信息成功',['info'=>$info,'canteen_list'=>$canteen_list]);
    }
     

    /**
     *  删除学校
     * 
     */
    public function delete($id)
    {

        // 启动事务
        Db::startTrans();
        try {
            // 修改学校
            Db::name('school')->delete($id);
            Db::name('canteen')->where('school_id','=',$id)->delete();
            
            // 提交事务
            Db::commit();
            $this->success("删除成功");
        } catch (\think\Exception\DbException $e) {
            // 回滚事务
            Db::rollback();
            $this->error("删除失败");
        }


        $result = Db::name('school')->delete($id);
    }

}
