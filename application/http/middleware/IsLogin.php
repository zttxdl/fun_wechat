<?php

namespace app\http\middleware;

use app\common\Auth\JwtAuth;
use think\exception\ErrorException;
use think\Request;
use think\Controller;

class IsLogin extends Controller
{


    public function handle($request, \Closure $next)
    {
        try{
            $phone = session('admin_user.phone');
            if($phone) {
                return $next($request);
            } else {
                $this->error('用户未登录','10001');
            }
        }catch (ErrorException $e){
            $this->error($e->getMessage());
        }

    }
}
