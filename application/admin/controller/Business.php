<?php


namespace app\admin\controller;


use think\Db;
use think\facade\Request;

class Business
{
    /**
     * 获取商家列表
     */
    public function getList()
    {
        $page_no = Request::param('page_no');
        $page_size = 5;
        $data = Db::name('shop_info')->order('id','desc')->page($page_no,$page_size)->select();

        
        dump($data);
        return json_success('获取成功',$shop_list);
    }

    /**
     * 获取商家详情
     */
    public function getDetail()
    {
        $id = Request::param('id');


    }

    /**
     * 添加店铺
     */
    public function addShop()
    {

    }

    /**
     * 添加商家资质
     */
    public function addQualification()
    {

    }

    /**
     * 添加收款信息
     */
    public function addAccount()
    {

    }

    /**
     * 商家审核列表
     */
    public function checkList()
    {
//        $list = Db::name('')
    }

    /**
     * 商家审核详情
     */
    public function checkDetail()
    {

    }

    /**
     * 商家审核
     */
    public function check()
    {

    }
}