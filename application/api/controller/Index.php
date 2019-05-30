<?php

namespace app\api\controller;

use app\common\controller\ApiBase;
use think\Request;
use think\Db;

class Index extends ApiBase
{

    protected $noNeedLogin = ['*'];

    //首页
    public function index(Request $request)
    {
        $lat = $request->param('latitude','');
        $lng = $request->param('longitude','');

        $data['slide'] = $this->getSlide();
        $data['channel'] = $this->getChannel();
        $data['special'] = $this->getSpecial($lat,$lng);
        $data['recommend'] = $this->getRecommendList($lat,$lng);

        return json_success('success',$data);
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
        $page = ($page-1) * $pagesize;

        $list = model('ShopInfo')->getDistance($lat,$lng,$page,$pagesize);

        if (! $list) {
            return [];
        }

        foreach ($list as &$value) {
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

        return json_success('success',$list);
    }


    //二级导航
    public function getNavigation(Request $request)
    {
        $lat = $request->param('latitude','');
        $lng = $request->param('longitude','');
        $class_id = $request->param('class_id','');
        $pagesize = input('pagesize',15);
        $page = input('page',1);
        $page = ($page-1) * $pagesize;

        $sql = "SELECT
            id,shop_name,marks,sales,up_to_send_money,run_time,
            address,manage_category_id,ping_fee,
            (
                ROUND(6371 * acos (
                    cos ( radians( '".$lat."' ) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( '".$lng."' ) ) + sin ( radians( '".$lat."' ) ) * sin( radians( latitude ) ) 
                ) 
            )) AS distance 
        FROM
            fun_shop_info 
		WHERE manage_category_id = $class_id
        HAVING
            distance < 3 
        ORDER BY distance 
        LIMIT $page,$pagesize";

        $list = Db::query($sql);

        if (! $list){
            return json_success('success',$list);
        }

        foreach ($list as &$value) {
            $value['disc'] = model('ShopDiscounts')
                ->field('face_value,threshold')
                ->where('shop_id',$value['id'])
                ->where('delete',0)
                ->select();
        }

        return json_success('success',$list);
    }

}
