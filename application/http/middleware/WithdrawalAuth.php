<?php

namespace app\http\middleware;

use think\Request;

class WithdrawalAuth
{
    /**
     * 骑手提现时间
     */
    public function handle($request, \Closure $next)
    {
        // 每周二为提现日
        if((date('w') != 2)){
            $this->error('每周二为提现日！今天不可提现', 205);
        }

        return $next($request);
    }
}
