<?php

namespace app\common\model;

use think\Model;

class ManageCategory extends Model
{
    /**
     * 获取经营品类的二级列表
     * 
     */
    public function getManageCategoryList()
    {
        $list = $this->where('level',2)->field('id,name')->select();
        return $list;
    }
     
}
