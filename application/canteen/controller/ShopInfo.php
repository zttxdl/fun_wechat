<?php

namespace app\canteen\controller;

use app\common\controller\Base;
use think\Request;

class ShopInfo extends Base
{  
    protected $canteen_id;
    public function __construct()
    {
        parent::__construct();
        $this->shopModel = Model('Shop');
        $this->SchoolModel = Model('School');
        $this->canteen_id = session('canteen.id');
    }
    /**
     * 获取商家列表
     */
    public function getList(Request $request)
    {
        $page_no = $request->param('page');
        $page_size = $request->param('pageSize');
        $key_word = $request->param('keyword');
        $id = $this->canteen_id;

        if($id) {
            $map[] = ['a.canteen_id','=',$id];
        }

        $map[] = ['a.status','in','3,4'];
        // 搜索条件
        if($key_word) {
            $map[] = ['a.shop_name|a.link_name|a.link_tel','like',$key_word.'%'];
        }

        // 获取当前学校的已审核通过的商铺列表
        $list = model('ShopInfo')
            ->alias('a')
            ->page($page_no,$page_size)
            ->where($map)
            ->paginate($page_size)
            ->toArray();
        $result = [];
        if($list) {
            foreach ($list['data'] as $row)
            {
                if($row['id']) {
                    $result['data'][] = [
                        'id' => $row['id'],
                        'shop_name' => $row['shop_name'],
                        'logo_img' => $row['logo_img'],
                        'link_name' => $row['link_name'],
                        'link_tel' => $row['link_tel'],
                        'add_time' => date('Y-m-d',$row['add_time']),
                        'school_name' =>  Model('School')->getNameById($row['school_id']),
                        'shop_stock' =>  Model('Shop')->getShopStock($row['id']),
                        'status' => config('shop_check_status')[$row['status']],
                        'canteen_open_status' => $row['canteen_open_status']
                    ];
                }
            }
        }


        $result['count'] = $list['total'];
        $result['page'] = $list['current_page'];
        $result['pageSize'] = $list['per_page'];
        $this->success('获取成功',$result);

        // 获取当前学校的已审核通过的商铺列表
        $result = model('ShopInfo')
            ->alias('a')
            ->join('School b','a.school_id = b.id')
            ->field(['a.id','a.shop_name','a.logo_img','a.link_name','a.link_tel','a.add_time','b.name'=>'school_name','count(a.id)'=>'shop_stock','a.open_status'])
            ->where($map)
            ->paginate($page_size)
            ->toArray();


        if($result['data']) {
            foreach ($result['data'] as $key=>$row)
            {
                if($row['id']) {
                    $result['data'][$key]['add_time'] = date('Y-m-d',$row['add_time']);
                }
            }
        }
        $this->success('获取成功',$result);
    }

    /**
     * 获取商家详情
     * @param Request $request
     * @return array|\think\response\Json
     */
    public function getDetail(Request $request)
    {
        $shop_id = $request->param('id');

        if(empty($shop_id)) {
            $this->error('非法请求','404');
        }
        $shop_info = $this->shopModel->getShopInfo($shop_id);

        if(!$shop_info) {
            $this->error('店铺不存在');
        }
        $result = [];
        foreach ($shop_info as $row)
        {
            //店铺信息
            $result['shop_info']['shop_name'] = $row['shop_name'];
            $result['shop_info']['logo_img'] = $row['logo_img'];
            $result['shop_info']['link_name'] = $row['link_name'];
            $result['shop_info']['link_tel'] = $row['link_tel'];
            $result['shop_info']['status'] = config('shop_check_status')[$row['status']];
            $result['shop_info']['manage_category_name'] = Model('ManageCategory')->getNameById($row['manage_category_id']);
            $result['shop_info']['address'] = $row['address'];
            $result['shop_info']['school'] = Model('School')->getNameById($row['school_id']);
            $result['shop_info']['canteen_name'] = Model('Canteen')->getCanteenName($row['canteen_id']);
        }

        $shop_more_info = $this->shopModel->getShopMoreInfo($shop_id);


        $shop_qualification = [];
        $shop_account = [];

        if($shop_more_info) {
            foreach ($shop_more_info as $row)
            {
                //商家资质
                $shop_qualification = [
                    'business_license' => $row['business_license'],
                    'proprietor' => $row['proprietor'],
                    'hand_card_front' => $row['hand_card_front'],
                    'hand_card_back' => $row['hand_card_back'],
                    'user_name' => $row['user_name'],
                    'identity_num' => $row['identity_num'],
                    'sex' => config('sex')[$row['sex']],
                    'licence' => $row['licence'],
                ];
                //收款信息
                $shop_account = [
                    'branch_back' => $row['branch_back'],
                    'back_hand_name' => $row['back_hand_name'],
                    'back_card_num' => $row['back_card_num'],
                ];
            }
        }



        $result['shop_qualification'] = $shop_qualification;
        $result['shop_account'] = $shop_account;

        //补充信息
        $result['shop_information'] = $this->shopModel->getInformation($shop_id);
        //在售商品
        $result['is_oline_goods'] = $this->shopModel->getIsOnlineGoods($shop_id);

        foreach ($result['is_oline_goods'] as &$row)
        {
            if($row['attr_ids']) {
                $res = $this->shopModel->getGoodsAttrName($row['attr_ids']);

                //dump($res);exit;

                $row['attr_names'] = isset($res) ? $res : '--';

                $row['class_name'] = $row['class_name'];
            }
        }

        //结算信息
        $result['shop_settle'] = $this->shopModel->getSettle($shop_id);
//        dump($result);
        $this->success('获取成功',$result);
    }

