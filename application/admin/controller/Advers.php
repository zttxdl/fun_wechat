<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;


/**
 * 广告控制器
 * @author Mike
 * date 2019/5/24
 */
class Advers extends Controller
{
    /**
     * 广告列表
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
        $list = Db::name('advers')->where($where)->order('id desc')->paginate(10)->each(function ($item, $key) {
            // 是否启用
            $item['mb_status'] = config('advers_status')[$item['status']];
            // 展示平台
            $item['platfrom'] = config('show_platfrom')[$item['platfrom']];

            return $item;
        });
        
        return  json_success('ok',['list'=>$list]);

    }


    /**
     * 展示编辑广告页面 
     * @param $id 广告表主键值
     */
    public function edit($id)
    {
        if (empty((int)$id)) {
            $this->error('非法参数',201);
        }

        $info = Db::name('advers')->find($id);  
        // 是否启用
        $info['mb_status'] = config('advers_status')[$info['status']];
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

        if (!isset($data['id']) || empty((int)$data['id'])) {
            $this->error('非法参数',201);
        }

        // 验证表单数据
        $check = $this->validate($data, 'Advers');
        if ($check !== true) {
            $this->error($check,201);
        }

        // 提交表单
        $result = Db::name('advers')->update($data);
        if (!$result) {
            $this->error('修改失败',201);
        }
        
        return  json_success('ok');
        
    }


    /**
     * 删除广告 
     * @param $id 广告表主键值
     */
    public function delete($id)
    {
        $result = Db::name('advers')->delete($id);
        if (!$result) {
            $this->error('删除失败',201);
        }
        $this->succes('删除成功');
    }
     
     
     


}
