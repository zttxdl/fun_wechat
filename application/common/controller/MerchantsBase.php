<?php

namespace app\common\controller;

use think\App;
use app\common\controller\Base;
use app\common\Auth\JwtAuth;

/**
 * 商家端基类控制器
 */
class MerchantsBase extends Base
{
    protected $noNeedLogin = [];
    protected $shop_id;
    protected $auth;

    function __construct(App $app = null)
    {
        parent::__construct($app);
        $action = $this->request->action();

        //判断是否要登录验证
        if (! $this->match($action)){
            $this->valid_token();
            $this->isDisable();
        }
    }

    public function valid_token()
    {
        $token = $this->request->header('api-token','');

        $jwtAuth = new JwtAuth();
        $jwt = $jwtAuth->checkToken($token);
        $this->auth =$jwt['data'];
        $this->shop_id = $this->auth->id;

    }

    /**
     * 检测当前控制器方法是否匹配传递的数组
     * @param array $action 需要验证的方法
     * @return bool
     */
    protected function match($action)
    {

        if (!is_array($this->noNeedLogin) || empty($this->noNeedLogin)){
            return false;
        }

        if (in_array($action,$this->noNeedLogin) || in_array('*',$this->noNeedLogin)){
            return true;
        }

        return false;
    }

    /**
     * 获得子分类
     * @param $category
     * @param int $parent_id
     * @return array
     */
    protected function getSonCategory($category, $pid=0){
        $arr  = array();
        foreach ($category as $key=>$value){
            if ($value['pid'] == $pid){
                $value['son'] = $this->getSonCategory($category,$value['id']);
                $arr[] = $value;
            }
        }
        return $arr;
    }

    /**
     * 是否禁用
     * @return boolean [description]
     */
    protected function isDisable(){
        $status = model('ShopInfo')->where('id',$this->shop_id)->value('status');
        if ($status == 4) {
            $this->error('您已被禁用，请联系客服',401);
        }

        return true;
    }

}