    /**
     * 更新商家营业状态
     * @param Request $request
     * @return \think\response\Json
     */
    public function setOpenStatus(Request $request)
    {
        $shop_id = $request->param('shop_id');
        $open_status = $request->param('open_status');
        if(!$shop_id) {
            $this->error('商家ID不能为空');
        }
        $res = Model('shopInfo')->where('id',$shop_id)->setField('open_status',$open_status);
        //店铺营业状态更新
        Model('shopInfo')->where('id',$shop_id)->setField('canteen_open_status',$open_status);

        $result = Model('shopInfo')->where('id',$shop_id)->find();

        if($res) {
            $this->success('更新成功',['open_status'=>$result['open_status']]);
        }

        $this->error('更新失败',201,['open_status'=>$result['open_status']]);

    }

    /**
     * 商家流水
     */
    public function getShopFlow(Request $request)
    {
        $shop_id = $request->param('shopId');
        $page = $request->param('page');
        $page_size = $request->param('pageSize');
        $key_word = $request->param('keyword');
        $trade_type = $request->param('tradeType');//0:全部 1:支付 2:退款
        $canteen_id = $this->canteen_id;


        // 搜索条件
        if($key_word)  $map[] = ['a.orders_sn','like',$key_word.'%'];
        if($trade_type == 1){
            $map[] = ['a.status','=',8];
        }elseif($trade_type == 2) {
            $map[] = ['a.status','=',11];
        }else{
            $map[] = ['a.status','in',[8,11]];
        }

        if($shop_id) {
            $map[] = ['a.shop_id','=',$shop_id];
        }else{
            $shop_list = model('Shop')->getShopListByCanteenID($canteen_id)->toArray();
            $shop_ids = implode(',',array_column($shop_list,'id'));
            $map[] = ['a.shop_id','in',$shop_ids];
        }
        
        $result = model('Orders')
                    ->alias('a')
                    ->join('shopInfo b','a.shop_id = b.id')
                    ->field('a.id,a.orders_sn,a.status,a.money,b.shop_name,a.pay_mode,a.pay_time')
                    ->where($map)
                    ->order('id','desc')
                    ->paginate($page_size)
                    ->toArray();
        // if(empty($result['data']['data']) && !isset($result['data']['data'])) {
        //     $this->error('暂无数据');
        // }
    
        foreach ($result['data'] as $key => $value) {
           $result['data'][$key]['pay_time'] = date('Y-m-d H:i:s',$value['pay_time']);
           $result['data'][$key]['pay_mode'] = $value['status'] == 8 ? '支付' : '退款';
           $result['data'][$key]['tradeWay'] = $value['pay_mode'] == 1 ? '微信支付' : '支付宝支付';

        }

         $this->success('获取成功',$result);
    }

    /**
     * 商家名称列表
     */
    public function getShopNameList()
    {
        $canteen_id = $this->canteen_id;
        //获取食堂对应商家列表
        $shop_list = model('Shop')->getShopListByCanteenID($canteen_id);
        $this->success('获取成功',$shop_list);     
    }


}
