<?php

namespace app\common\model;

use think\Model;

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
    public function getNameById($id){
        return $this->where('id',$id)->limit(1)->value('name');
    }

    /**
     *获取商品图片
     *
     */
    public function getImgById($id){
        return $this->where('id',$id)->value('thumb');
    }
}
