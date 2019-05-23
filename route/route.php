<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//登录注册用户组
Route::group('merchants',function (){
        Route::rule('login','merchants/Login/login');
        Route::rule('register','merchants/Login/register');
        Route::rule('updatePasswor','merchants/Login/updatePasswor');
        Route::rule('phoneValidate','merchants/Login/phoneValidate');
        Route::rule('getMobileCode','merchants/Login/getMobileCode');
});
