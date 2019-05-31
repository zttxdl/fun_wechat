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

        $result = Model('Shop')->getShopInfo($shop_id);

//        dump($result);
        if($result->isEmpty()) {
            return json_error('暂无店铺信息');
        }


        foreach ($result as $row)
        {
            if($row['status']) {

            }
            $shop_info = [
                'shop_name' => '123456',//店铺名称
                'status' => '1',//店铺营业状态
                'day_order' => '55',//今日订单数
                'day_sales' => 'wewe',//今日销售额
                'day_uv' => '20',//今日访客数
                'order_cancel_num' => '2',//订单取消数量
            ];
        }


        return json_success('获取成功',$shop_info);
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

    /**
     * 店铺图标修改
     */
    public function setShopLogo()
    {

    }

    /**
     * 添加商家资质
     */
    public function addQualification()
    {

    }

    /**
     * 添加收款信息
     */
    public function addAccount()
    {

    }


}