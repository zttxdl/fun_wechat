<?php

namespace app\common\controller;

use think\App;
use think\Controller;
use app\common\Auth\JwtAuth;
use EasyWeChat\Factory;


/**
 * 商品规格属性模块控制器
 */
class ApiBase extends Controller
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
        $token = $this->request->header('api-token', '');
        if ($token == '') {
            return json_error('请输入api-token', 500);
        }
        $jwtAuth = new JwtAuth();
        $this->auth = $jwtAuth->checkToken($token);
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
     * 获得子分类
     * @param $category
     * @param int $parent_id
     * @return array
     */
    protected function getSonCategory($category, $pid = 0)
    {
        $arr  = array();
        foreach ($category as $key => $value) {
            if ($value['pid'] == $pid) {
                $value['son'] = $this->getSonCategory($category, $value['id']);
                $arr[] = $value;
            }
        }
        return $arr;
    }
}
