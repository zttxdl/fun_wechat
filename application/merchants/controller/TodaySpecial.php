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
    protected $redisKey;

    public function __construct()
    {
        $this->redis = Cache::store('redis');
        $this->redisKey = 'TodayDeals';
    }

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

        if($this->getData($data['shop_id'])) {
            $this->error('店铺已经存在特价商品,活动到期在设置哦');
        }

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

        $result = TodayDeals::create($data);

        //数据存入缓存并设置生存周期
        $expire = $data['end_time'] - $data['start_time'];
        $this->setData($data['shop_id'],$data,$expire);

        $this->success('success',$result);
    }

    public function setData($shop_id,$data,$expire)
    {

        $this->redis->hSet($this->redisKey,$shop_id,json_encode($data,JSON_UNESCAPED_UNICODE));
        $this->redis->expire($this->redisKey,$expire);

        return true;
    }

    public function getData($shop_id)
    {
        return $this->redis->hGet($this->redisKey,$shop_id);
    }


}