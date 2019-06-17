<?php


namespace app\admin\controller;

//use think\Model;
use think\Controller;
use think\Model;
use think\Request;
use think\Db;
use app\common\model\Shop as ShopInfoModel;

class Shop extends Controller
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
        $page_size = config('page_size');

        $list = model('ShopInfo')
            ->alias('a')
            ->page($page_no,$page_size)
            ->whereIn('status','3,4')
            ->select();

        $shop_list = [];
        foreach ($list as $row)
        {
            if($row['id']) {
                $shop_list[] = [
                    'shop_name' => $row['shop_name'],
                    'logo_img' => $row['logo_img'],
                    'link_name' => $row['link_name'],
                    'link_tel' => $row['link_tel'],
                    'add_time' => date('Y-m-d',$row['add_time']),
                    'school_name' =>  Model('School')->getNameById($row['school_id']),
                    'shop_stock' =>  Model('Shop')->getShopStock($row['id']),
                    'status' => config('shop_check_status')[$row['status']],
                ];
            }
        }

        $this->success('获取成功',$shop_list);
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
            return json_error('店铺不存在');
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

                $row['attr_names'] = $res;
                //dump($res);exit;

                $row['attr_names'] = isset($row['attr_names']) ? $row['attr_names'] : '--';
            }
        }

        //结算信息
        $result['shop_settle'] = $this->shopModel->getSettle();
//        dump($result);
        $this->success('获取成功',$result);
    }


    /**
     * 商家审核列表
     */
    public function checkList(Request $request)
    {
        $page_no = $request->param('page_no');
        $page_size = 5;

        if(!$page_no) {
            $this->error('非法传参');
        }

        $data = model('shopInfo')
                            ->alias('a')
                            ->join('ManageCategory b','a.manage_category_id = b.id')
                            ->join('school c','a.school_id = c.id')
                            ->whereIn('a.status','1,2,3')
                            ->field(['a.id,a.logo_img','a.shop_name','a.link_name','a.link_tel','a.status','b.name'=>'manage_category_name','c.name'=>'school_name'])
                            ->select();

        $shop_check_list = [];

        foreach ($data as $row){
            $shop_check_list[] = [
                'id' => $row['id'],
                'logo_img' => $row['logo_img'],
                'shop_name' => $row['shop_name'],
                'link_name' => $row['link_name'],
                'link_tel' => $row['link_tel'],
                'manage_category_name' => $row['manage_category_name'],
                'school_name' => $row['school_name'],
                'status' => config('shop_check_status')[$row['status']]

            ];
        }

        $this->success('查询成功',$shop_check_list);


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
        // 获取第一个学校
        $current_school = $school_list[0]['children'][0];

        // 搜索条件
        !empty($request->get('name/s')) ? $where[] = ['shop_name','like',$request->get('name/s').'%'] : null;
        if (!empty($request->get('school_id/d'))) {
            $where[] = ['school_id','=',$request->get('school_id/d')];
            $current_school = Model('school')->getSchoolInfoById($request->get('school_id/d'));
        } else {
            $where[] = ['school_id','=',$current_school['id']];
        }

        // 获取当前学校的已审核通过的商铺列表
        // dump($where);die;
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
     * 展示当前学校的编辑排序页面 
     * 
     */
    public function editShopSort(Request $request)
    {
        $school_id = $request->param('school_id');
        if (!$school_id) {
            $this->error('非法参数');
        }

        // 获取当前学校名称
        $school_name = model('school')->getNameById($school_id);

        // 获取当前学校下的所有已审核通过的商家集合
        $where[] = ['school_id','=',$school_id];
        $shop_list = Model('Shop')->getCurSchShopList($where);

        $shop_sort_list = [];
        foreach ($shop_list as $k=>$row) {
            $shop_sort_list[$k]['shop_id'] = $row['id'];
            $shop_sort_list[$k]['shop_name'] = $row['shop_name'];
            $shop_sort_list[$k]['logo_img'] = $row['logo_img'];
            $shop_sort_list[$k]['school_name'] = Model('School')->getNameById($row['school_id']);
            $shop_sort_list[$k]['sort'] = $row['sort'];
        }

        $this->success('获取成功',['current_school_name'=>$school_name,'shop_sort_list'=>$shop_sort_list]);

    }
     


    /**
     * 商家排序
     */
    public function sort(Request $request)
    {
        $string = $request->param('sort_list');
    
        dump($string);die;
        $data = json_decode($string,true);
        $shop = new ShopInfoModel;
        $result = $shop->saveAll($data);

        if (!$result) {
            $this->error('设置失败');
        }
        $this->success('设置成功');
    }
}