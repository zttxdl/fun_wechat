<?php

namespace app\common\controller;

use think\App;
use think\Controller;
use think\Container;
use think\exception\HttpResponseException;
use think\Response;
use think\facade\Cache;


/**
 * 商品规格属性模块控制器
 */
class Base extends Controller
{
    protected $app;
    function __construct(App $app = null)
    {
        parent::__construct($app);
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
    protected function error($msg = '', $code = 201, $data = null, $type = null, array $header = [])
    {
        $this->result($data, $code, $msg, $type, $header);
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

    /**
     * 获取缓存
     * @param $key
     * @param null $value
     * @param int $expire
     * @return mixed
     */
    protected function getDataCache($key, $value = null, $expire = 0)
    {
        $cache = Cache::store('redis');
        if ($value === false) $cache->rm($key);
        elseif ($value === null) {
            return $cache->get($key);
        } else $cache->set($key, $value, $expire);
    }

    /**
     * 设置缓存
     * @param $param
     * @param $data
     * @param int $options
     */
    protected function setDataCache($param, $data, $options = 60)
    {
        $store = 'file'; //redis,file
        Cache::store($store)
            ->set('api_' . $param['name'], $data, $options, $param['tag']);
    }
}
