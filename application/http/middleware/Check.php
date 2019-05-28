<?php

namespace app\http\middleware;

use app\common\Auth\JwtAuth;
use think\exception\ErrorException;
use think\Request;

class Check
{
    public function handle($request, \Closure $next)
    {
        try{
            $token = $request->param('token');

            if($token) {
                $jwtAuth = JwtAuth::getInstance();
                $jwtAuth->setToken($token);

                if($jwtAuth->verify() && $jwtAuth->validate()){
                    return $next($request);
                } else {
                    return json_error('Token 过期','1001');
                }
            } else {
                return json_error('token 不存在','1002');
            }
        }catch (ErrorException $e){
            json_error($e->getMessage(),$e->getCode());
        }

    }
}
