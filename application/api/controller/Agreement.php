<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\Agreement as AgreementModel;
use app\common\controller\ApiBase;

class Agreement extends ApiBase
{

    protected  $noNeedLogin = ['*'];


    /**
     * 图文协议详情 
     * 
     */
    public function index($id)
    {
        $model = new AgreementModel();
        $info = $model->getAgreementContent($id);
        
        $this->success('获取图文协议成功',['info'=>$info]);
    }
}
