<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/3
 * Time: 2:39 AM
 */

namespace app\merchants\controller;


use app\common\controller\MerchantsBase;
use think\Request;

class Promotion extends MerchantsBase
{

    protected $noNeedLogin = ['del','set','index'];

    /**
     * 活动管理
     */
    public function index()
    {
        $shop_id = $this->shop_id;


        $id = model('ShopDiscounts')
            ->field('id,face_value,threshold')
            ->where('shop_id',$shop_id)
            ->select();

        $this->success('success',$id);
    }

    /**
     * 设置活动
     */

    public function set(Request $request)
    {
        $face_value = $request->param('face_value');
        $threshold = $request->param('threshold');

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
        $id = model('ShopDiscounts')
            ->where('id',$id)
            ->update(['delete'=>1]);
        if($id) {
            $this->success('success');
        }
        $this->error('fail');
    }

}