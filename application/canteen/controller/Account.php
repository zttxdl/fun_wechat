<?php

namespace app\canteen\controller;

use app\common\controller\Base;
use app\common\model\CanteenAccount;
use think\Request;

class Account extends Base
{
    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data =  $request->post();
        // 验证表单数据
        $check = $this->validate($data, 'Account');
        if ($check !== true) {
            $this->error($check,201);
        }
        $find = CanteenAccount::get(['canteen_id'=>$data['canteen_id']]);
        if ($find) {
            $this->error('添加失败',401);
        }
        $res = CanteenAccount::create($data);

        $this->success('success',$res);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $data = CanteenAccount::get(['canteen_id'=>$id]);
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
        $data =  $request->post();
        $res = CanteenAccount::update($data, ['id' => $id]);
        $this->success('success',$res);
    }

}
