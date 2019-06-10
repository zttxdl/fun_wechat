<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\ManageCategory as ManageCategoryModel;

class ManageCategory extends Controller
{
    /**
     * 获取经营品类二级列表
     *
     */
    public function index()
    {
        $model = new ManageCategoryModel();

        $manage_list = $model->getManageCategoryList();

        $this->success('获取经营品类二级列表成功',['manage_list'=>$manage_list]);
    }
}
