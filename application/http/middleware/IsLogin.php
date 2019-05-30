<?php

namespace app\http\middleware;

use app\common\Auth\JwtAuth;
use think\exception\ErrorException;
use think\Request;

class IsLogin
{
    public function handle($request, \Closure $next)
    {
        try{
            $phone = session('admin_user.phone');

            if($phone) {
                return $next($request);
            } else {
                return json_error('用户未登录','500');
            }
        }catch (ErrorException $e){
            json_error($e->getMessage(),$e->getCode());
        }

    }
}
