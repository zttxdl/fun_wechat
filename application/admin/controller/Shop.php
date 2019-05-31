<?php


namespace app\admin\controller;

//use think\Model;
use think\Model;
use think\Request;

class Shop
{

    public function __construct()
    {
        $this->shopModel = Model('Shop');
        $this->SchoolModel = Model('School');
    }

    /**
     * 获取商家列表
     */
    public function getList(Request $request)
    {
        $page_no = $request->param('page_no');
        $page_size = 5;

        $shop_list = model('Shop')->getShopList($page_no,$page_size);
        //$shop_list = $list;

        foreach ($shop_list as &$row)
        {
            if($row['id']) {
                unset($row['password']);
                $row['school_name'] = Model('School')->getNameById($row['id']);
                $row['shop_stock'] = Model('Shop')->getShopStock($row['id']);
            }
        }

        return json_success('获取成功',$shop_list);
    }


    /**
     * 获取商家详情
     */
    public function getDetail(Request $request)
    {
        $shop_id = $request->param('id');

        if(empty((int)$shop_id)) {
            return json_error('非法请求','404');
        }

        $result = [];

        //店铺信息
        $result['shop_info'] = $this->shopModel->getShopDetail($shop_id);

        //商家资质
        $result['shop_qualification'] = $this->shopModel->getShopQualification($shop_id);

        //收款信息
        $result['shop_account'] = $this->shopModel->getAccount($shop_id);

        //补充信息
        $result['shop_information'] = $this->shopModel->getInformation($shop_id);

        //在售商品
        $result['is_oline_goods'] = $this->shopModel->getIsOnlineGoods($shop_id);

        foreach ($result['is_oline_goods'] as &$row)
        {
            if($row['attrs_ids']) {
                $row['attrs_name'] = $this->shopModel->getGoodsAttrName($row['attrs_ids']);

                $row['attrs_name'] = isset($row['attrs_name']) ? $row['attrs_name'] : '--';
            }
        }

        //结算信息
        $result['shop_settle'] = $this->shopModel->getSettle();
        return json_success('获取成功',$result);

    }

    /**
     * 获取商家详情
     * @param Request $request
     * @return array|\think\response\Json
     */
    public function getDetail2(Request $request)
    {
        $shop_id = $request->param('id');

        if(empty((int)$shop_id)) {
            return json_error('非法请求','404');
        }
        $shop_info = $this->shopModel->getShopInfo($shop_id);
        //dump($shop_list);
        $result = [];
        foreach ($shop_info as $row)
        {
            //店铺信息
            $result['shop_info']['shop_name'] = $row['shop_name'];
            $result['shop_info']['logo_img'] = $row['logo_img'];
            $result['shop_info']['link_name'] = $row['link_name'];
            $result['shop_info']['link_tel'] = $row['link_tel'];
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

        //补充信息
        $result['shop_information'] = $this->shopModel->getInformation($shop_id);

        //在售商品
        /*$result['is_oline_goods'] = $this->shopModel->getIsOnlineGoods($shop_id);

        foreach ($result['is_oline_goods'] as &$row)
        {
            if($row['attrs_ids']) {
                $row['attrs_name'] = $this->shopModel->getGoodsAttrName($row['attrs_ids']);

                $row['attrs_name'] = isset($row['attrs_name']) ? $row['attrs_name'] : '--';
            }
        }*/

        //结算信息
        $result['shop_settle'] = $this->shopModel->getSettle();
        return json_success('获取成功',$result);
    }


    /**
     * 添加店铺
     */
    public function addShop()
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

    /**
     * 商家审核列表
     */
    public function checkList(Request $request)
    {
        $page_no = $request->param('page_no');
        $page_size = 5;

        if(!$page_no) {
            return json_error('非法传参','404');
        }

        $data  =  $this->shopModel->getShopList($page_no,$page_size);

        $shop_check_list = [];
        foreach ($data as $row)
        {
            $shop_check_list[] = [
                'logo_img' => $row['logo_img'],
                'shop_name' => $row['shop_name'],
                'link_name' => $row['link_name'],
                'link_tel' => $row['link_tel'],
                'manage_category_name' => Model('ManageCategory')->getNameById($row['manage_category_id']),
                'school_name' => Model('School')->getNameById($row['school_id']),
                'status' => config('shop_check_status')[$row['status']]

            ];
        }

        return json_success('查询成功',$shop_check_list);


    }

    /**
     * 商家审核详情
     */
    public function checkDetail(Request $request)
    {
        $shop_id = $request->param('shop_id');

        if(!$shop_id) {
            return json_error('非法传参','404');
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

        return json_success('获取成功',$result);

    }

    /**
     * 商家审核状态
     */
    public function checkStatus(Request $request)
    {
        $shop_id = $request->param('shop_id');
        $status = $request->param('status');

        if(empty($shop_id) || empty($status)){
            return json_error('非法传参','404');
        }

        $res = Model('ShopInfo')->where('id',$shop_id)->setField('status',$status);

        if($res) {
            return json_success('更新成功');
        }

        return json_error('更新失败');

    }

    /**
     * 商家审核展示
     */
    public function checkShow()
    {
        $data = config('check_status')['shop'];
        return json_success('获取成功',$data);
    }

    /**
     * 商家排序列表
     */
    public function sortInfo(Request $request)
    {
        $page_no = $request->param('page_no');
        $page_size = 5;
        if(empty($page_no)) {
            return json_error('非法传参','404');
        }
        $shop_list = Model('Shop')->getShopList($page_no,$page_size);

        $sort_info = [];
        foreach ($shop_list as $row) {
            $sort_info['shop_id'] = $row['id'];
            $sort_info['shop_name'] = $row['shop_name'];
            $sort_info['logo_img'] = $row['logo_img'];
            $sort_info['school_name'] = Model('School')->getNameById($row['school_id']);
            $sort_info['sort'] = $row['sort'];
        }

        return json_success('获取成功',$sort_info);
    }

    /**
     * 商家排序
     */
    public function sort(Request $request)
    {
        $shop_id = $request->param('shop_id');
        $sort = $request->param('sort');

        if(empty($shop_id) || empty($sort)) {
            return json_error('非法传参','404');
        }

        $map['shop_id'] = $shop_id;
        $map['sort'] = $sort;

        $data = $this->shopModel->sortEdit($map);

        if(empty($data)) {
            return json_error('更新失败');
        }
        return json_success('更新成功');
    }
}