<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/5/30
 * Time: 10:37 AM
 */

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use app\common\model\Orders;
use app\common\model\ShopInfo;
use app\common\model\ShopMoreInfo;
use think\Request;

class Shop extends MerchantsBase
{
    protected $noNeedLogin = [];
    /**
     * 店铺管理
     * @param Request $request
     * @return array
     */
    public function index()
    {

        $shop_id = $this->shop_id;
        $result = ShopInfo::where('id',$shop_id)->find();

        if($result->isEmpty()) {
            $this->error('暂无店铺信息');
        }
        if($result['status'] == '1') {
            $this->error('店铺待审核中!');
        }



        $day_order = Orders::where('shop_id',$shop_id)->count('id');
        $day_sales = Orders::where(['shop_id'=>$shop_id,'status'=>8])->sum('money');
        $day_cancel_order = Orders::where(['shop_id'=>$shop_id,'status'=>9])->count('id');

        $shop_info = [
            'shop_name' => $result['shop_name'],//店铺名称
            'status' => $result['open_status'],//店铺营业状态
            'day_order' => $day_order,//今日订单数
            'day_sales' => sprintf("%.2f",$day_sales),//今日销售额
            'day_uv' => '20',//今日访客数
            'logo_img'=>$result['logo_img'],
            'order_cancel_num' => $day_cancel_order,//订单取消数量
        ];



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

        if(empty($logo_img) && !isset($logo_img)){
            $this->error('图标不能为空');
        }

        if(empty($shop_id) && !isset($shop_id)){
            $this->error('店铺ID不能为空');
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

        $open_status = $request->param('open_status');

        $res = Model('shopInfo')->where('id',$shop_id)->setField('open_status',$open_status);

        if($res) {
            $this->success('更新成功');
        }

        $this->error('更新失败');

    }

    /**
     * 商家信息
     */
    public function info()
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
                'run_time' => $row['run_time'],
                'run_type' => $row['run_type'],
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
    public function setInfo(Request $request)
    {
        $data = $request->post();

        set_log('req==',$data,'setInfo');

        // 验证表单数据
        $check = $this->validate($data, 'ShopInfo');
        if ($check !== true) {
            $this->error($check,201);
        }

        $data['run_type'] = '平台配送';

        // 更新数据
        $result = ShopInfo::where('id','=',$this->shop_id)->update($data);;

        if (!$result) {
            $this->error('更新失败',201);
        }
        $this->success('更新成功');
    }
    
    /**
     * 商家入驻信息
     */
    public function moreInfo()
    {
        $shop_id = $this->shop_id;

        $result = [];

        $shop_info = ShopInfo::where('id',$shop_id)->find();
        $shop_more_info = ShopMoreInfo::where('shop_id',$shop_id)->find();
        //dump($shop_more_info);
        //商家资质
        $shop_qualification = [];

        //收款信息
        $shop_account = [];


        if($shop_info) {

            //店铺信息
            $result['shop_info']['shop_name'] = $shop_info['shop_name'];
            $result['shop_info']['address'] = $shop_info['address'];
            $result['shop_info']['longitude'] = $shop_info['longitude'];
            $result['shop_info']['latitude'] = $shop_info['latitude'];
            $result['shop_info']['school'] = Model('School')->getNameById($shop_info['school_id']);
            $result['shop_info']['manage_category_name'] = Model('ManageCategory')->getNameById($shop_info['manage_category_id']);
            $result['shop_info']['logo_img'] = $shop_info['logo_img'];
            $result['shop_info']['link_name'] = $shop_info['link_name'];
            $result['shop_info']['link_tel'] = $shop_info['link_tel'];

        }

        if($shop_more_info) {
            $shop_qualification['business_license'] = $shop_more_info['business_license'];
            $shop_qualification['proprietor'] = $shop_more_info['proprietor'];
            $shop_qualification['hand_card_front'] = $shop_more_info['hand_card_front'];
            $shop_qualification['hand_card_back'] = $shop_more_info['hand_card_back'];
            $shop_qualification['user_name'] = $shop_more_info['user_name'];
            $shop_qualification['identity_num'] = $shop_more_info['identity_num'];
            $shop_qualification['sex'] = $shop_more_info['sex'] == '1' ? '男' : '女';
            $shop_qualification['licence'] = $shop_more_info['licence'];

            $shop_account['branch_back'] = $shop_more_info['branch_back'];
            $shop_account['back_hand_name'] = $shop_more_info['back_hand_name'];
            $shop_account['back_card_num'] = $shop_more_info['back_card_num'];
            $shop_account['account_type'] = $shop_more_info['account_type'] == '1' ? '对公' : '对私';
        }


        $result['shop_qualification'] = $shop_qualification;
        $result['shop_account'] = $shop_account;

        $this->success('获取成功',$result);
    }


    /**
     * 修改密码
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function updatePwd(Request $request)
    {
        $phone = $request->param('phone');
        $new_password = $request->param('new_password');
        $true_password = $request->param('sure_password');
        $code = $request->param('code');

        //参数过滤
        $check = $this->validate($request->param(), 'Shop');
        if ($check !== true) {
            $this->error($check);
        }

        //手机号验证
        $key = 'alisms_' . 'auth' . '_' . $phone;

        $result        = model('ShopInfo')
            ->field('account')
            ->where('account',$phone)
            ->find();

        if(!$result){
            $this->error('账户不存在!');
        }

        $result = model('Alisms', 'service')->checkCode($phone, 'auth', $code);
        if (!$result) {
            $this->error(model('Alisms', 'service')->getError());
        }

        $data = model('ShopInfo')->where('id',$this->shop_id)->find();


        if ($new_password != $true_password){
            $this->error('两次密码不一致');
        }

        model('ShopInfo')->where('id',$this->shop_id)->update(['password'=>md5($new_password)]);

        $this->success('success');
    }




}