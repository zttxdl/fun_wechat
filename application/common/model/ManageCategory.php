<?php

namespace app\common\model;

use think\Model;

class ManageCategory extends Model
{

    private $table_name = 'manage_category';

    public function getNameById($id)
    {
        return $this->where('id',$id)->value('name');

    }

    /**
     * 获取经营品类列表
     * 
     */
    public function getManageCategoryList()
    {
        $list = $this->field('id,name')->order('sort','asc')->select()->toArray();
        return $list;
    }


    /**
     * 获取经营品类列表【专门针对前端优惠券部分】
     * 
     */
    public function getManageCategoryListForFront()
    {
        $list = $this->field('id as value,name as label')->order('sort','asc')->select()->toArray();
        return $list;
    }


    /**
     * 获取经营品类名称集合
     * 
     */
    public function getNames($ids)
    {
        $category_arrs = $this->where('id','in',$ids)->column('name');
        $category_names = implode('、',$category_arrs);
        return $category_names;
    }
    
     

}
