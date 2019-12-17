<?php

namespace app\common\model;

use think\Model;
use think\Db;

class Product extends Model
{
    /**
     * 获取商品类型
     */
    public function getProductById($id)
    {
        return $this->field('*')->where('id',$id)->find();
    }

    /**
     * 获取单条商品名称
     * @param $id
     * @return mixed
     */
    public function getNameById($id)
    {
        return $this->where('id',$id)->limit(1)->value('name');
    }

    /**
     * 获取单条商品价格【原价】
     * @param $id
     * @return mixed
     */
    public function getPriceById($id)
    {
        return $this->where('id',$id)->value('price');
    }

    /**
     *获取商品图片
     *
     */
    public function getImgById($id)
    {
        return $this->where('id',$id)->value('thumb');
    }

    /**
     *获取商品月销量
     */
    public function getMonthSales($id)
    {
        
        $num = Db::name('product_sales')
                ->where('product_id',$id)
                ->whereTime('create_time', 'month')
                ->sum('num');

        return $num;
    }

    /**
     *获取商品原价
     */
    public function getGoodsOldPrice($id)
    {
        return $this->where('id',$id)->value('old_price');
    }
}   









