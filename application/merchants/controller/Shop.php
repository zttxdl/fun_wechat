<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/5/30
 * Time: 10:37 AM
 */

namespace app\merchants\controller;


use think\Request;

class Shop
{
    /**
     * 店铺管理
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {

        $shop_id = $request->param('id');
        $shop_info = [];

        $data = Model('Shop')->getShopInfo($shop_id);

        foreach ($data as $row)
        {
            $shop_info = [
                'shop_name' => '123456',
                'shop_name' => '23456',
                'shop_name' => 'wewe',
            ];
        }


        return $shop_info;
    }

    /**
     * 修改店铺名称
     * @param Request $request
     */
    public function setShopName(Request $request)
    {
        $shop_id = $request->param('shop_id');
        $shop_name = $request->param('shop_name');

        if(empty($shop_id) || empty($shop_name)) {
            json_error('非法传参','404');
        }

        $res = Model('shopInfo')->where('id',$shop_id)->setField('shop_name',$shop_name);

        if($res) {
            return json_success('更新成功');
        }

        return json_error('更新失败');
    }


}