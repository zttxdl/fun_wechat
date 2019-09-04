<?php

namespace app\canteen\controller;

use app\common\controller\Base;
use think\Request;

class ShopInfo extends CanteenBase
{
    protected $isLogin;
    
    /**
     * 获取商家列表
     */
    public function getList(Request $request)
    {
        $page_no = $request->param('page');
        $page_size = $request->param('pageSize');
        $key_word = $request->param('keyword');
        // $school_id = $request->param('school_id/d');
        $id = session('canteen.id');

        if($id) {
            $map[] = ['canteen_id','=',$id];
        }

        $map[] = ['status','in','3,4'];
        // 搜索条件
        if($key_word) {
            $map[] = ['a.shop_name|a.link_name|a.link_tel','like',$key_word.'%'];
        }

        // 学校列表
        // $school_list = Model('school')->getSchoolList();

        // if ($school_id) {
        //     $map[] = ['school_id','=',$school_id];
        // }

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
                        'open_status' => $row['open_status'],
                        // 'month_sales' => model('Shop')->getMonthSales($row['id']),
                        // 'count_sales' => model('Shop')->getCountSales($row['id']),
                    ];
                }
            }
        }


        $result['count'] = $list['total'];
        $result['page'] = $list['current_page'];
        $result['pageSize'] = $list['per_page'];
        // $result['school_list'] = $school_list;
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

        if(empty((int)$shop_id)) {
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
        $canteen_id = session('canteen.id');
        $shop_id = model('Shop')->getShopIdByCanteenId($canteen_id);

        dump($shop_id);exit;

        $open_status = $request->param('open_status');

        $res = Model('shopInfo')->where('id',$shop_id)->setField('open_status',$open_status);

        $result = ShopInfo::where('id',$shop_id)->find();

        if($res) {
            $this->success('更新成功',['open_status'=>$result['open_status']]);
        }

        $this->error('更新失败',201,['open_status'=>$result['open_status']]);

    }

    /**
     * 商家流水
     */
    public function getShopFlow()
    {
        $canteen_id = $this->canteen_id;
    }

}
