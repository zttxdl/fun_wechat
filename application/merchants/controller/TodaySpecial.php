<?php

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use think\Request;
use app\common\model\TodayDeals;
use think\facade\Cache;

/**
 * 商品今天特价模块控制器
 */
class TodaySpecial extends MerchantsBase
{
    protected $noNeedLogin = [];

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $today = date('Y-m-d',time());
        $result = model('TodayDeals')
            ->field('id,name,product_id,old_price,price,num,limit_buy_num,thumb,start_time,end_time')
            ->where('shop_id',$this->shop_id)
            ->where('today',$today)
            ->order('create_time desc')
            ->find();
        if ($result) {
            $result->res_time = $result->end_time - time();
            $result->the_time = $result->start_time - time();
            $result->length = 1;
        }else {
            $result['length'] = 0;
        }

        $this->success('success',$result);

    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data   = $request->param();
        $data['shop_id'] = $this->shop_id;

        $data['start_time'] = strtotime($request->param('start_time'));
        $data['end_time'] = strtotime($request->param('end_time'));
        $data['today'] = date('Y-m-d',$data['start_time']);
        $data['create_time'] = time();
        //获取学校主键
        $data['school_id'] = model('ShopInfo')->where('id',$this->shop_id)->value('school_id');
        $product = model('Product')
            ->field('thumb,name,old_price')
            ->where('id',$data['product_id'])
            ->find();
        $data['thumb'] = $product->thumb;
        $data['name'] = $product->name;
        $count = model('TodayDeals')->where('today',$data['today'])->where('shop_id',$this->shop_id)->count();
        if ($count >= 3){
            $this->error('一天最多设置3次');
        }
        $result = TodayDeals::create($data);

        $this->success('success',$result);
    }




}