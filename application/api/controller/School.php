<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\School as SchoolModel;
use app\common\controller\ApiBase;

class School extends ApiBase
{
    protected  $noNeedLogin = [];


    /**
     * 学校地区
     *
     */
    public function index()
    {
        $model = new SchoolModel();

        $school_list = $model->getShopList();

        $this->success('获取学校地区列表成功',['school_list'=>$school_list]);
    }

    
}
