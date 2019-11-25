<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Db;
use think\Model;
use think\Request;

class ShopDiscounts extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index($id)
    {
        $list = Db::name('shop_discounts')->where([['shop_id','=',$id],['delete','=',0]])->field('id,face_value,threshold,platform_assume,shop_id')->order('threshold')->select();

        $this->success('获取商家满减成功',['list'=>$list]);
    }


    /**
     * insert 
     * 
     */
    public function insert(Request $request)
    {
        $data = $request->post();

        // 验证表单数据
        $check = $this->validate($data, 'ShopDiscounts');
        if ($check !== true) {
            $this->error($check,201);
        }

        // 判断数量不得大于3个
        if (Db::name('shop_discounts')->where([['shop_id','=',$data['shop_id']],['delete','=',0]])->count() >= 3) {
            $this->error('满减活动不可超过3个');
        }
        $data['create_time'] = time();

        $ret = Db::name('shop_discounts')->insert($data);
        if (!$ret){
            $this->error('添加失败');
        }
        $this->success('添加成功');
    }


    /**
     * edit 
     * 
     */
    public function edit($id)
    {
        $info = Db::name('shop_discounts')->where('id','=',$id)->field('id,face_value,threshold,platform_assume,shop_id')->find();

        $this->success('获取成功',['info'=>$info]);
    }


    /**
     * update 
     * 
     */
    public function update(Request $request,$id)
    {
        $data = $request->post();

        // 验证表单数据
        $check = $this->validate($data, 'ShopDiscounts');
        if ($check !== true) {
            $this->error($check,201);
        }

        $ret = Db::name('shop_discounts')->where('id','=',$id)->update($data);
        if (!$ret){
            $this->error('修改失败');
        }
        $this->success('修改成功');

    }


    /**
     * delete 
     * 
     */
    public function delete($id)
    {
        $res = model('ShopDiscounts')->where('id','=',$id)->setField('delete',1);

        if (!$res) {
            $this->error('删除失败');
        }
        $this->success('删除成功');
    }


     

}
 