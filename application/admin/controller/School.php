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
        $data['longitude'] = $request->param('longitude');
        $data['latitude'] = $request->param('latitude');
        $data['completion_time'] = $request->param('completion_time');
        $data['fetch_time'] = $request->param('fetch_time');
        $canteen = $request->param('canteen');

        // 验证表单数据
        $check = $this->validate($data, 'School');
        if ($check !== true) {
            $this->error($check,201);
        }

        // 验证学校是否重名
        $count = Db::name('school')->where('name','=',$data['name'])->count();
        if ($count) {
            $this->error('学校名称已存在！');
        }

        // 启动事务
        Db::startTrans();
        try {
            // 添加学校
            $school_id = Db::name('school')->insertGetId($data);
            $canteen_list = json_decode($canteen,true);
            if (!empty($canteen_list)) {
                foreach ($canteen_list as $k => &$v) {
                    $v['school_id'] = $school_id;
                    $v['cleartext'] = $v['password'];
                    $v['password'] = md5($v['password']);
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
        $info = Db::name('school')->where('id','=',$id)->field('id,name,fid,latitude,longitude,completion_time,fetch_time')->find();
        $info['cname'] = Db::name('school')->where('id','=',$info['fid'])->value('name');
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
        $data['longitude'] = $request->param('longitude');
        $data['latitude'] = $request->param('latitude');
        $data['completion_time'] = $request->param('completion_time');
        $data['fetch_time'] = $request->param('fetch_time');
        $canteen = $request->param('canteen');

        // 验证表单数据
        $check = $this->validate($data, 'School');
        if ($check !== true) {
            $this->error($check,201);
        }

        // 验证学校是否重名
        $count = Db::name('school')->where([['name','=',$data['name']],['id','<>',$data['id']]])->count();
        if ($count) {
            $this->error('学校名称已存在！');
        }

         // 启动事务
         Db::startTrans();
         try {
             // 修改学校
             Db::name('school')->update($data);
             $canteen_list = json_decode($canteen,true);
             if (!empty($canteen_list)) {
                 foreach ($canteen_list as $k => $v) {
                    // 修改食堂
                    $v['password'] = md5($v['cleartext']);
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
        $info = Db::name('school')->where('id','=',$id)->field('fid,name,longitude,latitude,completion_time,fetch_time')->find();
        $info['area'] = Db::name('school')->where('id','=',$info['fid'])->value('name');
        $canteen_list = Db::name('canteen')->where('school_id','=',$id)->field('id,name,cut_proportion,account,withdraw_cycle,cleartext')->select();

        $this->success('获取编辑学校信息成功',['info'=>$info,'canteen_list'=>$canteen_list]);
    }
     

    /**
     *  删除学校
     * 
     */
    public function delete($id)
    {
        $count = Db::name('shop_info')->where('school_id','=',$id)->count();
        if ($count) {
            $this->error("当前学校已有商家入驻，不可删除");
        }

        // 启动事务
        Db::startTrans();
        try {
            // 删除学校 以及食堂
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
    }
    /**
     * 楼栋展示
     */
    public function getHourse(Request $request)
    {
        $page = $request->param('page');
        $page_size = $request->param('pageSize');
        $school_id = $request->param('schoolId');

        $list = Db::name('Hourse')
        ->field('id,fid,name,school_id')
        ->where('school_id',$school_id)
        ->select();
        $list = get_node($list);
        foreach ($list as $k => $v) {
            if ($list[$k]['fid'] == 0) {
                $list[$k]['fid'] = "无上级分类";
            } else {
                $list[$k]['fid'] = Db::name("Hourse")->where("id",'=', $list[$k]['fid'])->value("name");
            }
        }
        $this->success('获取菜单成功', ['list' => $list]);
    }
    
    /**
     * 新增楼栋
     */
    public function addHourse(Request $request)
    {
        $data = $request->param();
        if($request->isPost()){
            // 验证表单数据
            $check = $this->validate($data, 'Hourse.addHourse');
            if ($check !== true) {
                $this->error($check,201);
            }
            if (Db::name("Hourse")->insert($data)) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        }else{
            $arr = Db::name('Hourse')->where('school_id',$data['school_id'])->field('id,fid,name,school_id')->select();  
            $list = get_node($arr);
            $this->success('楼栋列表获取成功',$list);
        }
    }

    /**
     * 编辑楼栋
     */
    public function updateHourse(Request $request)
    {
        $data = $request->param(); 
        if ($request->isPost()) {
            // 表单校验
            $check = $this->validate($data, 'Hourse.updateHourse');
            if ($check !== true) {
                $this->error($check,201);
            }
            if (Db::name("Hourse")->where(['id' => $data['id']])->update($data)) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $arr = Db::name("Hourse")->where('school_id',$data['school_id'])->field('id,fid,level,name')->select();
            $info = Db::name('Hourse')->where('id',$data['id'])->find();
            $list = get_node($arr);
            $this->success('楼栋列表获取成功',['list'=>$list,'info'=>$info]);
        }
    }

    /**
     * 删除楼栋
     */
    public function deleteHourse(Request $request)
    {
        $id = $request->param('id');
        $count = Db::name('Hourse')->where('fid','=',$id)->count();
        if ($count) {
            $this->error('有子类无法删除!'); 
        }
        if (Db::name("Hourse")->where('id','=',$id)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');            
        } 
    }

}
