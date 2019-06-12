<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\ManageCategory as ManageCategoryModel;
use app\common\controller\ApiBase;

class ManageCategory extends ApiBase
{
    protected  $noNeedLogin = ['*'];

    
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
