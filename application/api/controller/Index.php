<?php

namespace app\api\controller;

use app\common\controller\ApiBase;
use think\Request;

class Index extends ApiBase
{

    protected $noNeedLogin = ['*'];

    //首页
    public function index(Request $request)
    {
        $lat = $request->param('latitude','');
        $lng = $request->param('longitude','');
        set_log('lat',$lat,'index');
        set_log('lng',$lng,'index');
        $data['slide'] = $this->getSlide();
        $data['channel'] = $this->getChannel();
        $data['special'] = $this->getSpecial($lat,$lng);
        $data['recommend'] = $this->getRecommendList($lat,$lng);

        $this->success('success',$data);
    }

    //通过经纬度获取最近学校
    public function  getSchool(Request $request)
    {
        $lat = $request->param('latitude','');
        $lng = $request->param('longitude','');

        $data = model('School')->field("id,name,ROUND(6371 * acos (cos ( radians($lat)) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( $lng) ) + sin ( radians( $lat) ) * sin( radians( latitude ) ) ),1 ) AS distance ")
            ->having('distance < 5')
            ->where('level',2)
            ->order('distance asc')
            ->find();

        $this->success('success',$data);
    }

    //获取轮播图
    protected function getSlide()
    {

        $where[] = [
            'status',
            '=',
            1,
        ];
            $where[] = [
                'platfrom',
                '=',
                1,
            ];
        $data = model('Advers')
            ->field('id,name,img,link_url')
            ->where($where)
            ->order('sort','asc')
            ->select();

        return $data;
    }

    //分类导航
    public function getChannel()
    {
        $where[] = [
            'status',
            '=',
            1,
        ];

        $where[] = [
            'level',
            '=',
            1,
        ];

        $data = model('ManageCategory')
            ->field('id,name,img')
            ->where($where)
            ->order('sort','asc')
            ->limit(4)
            ->select();

        return $data;
    }

    //今日特价
    public function getSpecial($lat,$lng)
    {
        $list = model('ShopInfo')->getDistance($lat,$lng);
        if (! $list) {
            return [];
        }

        $data = array_column($list,'id');
        $shop_ids=  implode(",",$data);
        $where[] = [
            'status',
            '=',
            1,
        ];

        $where[] = [
            'shop_id',
            'in',
            $shop_ids,
        ];
        $day = date('Y-m-d',time());
        $where[] = [
            'today',
            '=',
            $day,
        ];

        $data = model('TodayDeals')
            ->field('name,shop_id,product_id,old_price,price,num,limit_buy_num,thumb')
            ->where($where)
            ->whereTime('end_time', '>=', time())
            ->limit(4)
            ->select();

        return $data;
    }

    //推荐商家
    public function getRecommendList($lat,$lng)
    {
        $pagesize = input('pagesize',15);
        $page = input('page',1);
        $list = model('ShopInfo')->getDistance($lat,$lng,$page,$pagesize);

        if (empty($list)) {
            return [];
        }

        foreach ($list as &$value) {
            if (! empty($value['run_time'])){
                $value['business'] = model('ShopInfo')->getBusiness($value['run_time']);
            }else{
                $value['business'] = 0;
            }

            $value['disc'] = model('ShopDiscounts')
                ->field('face_value,threshold')
                ->where('shop_id',$value['id'])
                ->where('delete',0)
                ->select();
        }

        return $list;
    }

    //推荐商家分页加载
    public function getRecommend(Request $request)
    {
        $lat = $request->param('latitude','');
        $lng = $request->param('longitude','');

        $list = $this->getRecommendList($lat,$lng);

        $this->success('success',$list);
    }


    //二级导航
    public function getNavigation(Request $request)
    {
        $lat = $request->param('latitude','');
        $lng = $request->param('longitude','');
        $class_id = $request->param('class_id','');
        $pagesize = input('pagesize',15);
        $page = input('page',1);

        $data = model('School')->field("id,name,ROUND(6371 * acos (cos ( radians($lat)) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( $lng) ) + sin ( radians( $lat) ) * sin( radians( latitude ) ) ),1 ) AS distance ")
            ->having('distance < 5')
            ->where('level',2)
            ->order('distance asc')
            ->find();

        $list = [];
        if ($data){
            $list = model('ShopInfo')->field("id,shop_name,logo_img,marks,sales,up_to_send_money,run_time,address,manage_category_id,ping_fee,school_id")
                ->where('school_id',$data->id)
                ->where('manage_category_id',$class_id)
                ->page($page,$pagesize)
                ->select()
                ->toArray();

        }


        if (empty($list)){
            $this->success('success',$list);
        }

        foreach ($list as &$value) {
            if (! empty($value['run_time'])){
                $value['business'] = model('ShopInfo')->getBusiness($value['run_time']);
            }else{
                $value['business'] = 0;
            }
            $value['disc'] = model('ShopDiscounts')
                ->field('face_value,threshold')
                ->where('shop_id',$value['id'])
                ->where('delete',0)
                ->select();
        }

        $this->success('success',$list);
    }

    //今日特价
    public function getSpecialList(Request $request)
    {
        $lat = $request->param('latitude');
        $lng = $request->param('longitude');
        $page = $request->param('page');
        $pagesize = $request->param('pagesize');

        $list = model('ShopInfo')->getDistance($lat,$lng);
        if (! $list) {
            return [];
        }

        $data = array_column($list,'id');
        $shop_ids=  implode(",",$data);
        $where[] = [
            'status',
            '=',
            1,
        ];

        $where[] = [
            'shop_id',
            'in',
            $shop_ids,
        ];
        $day = date('Y-m-d',time());
        $where[] = [
            'today',
            '=',
            $day,
        ];

        $data = model('TodayDeals')
            ->field('name,shop_id,product_id,old_price,price,num,limit_buy_num,thumb')
            ->where($where)
            ->whereTime('end_time', '>=', time())
            ->page($page,$pagesize)
            ->select()
            ->toArray();
        foreach ($data as &$val) {
            $fin = model('ShopInfo')->field('shop_name,up_to_send_money,ping_fee')->where('id',$val['shop_id'])->find();
            $val['shop_name'] = $fin->shop_name;
            $val['up_to_send_money'] = $fin->up_to_send_money;
            $val['ping_fee'] = $fin->ping_fee;
        }

        $this->success('success',$data);
    }
}
