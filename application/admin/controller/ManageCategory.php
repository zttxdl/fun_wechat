<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use think\Db;


class ManageCategory extends Base
{

    /**
     * 获取经营品类列表 
     * 
     */
    public function index()
    {
        $list = model('ManageCategory')->field('id,name,img,sort')->order('sort','asc')->select();
        $this->success('获取成功',['list'=>$list]);

    }


    /**
     * 新建品类 
     * 
     */
    public function insert(Request $request)
    {
        $data = $request->param();
        // 验证表单数据
        $check = $this->validate($data, 'ManageCategory');
        if ($check !== true) {
            $this->error($check,201);
        }
        // 添加数据库
        $result = model('ManageCategory')->create($data);
        if (!$result) {
            $this->error('添加失败',201);
        }
        $this->success('添加成功');

    }


    /**
     * 展示修改经营品类 
     * 
     */
    public function edit($id)
    {
        if (empty($id)) {
            $this->error('非法参数',201);
        }
        $info = model('ManageCategory')->where('id','=',$id)->find();
        $this->success('获取成功',['info'=>$info]);
    }


    /**
     * 保存修改经营品类 
     * 
     */
    public function update(Request $request)
    {
        $data = $request->param();

        // 验证表单数据
        $check = $this->validate($data, 'ManageCategory');
        if ($check !== true) {
            $this->error($check,201);
        }

        // 判断图片是否更换
        $img = model('ManageCategory')->where('id','=',$data['id'])->value('img');
        if ($img != $data['img']) {
            // 删除七牛云上面的 图片名称为 $img 的图片物理地址
            qiniu_img_del($img);
        }

        // 修改数据库
        $result = Db::name('manage_category')->update($data);
        if (!$result) {
            $this->error('修改失败',201);
        }
        $this->success('修改成功');
    }



    /**
     * 删除经营品类 
     * 
     */
    public function delete($id)
    {
        if (empty($id)) {
            $this->error('非法参数',201);
        }
        $img = model('ManageCategory')->where('id','=',$id)->value('img');

        $result = Db::name('manage_category')->delete($id);
        if (!$result) {
            $this->error('删除失败');
        }

        // 调用七牛云的删除图片方法，删除图片存储地址
        qiniu_img_del($img);
        
        $this->success('删除成功');

    }
     


     


    
     

}
