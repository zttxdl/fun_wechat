<?php

namespace app\common\controller;

use think\App;
use think\Controller;
use app\common\Auth\JwtAuth;
use think\facade\Cache;



/**
 * 商品规格属性模块控制器
 */
class RiderBase extends Controller
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

    /**
     * 获取缓存
     * @param $param
     * @param null $options
     * @return mixed
     */
    public function getDataCache($param, $options = null)
    {
        $store = 'file'; //redis,file

        return Cache::store($store)
            ->get('rider_' . $param['name']);
    }

    /**
     * 设置缓存
     * @param $param
     * @param $data
     * @param int $options
     */
    public function setDataCache($param, $data, $options = 60)
    {
        $store = 'file'; //redis,file
        Cache::store($store)
            ->set('rider_' . $param['name'], $data, $options, $param['tag']);
    }

    

}
