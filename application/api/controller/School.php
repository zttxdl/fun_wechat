<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\School as SchoolModel;

class School extends Controller
{
    /**
     * 学校地区
     *
     */
    public function index()
    {
        $model = new SchoolModel();

        $school_list = $model->getShopList();

        $this->succes('获取学校地区列表成功',['school_list'=>$school_list]);
    }

    
}
