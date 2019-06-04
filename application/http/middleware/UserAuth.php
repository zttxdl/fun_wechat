<?php

namespace app\http\middleware;

use function GuzzleHttp\json_encode;
use think\Request;
use app\common\model\User;

class UserAuth
{
    public function handle($request, \Closure $next)
    {
        $uid = $request->has('uid') ? $request->param('uid') : '';
        if (!$uid) {
            return json_error('参数出错，暂无登录', 205);
        } 
        
        $result = User::find($uid);
        if (!$result) {
            return json_error('未查到该用户',201);
        }

        return $next($request);
    }
}
