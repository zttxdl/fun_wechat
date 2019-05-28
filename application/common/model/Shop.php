<?php


namespace app\common\model;


use think\Model;

class Shop extends Model
{
    private $table_name = 'shop_info';
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

    }

    /**
     * 获取店铺销售总额
     */
    public function getCountSales($shop_id)
    {

    }



}