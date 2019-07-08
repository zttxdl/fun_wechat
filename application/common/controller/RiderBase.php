<?php

namespace app\common\controller;

use think\App;
use think\Controller;
use app\common\Auth\JwtAuth;
use think\facade\Cache;
use think\Container;
use think\exception\HttpResponseException;
use think\Response;



/**
 * 商品规格属性模块控制器
 */
class RiderBase extends Controller
{
    protected $noNeedLogin = [];
    protected $auth;
    protected $app;

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

    /**
     * 操作成功返回的数据
     * @param string $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为1
     * @param string $type   输出类型
     * @param array  $header 发送的 Header 信息
     */
    protected function success($msg = '', $data = null, $code = 200, $type = null, array $header = [])
    {
        $this->result($data, $code, $msg, $type, $header);
    }

    /**
     * 操作失败返回的数据
     * @param string $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为0
     * @param string $type   输出类型
     * @param array  $header 发送的 Header 信息
     */
    protected function error($msg = '', $code = 201, $type = null, array $header = [])
    {
        $this->result($data = '', $code, $msg, $type, $header);
    }

    /**
     * 返回封装后的API数据到客户端
     * @access protected
     * @param  mixed     $data 要返回的数据
     * @param  integer   $code 返回的code
     * @param  mixed     $msg 提示信息
     * @param  string    $type 返回数据格式
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function result($data, $code = 0, $msg = '', $type = '', array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data,
        ];

        $type     = $type ?: $this->getResponseType();
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        if (!$this->app) {
            $this->app = Container::get('app');
        }

        $isAjax = $this->app['request']->isAjax();
        $config = $this->app['config'];

        return $isAjax
            ? $config->get('default_ajax_return')
            : $config->get('default_return_type');
    }

}
