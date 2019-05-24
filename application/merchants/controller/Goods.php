<?php

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use think\Request;

/**
 * 商品模块控制器
 */
class Goods extends MerchantsBase
{

    protected $noNeedLogin = [];
    /**
     * 新建商品
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function addGoods(Request $request)
    {
        $data = $request->param();

        $check = $this->validate($data, 'Goods');
        if ($check !== true) {
            return json_error($check);
        }


    }

    /**
     * 新建分类
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function addCategory(Request $request)
    {
        $data = $request->param();

        $check = $this->validate($data, 'Goods');
        if ($check !== true) {
            return json_error($check);
        }


    }
}