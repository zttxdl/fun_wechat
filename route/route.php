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



/*************** 管理平台端 *********************************************************************************************/
// 广告组
Route::group('a-advers', function () {
    Route::get('/index', 'index');
    Route::get('/edit/:id', 'edit');
    Route::post('/update', 'update');
    Route::get('/del/:id', 'delete');
})->prefix('admin/advers/');


// 优惠券组
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

// 图文协议组
Route::group('a-agreement', function () {
    Route::get('/index', 'index');
    Route::get('/info/:id', 'show');
    Route::get('/edit/:id', 'edit');
    Route::post('/update', 'update');
})->prefix('admin/agreement/');

// 反馈建议组
Route::group('a-feedback', function () {
    Route::get('/index', 'index');
    Route::get('/info/:id', 'show');
    Route::get('/status/:id/:status', 'status');
})->prefix('admin/feedback/');

// 意向表单组
Route::group('a-intention', function () {
    // 商家
    Route::get('/m-index', 'admin/MerchantEnter/index');
    Route::get('/m-status/:id/:status', 'admin/MerchantEnter/status');
    // 骑手
    Route::get('/r-index', 'admin/RiderRecruit/index');
    Route::get('/r-status/:id/:status', 'admin/RiderRecruit/status');
});

// （骑手）配送管理组
Route::group('a-rider', function () {
    // 骑手组
    Route::get('/index', 'admin/RiderInfo/index');
    Route::get('/info/:id', 'admin/RiderInfo/show');
    Route::get('/status/:id/:status', 'admin/RiderInfo/status');
    // 骑手审核组
    Route::get('/c-index', 'admin/RiderInfo/checkRiderList');
    Route::get('/c-info/:id', 'admin/RiderInfo/checkShow');
    Route::post('/c-status', 'admin/RiderInfo/setCheckStatus');

});

// 用户模块
Route::group('admin',function (){
    Route::rule('login','admin/Login/login');//用户登录
    Route::rule('loginOut','admin/Login/loginOut');//用户退出
    Route::rule('register','admin/Login/register');//用户注册
    Route::rule('verify','admin/Login/verify');//验证码
    Route::rule('index','admin/Admin/index');//后台用户列表
    Route::rule('add','admin/Admin/add');//后台用户新增
    Route::rule('edit','admin/Admin/update');//后台用户新增

});



// 首页模块
Route::group('admin',function (){
    Route::rule('index/info','admin/Index/getUserList');
});

// 会员模块
Route::group('admin',function (){
    Route::rule('user/list','admin/User/getList');//会员列表
    Route::rule('user/detail','admin/User/getDetail');//会员详情
    Route::rule('user/recycle','admin/User/recycle');//回收站
});

// 商家模块
Route::group('admin',function (){
    Route::rule('shop/list','admin/Shop/getList');//商家列表
    Route::rule('shop/detail','admin/Shop/getDetail');//商家详情
    Route::rule('shop/addShop','admin/Shop/addShop');//添加店铺
    Route::rule('shop/addQualification','admin/Shop/addQualification');//添加商家资质
    Route::rule('shop/addAccount','admin/Shop/addAccount');//添加收款信息
    Route::rule('shop/check','admin/Shop/check');//商家审核
});






/*************** 商家端 *********************************************************************************************/
//商家登录注册用户组
Route::group('merchants',function (){
    Route::rule('login','merchants/Login/login');
    Route::rule('login2', 'merchants/Login/login2');
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
	Route::rule('updatePwd','merchants/Merchants/updatePwd');


});





