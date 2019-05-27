<?php

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use think\Request;
use app\common\model\TodayDeals;

/**
 * 商品模块控制器
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
            ->where('shop_id',$this->shop_id)
            ->where('today',$today)
            ->find();

        return json_success('success',$result);

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

        $product = model('Product')
            ->field('thumb,name,old_price')
            ->where('id',$data['product_id'])
            ->find();
        $data['thumb'] = $product->thumb;
        $data['name'] = $product->name;
        $data['old_price'] = $product->old_price;

        $result = TodayDeals::create($data);

        return json_success('success',$result);
    }


    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data   = $request->param();
        $result = TodayDeals::update($data, ['id' => $id]);
        return json_success('success',$result);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $result = TodayDeals::destroy($id);
        return json_success('success',$result);
    }
}