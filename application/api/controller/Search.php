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
        $school_id = $request->param( 'school_id');
        $pagesize = $request->param('pagesize',20);
        $page = $request->param('page',1);

        //记录历史搜索
        $data = ['user_id'=>$user_id,'keywords'=>$keywords,'add_time'=>time()];
        $where[] = ['user_id','=',$user_id];
        $where[] = ['keywords','=',$keywords];
        $find = model('Search')->where($where)->find();
        if ($find){
            model('Search')->where($where)->update(['add_time'=>time()]);
        }else{
            model('Search')->insert($data);
        }

        //搜索周边
        $list = Db::name('shop_info a')
            ->distinct(true)
            ->join('product b','a.id = b.shop_id')
            ->field("a.id,a.shop_name,a.marks,a.sales,a.logo_img,a.up_to_send_money,a.run_time,
            a.address,a.manage_category_id,a.ping_fee")
            ->where('a.shop_name|b.name','like','%'.$keywords.'%')
            ->where( 'a.school_id','=', $school_id)
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

    //删除搜索记录
    public function delete()
    {
        $where[] = ['user_id','=',$this->auth->id];
        $id = model('Search')->where($where)->delete();
        if ($id){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

}