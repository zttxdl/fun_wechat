<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/3
 * Time: 11:12 AM
 */

namespace app\http\middleware;

//解决跨域问题
class CrossDomain
{
    public function handle($request, \Closure $next)
    {
        header('Access-Control-Allow-Origin: *');
        /*header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Max-Age: 1728000');*/
        /*if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }*/

        return $next($request);
    }
}