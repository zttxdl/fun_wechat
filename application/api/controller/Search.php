<?php

namespace app\api\controller;

use app\common\controller\ApiBase;
use think\Request;
use think\Db;

class Search extends ApiBase
{
    protected $noNeedLogin = [];

    //搜索页面
    public function index()
    {
        $user_id = $this->auth->id;
        $data['hot'] = model('HotSearch')
            ->distinct(true)
            ->field('keywords')
            ->order('add_time','desc')
            ->limit(8)
            ->select();
        $data['history'] = model('Search')
            ->distinct(true)
            ->field('keywords')
            ->where('user_id',$user_id)
            ->order('add_time','desc')
            ->limit(8)
            ->select();

        $this->success('success',$data);
    }

    //搜索
    public function search(Request $request)
    {
        $user_id = $this->auth->id;
        $keywords = $request->param('keywords');
        $lat = $request->param('latitude');
        $lng = $request->param('longitude');
        $pagesize = $request->param('pagesize',15);
        $page = $request->param('page',1);

        //记录历史搜索
        $data = ['user_id'=>$user_id,'keywords'=>$keywords,'add_time'=>time()];
        model('Search')->insert($data);

        //搜索周边
        $list = Db::name('shop_info a')
            ->join('product b','a.id = b.shop_id')
            ->field("a.id,a.shop_name,a.marks,a.sales,a.logo_img,a.up_to_send_money,a.run_time,
            a.address,a.manage_category_id,a.ping_fee,ROUND(6371 * acos (cos ( radians($lat)) * cos( radians( a.latitude ) ) * cos( radians( a.longitude ) - radians( $lng) ) + sin ( radians( $lat) ) * sin( radians( a.latitude ) ) ),1 ) AS distance ")
            ->where('a.shop_name|b.name','like','%'.$keywords.'%')
            ->having('distance < 3')
            ->page($page,$pagesize)
            ->select();

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


}