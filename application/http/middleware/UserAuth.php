<?php

namespace app\http\middleware;

use function GuzzleHttp\json_encode;

class UserAuth
{
    public function handle($request, \Closure $next)
    {
        $uid = isset($_SERVER['HTTP_API_TOKEN']) ? $_SERVER['HTTP_API_TOKEN'] : '';
        
        if ($uid) {
            return $next($request);
        } else {
            return json_encode('用户没有登录');
        }
    }
}
