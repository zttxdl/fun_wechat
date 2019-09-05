<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\common\controller\Base;
use think\Db;

class Canteen extends Base
{
    /**
     * 添加食堂【新增学校时，添加食堂用，用于判断食堂账户名称是否存在】
     * 
     */
    public function verification(Request $request)
    {
        $data = $request->param();

        // 验证表单数据
        $check = $this->validate($data, 'Canteen');
        if ($check !== true) {
            $this->error($check,201);
        }
        // 验证账户是否重名
        $count = Db::name('canteen')->where('account','=',$data['account'])->count();
        if ($count) {
            $this->error('食堂账户名已存在！');
        }
        $this->success('食堂账户名账户名可用');

    }


    /**
     * 添加食堂【编辑学校时，有用到】
     * 
     */
    public function insert(Request $request)
    {
        $data = $request->param();
        $data['cleartext'] = $data['password'];
        $data['password'] = md5($data['password']);

        // 验证表单数据
        $check = $this->validate($data, 'Canteen');
        if ($check !== true) {
            $this->error($check,201);
        }
        // 验证账户是否重名
        $count = Db::name('canteen')->where('account','=',$data['account'])->count();
        if ($count) {
            $this->error('食堂账户名已存在！');
        }

        // 添加数据库
        $red_id = Db::name('canteen')->insertGetId($data);
        if (!$red_id) {
            $this->error('添加失败');
        }
        $data['id'] = $red_id;
        unset($data['school_id']);
        unset($data['password']);
        $this->success('添加成功',['canteen_info'=>$data]);

    }


    /**
     * 删除食堂【编辑学校时，有用到】
     *
     * @param  int  $id
     */
    public function delete($id)
    {
        $count = Db::name('shop_info')->where('canteen_id','=',$id)->count();
        if ($count) {
            $this->error('该食堂已有商家入驻，不可删除');
        }

        $result = Db::name('canteen')->delete($id);
        if (!$result) {
            $this->error('删除失败');
        }
        $this->success('删除成功');
    }

}
