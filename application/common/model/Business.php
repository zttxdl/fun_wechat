<?php


namespace app\common\model;


use think\Model;

class Business extends Model
{
    /**
     * 根据id 获取学校名称
     * @param $school_id
     * @return mixed
     */
    public function getSchoolNameById($school_id)
    {
        $res = $this->name('school')->field('name')->where('id',$school_id);
        return $res;
    }

    /**
     * 获取已经审核的商家店铺
     * @param $page_no
     * @param $page_size
     * @return mixed
     */
    public function getBusinessList($page_no,$page_size)
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
    public function getBusinessStock($shop_id)
    {
        $data = $this->name('product')->where('shop_id',$shop_id)->count('id');
        return $data;
    }

    /**
     * 获取月销售额
     */
    public function getMonthSales()
    {

    }

    /**
     * 获取销售总额
     */
    public function getCountSales()
    {

    }



}