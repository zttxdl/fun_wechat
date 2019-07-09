<?php


namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use app\common\model\Shop as ShopInfoModel;

class Shop extends Base
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
        $page_no = $request->param('page');
        $page_size = $request->param('pageSize');
        $key_word = $request->param('keyword');
        $school_id = $request->param('school_id/d');


        $map[] = ['status','in','3,4'];
        // 搜索条件
        if($key_word) {
            $map[] = ['a.shop_name|a.link_name|a.link_tel','like',$key_word.'%'];
        }

        // 学校列表
        $school_list = Model('school')->getSchoolList();

        if ($school_id) {
            $map[] = ['school_id','=',$school_id];
        }



        // 获取当前学校的已审核通过的商铺列表
        $list = model('ShopInfo')
            ->alias('a')
            ->page($page_no,$page_size)
            ->where($map)
            ->paginate($page_size)
            ->toArray();

        if(!$list['data']) {
            $this->error('暂无数据');
        }

        $result = [];
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
                    'month_sales' => model('Shop')->getMonthSales($row['id']),
                    'count_sales' => model('Shop')->getCountSales($row['id']),
                ];
            }
        }

        $result['count'] = $list['total'];
        $result['page'] = $list['current_page'];
        $result['pageSize'] = $list['per_page'];
        $result['school_list'] = $school_list;
        $this->success('获取成功',$result);
    }

    /**
     * 启用禁用店铺
     */
    public function setStatus(Request $request)
    {
        $shop_id = $request->param('shop_id');
        $status = $request->param('status');//3 启用 4 禁用

        $res = Model('ShopInfo')->where('id',$shop_id)->setField('status',$status);


        if($res) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
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
     * 商家审核列表
     */
    public function checkList(Request $request)
    {
        $page = $request->param('page');
        $page_size = $request->param('pageSize',20);
        $key_word = $request->param('keyword');
        $status = $request->param('status');

        $map = [];
        if($key_word) {
            $map[] = ['a.shop_name|a.link_name|a.link_tel|b.name|c.name','like',$key_word.'%'];
        }

        if($status) {
            $map[] = ['a.status','=',$status];
        }else{
            $map[] = ['a.status','in','1,2,3'];
        }

        $data = model('shopInfo')
                            ->alias('a')
                            ->join('ManageCategory b','a.manage_category_id = b.id')
                            ->join('school c','a.school_id = c.id')
                            ->where($map)
//                            ->whereIn('a.status','1,2,3')
                            ->field(['a.id','a.logo_img','a.shop_name','a.link_name','a.link_tel','a.status','b.name'=>'manage_category_name','c.name'=>'school_name'])
//                            ->fetchSql()
                            ->paginate($page_size)->toArray();

        if(!$data['data']) {
            $this->error('暂无数据');
        }

        $result = [];

        foreach ($data['data'] as $row){
            $result['info'][] = [
                'id' => $row['id'],
                'logo_img' => $row['logo_img'],
                'shop_name' => $row['shop_name'],
                'link_name' => $row['link_name'],
                'link_tel' => $row['link_tel'],
                'manage_category_name' => $row['manage_category_name'],
                'school_name' => $row['school_name'],
                'status' => $row['status'],
                'mb_status' => $config('shop_check_status')[$row['status']],

            ];
        }
        $result['count'] = $data['total'];
        $result['page'] = $data['current_page'];
        $result['pageSize'] = $data['per_page'];
        $this->success('获取成功',$result);

    }

    /**
     * 商家审核详情
     */
    public function checkDetail(Request $request)
    {
        $shop_id = $request->param('shop_id');

        if(!$shop_id) {
            $this->error('非法传参');
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
            $result['shop_info']['status'] = config('shop_check_status')[$row['status']];
            $result['shop_info']['school'] = Model('School')->getNameById($row['school_id']);
            $result['shop_info']['manage_category_name'] = Model('ManageCategory')->getNameById($row['manage_category_id']);
        }

        $shop_more_info = $this->shopModel->getShopMoreInfo($shop_id);
        //dump($shop_more_info);
        //商家资质
        $shop_qualification = [];

        //收款信息
        $shop_account = [];
        //exit;
        foreach ($shop_more_info as $row)
        {
            $shop_qualification['business_license'] = $row['business_license'];
            $shop_qualification['proprietor'] = $row['proprietor'];
            $shop_qualification['hand_card_front'] = $row['hand_card_front'];
            $shop_qualification['hand_card_back'] = $row['hand_card_back'];
            $shop_qualification['user_name'] = !empty($row['user_name']) ? $row['user_name'] : $row['proprietor'];
            $shop_qualification['identity_num'] = $row['identity_num'];
            $shop_qualification['sex'] = config('sex')[$row['sex']];
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
     * 商家审核状态
     */
    public function checkStatus(Request $request)
    {
        $shop_id = $request->param('shop_id');
        $status = $request->param('status');
        $remark = $request->param('remark');

        if(empty($shop_id) || empty($status)){
            $this->error('非法传参','404');
        }

        $shopInfo = Model('ShopInfo');

        if($status == '2') {
            if(empty($remark)){
                $this->error('请填写不通过理由哦');
            }
            $res = $shopInfo->update([
                'status' => $status,
                'remark' => $remark
            ],['id' => $shop_id]);

        } else {
            $res = $shopInfo->where('id',$shop_id)->setField('status',$status);
        }


        if($res) {
            $this->success('更新成功');
        }

        $this->error('更新失败');

    }


    /**
     * 商家审核展示
     */
    public function checkShow()
    {
        $data = config('check_status')['shop'];
        $this->success('获取成功',$data);
    }


    /**
     * 商家排序列表【默认学校】
     */
    public function sortInfo(Request $request)
    {
        // 学校列表
        $school_list = Model('school')->getSchoolList();

        // 搜索条件
        !empty($request->get('name/s')) ? $where[] = ['shop_name','like',$request->get('name/s').'%'] : null;
        if (!empty($request->get('school_id/d'))) {
            $where[] = ['school_id','=',$request->get('school_id/d')];
            $current_school = Model('school')->getSchoolInfoById($request->get('school_id/d'));
        } else {
            // 获取第一个学校
            $current_school = $school_list[0]['children'][0];

            $where[] = ['school_id','=',$current_school['id']];
        }

        // 获取当前学校的已审核通过的商铺列表
        $shop_list = Model('Shop')->getCurSchShopList($where);

        $shop_sort_list = [];
        foreach ($shop_list as $k=>$row) {
            $shop_sort_list[$k]['shop_id'] = $row['id'];
            $shop_sort_list[$k]['shop_name'] = $row['shop_name'];
            $shop_sort_list[$k]['logo_img'] = $row['logo_img'];
            $shop_sort_list[$k]['school_name'] = Model('School')->getNameById($row['school_id']);
            $shop_sort_list[$k]['sort'] = $row['sort'];
        }

        $this->success('获取成功',['school_list'=>$school_list,'shop_sort_list'=>$shop_sort_list,'current_school'=>$current_school]);
    }


    /**
     * 商家排序
     */
    public function sort(Request $request)
    {
        $string = $request->param('sort_list');
        $data = json_decode($string,true);
        $shop = new ShopInfoModel;
        $result = $shop->saveAll($data);

        if (!$result) {
            $this->error('设置失败');
        }
        $this->success('设置成功');
    }

}