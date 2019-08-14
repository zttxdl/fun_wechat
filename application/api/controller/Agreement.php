<?php

namespace app\api\controller;

use think\facade\Cache;
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
        $info = Cache::get('agreement_'.$id,'');
        if (!$info) {
            $model = new AgreementModel();
            $info = $model->getAgreementContent($id);
            Cache::store('redis')->set('agreement_'.$id,$info,3600*24*7*30);
        }
        
        $this->success('获取图文协议成功',['info'=>$info]);
    }
}
