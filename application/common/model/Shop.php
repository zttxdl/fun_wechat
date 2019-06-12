<?php


namespace app\common\model;


use think\Model;

class Shop extends Model
{
    private $table_name = 'shop_info';
    private $shop_id;
    /**
     * 获取已经审核的商家店铺
     * @param $page_no
     * @param $page_size
     * @return mixed
     */
    public function getShopList($page_no,$page_size)
    {
        $data = $this->name('shop_info')
            ->where('status',1)
            ->order('id','desc')
            ->page($page_no,$page_size)
            ->select();

        return $data;

    }

    /**
     * 获取指定商家信息
     * @param $shop_id
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopInfo($shop_id)
    {
        $data = $this->name('shop_info')
            ->where('id',$shop_id)
            ->select();

        return $data;
    }

    /**
     * 获取店铺在售商品
     */
    public function getShopStock($shop_id)
    {
        $data = $this->name('product')->where('shop_id',$shop_id)->count('id');
        return $data;
    }

    /**
     * 获取店铺月销售额
     */
    public function getMonthSales($shop_id)
    {
        $start_time = date('Y-m-01',strtotime(date('Y-m-d H:i:s')));

        $end_time =  strtotime("$start_time +1 month -1 day");

        $start_time = strtotime($start_time);

//        dump($start_time);
//        dump($end_time);

        $data = $this->name('orders')->where('pay_status',1)
            ->where('shop_id',$shop_id)
            ->whereBetweenTime('add_time',$start_time,$end_time)
            ->sum('money');

        return $data;

    }

    /**
     * 获取店铺销售总额
     */
    public function getCountSales($shop_id)
    {
        $data = $this->name('orders')->where('pay_status',1)
            ->where('shop_id',$shop_id)
            ->sum('money');

        return $data;

    }

    /**
     * 获取店铺明细
     * @param $shop_id
     */
    public function getShopDetail($shop_id)
    {
        $data = $this->name($this->table_name)
            ->field('shop_name,logo_img,link_name,link_tel,manage_category_id,school_id,address')
            ->where('id',$shop_id)->find();
        return $data;
    }

    /**
     * 获取店铺补充信息
     */
    public function getInformation()
    {

    }

    /**
     * 获取店铺更多信息
     * @param $shop_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopMoreInfo($shop_id)
    {
        $data = $this->name('shop_more_info')->where('shop_id',$shop_id)->select();
        return $data;
    }

    /**
     * 获取商家资质
     */
    public function getShopQualification($shop_id)
    {
        $data = $this->name('shop_more_info')
            ->where('shop_id',$shop_id)
            ->field('business_license,proprietor,hand_card_front,user_name,identity_num,sex,licence')
            ->find();
        return $data;
    }


    /**
     * 获取收款信息
     * @param $shop_id
     */
    public function getAccount($shop_id)
    {
        $data = $this->name('shop_more_info')
            ->where('shop_id',$shop_id)
            ->field('branch_back,back_hand_name,back_card_num')
            ->find();
        return $data;
    }

    /**
     * 获取店铺在售商品
     * @param $shop_id
     */
    public function getIsOnlineGoods($shop_id)
    {
        $data = $this->name('product')
            ->alias('a')
            ->join('products_classify b','a.products_classify_id = b.id')
            ->where('a.shop_id',$shop_id)
            ->where('a.status',1)
            ->field('a.id,a.name,a.attr_ids,b.name,a.price')
            ->select();
        return $data;
    }

    /**
     * 获取一级商品属性名称集合
     * @param string $attr
     * @return array|mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function getGoodsAttrName($attr_ids = '')
    {
        $res = $this->name('product_attr_classify')->field('name')->whereIn('id',$attr_ids)->select();
        return $res;
    }

    /**
     * 获取结算
     */
    public function getSettle()
    {
        $total_num = '';//总订单量
        $month_order_num = '';//月订单量
        $day_order_num = '';//日均订单量
        $settlement_money = '';//结算金额
        $settlement_wait_money = '';//待结算金额
        $month_money = '';//月销售额
        $total_money = '';//总销售额


    }

    /**
     * 商家排序更新
     * @param $map
     * @return bool|int
     */
    public function sortEdit($map)
    {
        $res = $this->name($this->table_name)->where('id',$map['shop_id'])->setField('sort',$map['sort']);

        return isset($res) ? $res :false ;
    }




}