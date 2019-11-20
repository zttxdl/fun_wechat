<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/3
 * Time: 2:39 AM
 */

namespace app\merchants\controller;


use app\common\controller\MerchantsBase;
use think\Db;
use think\Request;

class Promotion extends MerchantsBase
{

    protected $noNeedLogin = [];

    /**
     * 活动管理
     */
    public function index()
    {
        $shop_id = $this->shop_id;


        $list = model('ShopDiscounts')
            ->field('id,face_value,threshold,platform_assume')
            ->where('shop_id',$shop_id)
            ->where('delete',0)
            ->select();

        foreach ($list as $k => &$v) {
            if ($v['platform_assume']) {
                $v['face_value'] = $v['face_value'] - $v['platform_assume'];
            }
        }
        
        $this->success('success',$list);
    }

    /**
     * 设置活动
     */

    public function set(Request $request)
    {
        $face_value = $request->param('face_value');
        $threshold = $request->param('threshold');

        if (!$face_value || !$threshold) {
            $this->error('非法参数');
        }
        if ($face_value > $threshold) {
            $this->error('劵值不能大于门槛');
        }
        $count = model('ShopDiscounts')->where('shop_id',$this->shop_id)->where('delete',0)->count();

        if ($count >=3){
            $this->error('最多设置3个活动');
        }
        $data = [
            'face_value'=>$face_value,
            'threshold'=>$threshold,
            'shop_id'=>isset($this->shop_id) ? $this->shop_id : '1',
            'create_time'=>time(),
        ];

        $id = model('ShopDiscounts')->insertGetId($data);

        $this->success('success',['id'=>$id]);
    }

    /**
     * 删除活动
     */
    public function del(Request $request)
    {
        $id = $request->param('id');
        if(!$id) {
            $this->error('参数不能空');
        }

        $open_status = Db::name('shop_info')->where('id','=',$this->shop_id)->value('open_status');
        if ($open_status) {
            $this->error('营业状态下不可删除活动');
        }
        $id = model('ShopDiscounts')
            ->where('id',$id)
            ->update(['delete'=>1]);
        if($id) {
            $this->success('success');
        }
        $this->error('fail');
    }

}