<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/5/30
 * Time: 10:37 AM
 */

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use think\Request;

class Shop extends MerchantsBase
{
    protected $noNeedLogin = [];
    /**
     * 店铺管理
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {

        $shop_id = $this->shop_id;
        $shop_info = [];

        $result = Model('Shop')->getShopInfo($shop_id);

       //dump($result);
        if($result->isEmpty()) {
            $this->error('暂无店铺信息');
        }


        foreach ($result as $row)
        {
            if($row['status'] == '1') {
                $this->error('店铺待审核中!');
            }
            $shop_info = [
                'shop_name' => $row['shop_name'],//店铺名称
                'status' => '1',//店铺营业状态
                'day_order' => '55',//今日订单数
                'day_sales' => '500',//今日销售额
                'day_uv' => '20',//今日访客数
                'order_cancel_num' => '2',//订单取消数量
            ];

        }


        $this->success('获取成功',$shop_info);
    }

    /**
     * 修改店铺名称
     * @param Request $request
     */
    public function setName(Request $request)
    {
        $shop_id = $this->shop_id;
        $shop_name = $request->param('shop_name');


        if(empty($shop_id) || empty($shop_name)) {
            json_error('非法传参');
        }

        $res = Model('shopInfo')->where('id',$shop_id)->setField('shop_name',$shop_name);

        if($res) {
            $this->success('更新成功');
        }

        $this->error('更新失败');
    }

    /**
     * 店铺图标修改
     */
    public function setLogo(Request $request)
    {
        $shop_id = $this->shop_id;
        $logo_img = $request->param('logo_img');


        if(empty($shop_id) || empty($shop_name)) {
            json_error('非法传参');
        }

        $res = Model('shopInfo')->where('id',$shop_id)->setField('logo_img',$logo_img);

        if($res) {
            $this->success('更新成功');
        }

        $this->error('更新失败');
    }

    /**
     * 店铺营业状态
     * @param Request $request
     * @return \think\response\Json
     */
    public function setOpenStatus(Request $request)
    {
        $shop_id = $this->shop_id;
        $shop_name = $request->param('shop_name');
        $open_status = $request->param('open_status');


        if(empty($shop_id) || empty($open_status)) {
            json_error('非法传参','404');
        }

        $res = Model('shopInfo')->where('id',$shop_id)->setField('open_status',$open_status);

        if($res) {
            $this->success('更新成功');
        }

        $this->error('更新失败');

    }

    /**
     * 商家信息
     */
    public function info(Request $request)
    {
        $shop_id = $this->shop_id;

        if(!$shop_id) {
            $this->error('非法传参');
        }

        $shop_info = Model('Shop')->getShopInfo($shop_id);


        if(empty($shop_info) && !isset($shop_info)) {
            $this->error('店铺不存在');
        }

        $result = [];

        foreach ($shop_info as $row)
        {
            $result = [
                'shop_name' => $row['shop_name'],
                'link_tel' => $row['link_tel'],
                'open_time' => $row['open_time'],
                'run_type' => config('run_type')[$row['run_type']],
                'ping_fee' => $row['ping_fee'],
                'up_to_send_money' => $row['up_to_send_money'],
                'notice' => $row['notice'],
                'info' => $row['info'],
            ];
        }

        $this->success('获取成功',$result);

    }

    /**
     * 商家信息设置
     */
    public function setInfo()
    {

    }

    /**
     *营业时间修改
     */
    public function setOpenTime()
    {

    }

    /**
     * 商家更多信息
     */
    public function moreInfo(Request $request)
    {
        $shop_id = $this->shop_id;

        if(!$shop_id) {
            $this->error('非法传参','404');
        }

        $result = [];

        $shop_info = $this->shopModel->getShopInfo($shop_id);

        foreach ($shop_info as $row)
        {
            //店铺信息
            $result['shop_info']['shop_name'] = $row['shop_name'];
            $result['shop_info']['logo_img'] = $row['logo_img'];
            $result['shop_info']['link_name'] = $row['link_name'];
            $result['shop_info']['link_tel'] = $row['link_tel'];
            $result['shop_info']['address'] = $row['address'];
            $result['shop_info']['school'] = Model('School')->getNameById($row['school_id']);
            $result['shop_info']['manage_category_name'] = Model('ManageCategory')->getNameById($row['manage_category_id']);
        }

        $shop_more_info = $this->shopModel->getShopMoreInfo($shop_id);
        //dump($shop_more_info);
        //商家资质
        $shop_qualification = [];

        //收款信息
        $shop_account = [];

        foreach ($shop_more_info as $row)
        {
            $shop_qualification['business_license'] = $row['business_license'];
            $shop_qualification['proprietor'] = $row['proprietor'];
            $shop_qualification['hand_card_front'] = $row['hand_card_front'];
            $shop_qualification['user_name'] = $row['user_name'];
            $shop_qualification['identity_num'] = $row['identity_num'];
            $shop_qualification['sex'] = $row['sex'];
            $shop_qualification['licence'] = $row['licence'];

            $shop_account['branch_back'] = $row['branch_back'];
            $shop_account['back_hand_name'] = $row['back_hand_name'];
            $shop_account['back_card_num'] = $row['back_card_num'];
        }

        $result['shop_qualification'] = $shop_qualification;
        $result['shop_account'] = $shop_account;

        $this->success('获取成功',$result);
    }

    /**
     * 商家审核反馈
     */

    public function checkStatus()
    {
        $shop_id = $this->shop_id;


    }







}