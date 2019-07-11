<?php

namespace app\http\middleware;

use think\Request;
use app\common\model\User;

class UserAuth
{
    use  \app\common\controller\Jump;

    public function handle($request, \Closure $next)
    {
        $uid = $request->header('user-auth');
        if (!$uid) {
            $this->error('参数出错，暂无登录', 205);
        } 
        
        $result = User::find($uid);
        if (!$result) {
            $this->error('未查到该用户',201);
        }

        return $next($request);
    }
}
