<?php

namespace app\common\model;

use think\Model;

class ProductsClassify extends Model
{
    protected $autoWriteTimestamp = true;
    protected $insert             = [
        'status' => 1,
    ];

    /**
     * 获取分类名称
     * @param $id
     * @return mixed
     */
    public function getNameById($id){
        return $this->where('id',$id)->value('name');
    }

}
