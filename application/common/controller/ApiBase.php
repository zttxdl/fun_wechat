<?php

namespace app\common\controller;

use think\App;
use app\common\Auth\JwtAuth;


/**
 * 用户端基类控制器
 */
class ApiBase extends Base
{
    protected $noNeedLogin = [];
    protected $auth;
    
    function __construct(App $app = null)
    {
        parent::__construct($app);
        $action = $this->request->action();

        //判断是否要登录验证
        if (!$this->match($action)) {
            $this->valid_token();
        }
    }

    protected function valid_token()
    {
        $token = $this->request->header('api-token');
        $jwtAuth = new JwtAuth();
        $jwt = $jwtAuth->checkToken($token);
        $this->auth =$jwt['data'];
    }

    /**
     * 检测当前控制器方法是否匹配传递的数组
     * @param array $action 需要验证的方法
     * @return bool
     */
    protected function match($action)
    {

        if (!is_array($this->noNeedLogin) || empty($this->noNeedLogin)) {
            return false;
        }

        if (in_array($action, $this->noNeedLogin) || in_array('*', $this->noNeedLogin)) {
            return true;
        }

        return false;
    }

    /**
     * 是否禁用
     * [isDisable description]
     * @param  [type]  $id [description]
     * @return boolean     [description]
     */
    protected function isDisable($id){
        $status = model('ShopInfo')->where('id',$id)->value('status');
        if ($status == 4) {
            $this->error('该商家已下线',401);
        }

        return true;
    }
}
