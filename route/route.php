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
    Route::get('/shop-list/:id', 'getSchoolShop');
})->prefix('admin/coupon/');


//登录注册用户组
Route::group('merchants',function (){
    Route::rule('login','merchants/Login/login');
    Route::rule('register','merchants/Login/register');
    Route::rule('updatePasswor','merchants/Login/updatePasswor');
    Route::rule('phoneValidate','merchants/Login/phoneValidate');
    Route::rule('getMobileCode','merchants/Login/getMobileCode');

	Route::rule('createShop','merchants/Merchants/createShop');
	Route::rule('getSchool','merchants/Merchants/getSchool');
	Route::rule('getCategory','merchants/Merchants/getCategory');
	Route::rule('getBack','merchants/Merchants/getBack');
	//文件上传
	Route::rule('upload','merchants/Upload/up');


});

<<<<<<< HEAD
//後臺登录用戶
Route::group('user',function (){
    Route::rule('login','admin/Login/login');
    Route::rule('info','admin/Login/info')->middleware('Check');
=======
//后台用户模块
Route::group('admin',function (){
    Route::rule('login','admin/Login/login');//用户登录
    Route::rule('register','admin/Login/register');//用户注册
    Route::rule('verify','admin/Login/verify');//验证码
    Route::rule('index','admin/Admin/index');//后台用户列表
    Route::rule('add','admin/Admin/add');//后台用户新增
    Route::rule('edit','admin/Admin/update');//后台用户新增

});

//后台首页模块
Route::group('admin',function (){
    Route::rule('index/info','admin/Index/getUserList');
});

//后台会员模块
Route::group('admin',function (){
    Route::rule('user/list','admin/User/getList');//会员列表
    Route::rule('user/detail','admin/User/getDetail');//会员详情
    Route::rule('user/recycle','admin/User/recycle');//回收站

>>>>>>> ztt
});
