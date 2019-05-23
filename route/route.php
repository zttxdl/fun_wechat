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

// 管理平台优惠券
Route::group('a-coupon', function () {
    Route::get('/index', 'index');
    Route::get('/info/:id', 'show');
    Route::get('/add', 'add');
    Route::post('/create', 'create');
    Route::get('/edit/:id', 'edit');
    Route::post('/update', 'update');
    Route::post('/status', 'status');
})->prefix('admin/coupon/');



