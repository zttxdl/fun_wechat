<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

class Advers extends Controller
{
    /**
     * 广告管理控制器
     *
     */
    public function index(Request $request)
    {
        // 搜索条件
        $where = [];
        !empty($request->get('name/s')) ? $where[] = ['name','like',$request->get('name/s').'%'] : null;
        !empty($request->get('platfrom/d')) ? $where[] = ['platfrom','=',$request->get('platfrom/d')] : null;
        !empty($request->get('status/d')) ? $where[] = ['status','=',$request->get('status/d')] : null;

        // 广告列表
        $list = Db::name('advers')->where($where)->paginate(10)->each(function ($item, $key) {
            // 是否启用
            $item['status'] = config('advers_status')[$item['status']];
            // 展示平台
            $item['platfrom'] = config('show_platfrom')[$item['platfrom']];

            return $item;
        });
        
        return  json_success('ok',['list'=>$list]);

    }


    /**
     * 展示编辑广告页面 
     * 
     */
    public function edit(Request $request,$id)
    {
        if (empty((int)$id) ) {
            return json_error('非法参数',201);
        }

        $info = Db::name('advers')->find($id);  
        // 是否启用
        $info['status'] = config('advers_status')[$info['status']];
        // 展示平台
        $info['platfrom'] = config('show_platfrom')[$info['platfrom']];

        return  json_success('ok',['info'=>$info]);
        
    }


    /**
     * 保存修改广告
     * 
     */
    public function update(Request $request)
    {
        $data = $request->param();

        if (empty((int)$data['id'])) {
            return json_error('非法参数',201);
        }

        // 验证表单数据
        $check = $this->validate($data, 'Advers');
        if ($check !== true) {
            return json_error($check,201);
        }

        // 提交表单
        $result = Db::name('advers')->update($data);
        if ($result === false) {
            return json_error('修改失败',201);
        }

        return  json_success('ok');
        
    }


    /**
     * 删除广告 
     * 
     */
    public function delete($id)
    {
        $result = Db::name('advers')->delete($id);
        if (!$result) {
            return json_error('删除失败',201);
        }
        return json_success('删除成功');
    }
     
     
     


}
