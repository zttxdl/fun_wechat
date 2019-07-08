<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use think\Db;
use think\facade\Cache;

/**
 * 图文协议控制器
 * @author Mike
 * date 2019/5/27
 */
class Agreement extends Base
{
    /**
     * 图文协议列表
     * 
     */
    public function index(Request $request)
    {
        // 搜索条件
        $where = [];
        !empty($request->get('title/s')) ? $where[] = ['title','like',$request->get('title/s').'%'] : null;
        !empty($request->get('platfrom/d')) ? $where[] = ['platfrom','=',$request->get('platfrom/d')] : null;
        
        $list = Db::name('agreement')->where($where)->field('id,title,platfrom,save_time')->order('id desc')->select();

        foreach ($list as $k => &$vo) {
            $vo['platfrom'] = config('show_platfrom')[$vo['platfrom']];
            $vo['save_time'] = date('Y-m-d H:i',$vo['save_time']);
        }

        $this->success('ok',['list'=>$list]);

    }


    /**
     * 展示图文协议编辑页面 
     * @param $id 图文协议表主键值
     */
    public function edit($id)
    {
        if (empty((int)$id) ) {
            $this->error('非法参数',201);
        }

        $info = Db::name('agreement')->where('id',$id)->field('id,title,content')->find();
        // 设置缓存
        Cache::store('redis')->set('agreement_'.$id,$info,3600*24*7*30);
        $this->success('ok',['info'=>$info]);
    }


    /**
     * 保存图文协议编辑
     * 
     */
    public function update(Request $request)
    {
        $data = $request->param();
        $data['save_time'] = time();

        if (!isset($data['id']) || empty((int)$data['id'])) {
            $this->error('非法参数',201);
        }

        // 验证表单数据
        $check = $this->validate($data, 'Agreement');
        if ($check !== true) {
            $this->error($check,201);
        }

        // 提交表单
        $result = Db::name('agreement')->update($data);
        if (!$result) {
            $this->error('修改失败',201);
        }
        
        $this->success('修改成功');
    }


    /**
     * 图文协议详情
     * @param $id 图文协议表主键值
     */
    public function show($id)
    {
        $info = Db::name('agreement')->where('id',$id)->field('title,content')->find();

        $this->success('ok',['info'=>$info]);
    }
     

     
     

    
}
