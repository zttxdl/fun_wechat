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

//解决跨域问题
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: POST,GET');
if(request()->isOptions()){
    exit();
}

/*************** 管理平台端 *********************************************************************************************/
// 广告组
Route::group('a-advers', function () {
    Route::get('/index', 'index');
    Route::get('/edit/:id', 'edit');
    Route::post('/update', 'update');
    Route::get('/del/:id', 'delete');
})->prefix('admin/advers/')->middleware('IsLogin');


// 优惠券组
Route::group('a-coupon', function () {
    Route::get('/index', 'index');
    Route::get('/info/:id', 'show');
    Route::get('/add', 'add');
    Route::post('/create', 'create');
    Route::get('/edit/:id', 'edit');
    Route::post('/update', 'update');
    Route::get('/status/:id/:status', 'status');
    Route::get('/shop-list/:id', 'getSchoolShop');
})->prefix('admin/coupon/')->middleware('IsLogin');

// 图文协议组
Route::group('a-agreement', function () {
    Route::get('/index', 'index');
    Route::get('/info/:id', 'show');
    Route::get('/edit/:id', 'edit');
    Route::post('/update', 'update');
})->prefix('admin/agreement/')->middleware('IsLogin');

// 反馈建议组
Route::group('a-feedback', function () {
    Route::get('/index', 'index');
    Route::get('/info/:id', 'show');
    Route::get('/status/:id/:status', 'status');
})->prefix('admin/feedback/')->middleware('IsLogin');

// 意向表单组
Route::group('a-intention', function () {
    // 商家
    Route::get('/m-index', 'admin/MerchantEnter/index');
    Route::get('/m-status/:id/:status', 'admin/MerchantEnter/status');
    // 骑手
    Route::get('/r-index', 'admin/RiderRecruit/index');
    Route::get('/r-status/:id/:status', 'admin/RiderRecruit/status');
})->middleware('IsLogin');

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

})->middleware('IsLogin');

//系统模块
Route::group('admin',function (){
    Route::rule('login','admin/Login/login');//用户登录
    Route::rule('loginOut','admin/Login/loginOut');//用户退出
    Route::rule('register','admin/Login/register');//用户注册
    Route::rule('verify','admin/Login/verify');//验证码
});

//权限模块
Route::group('admin',function (){
    Route::rule('index','admin/Admin/index');//后台用户列表
    Route::rule('add','admin/Admin/add');//后台用户新增
    Route::rule('edit','admin/Admin/update');//后台用户新增
})->middleware('IsLogin');



// 首页模块
Route::group('admin',function (){
    Route::rule('indexInfo','admin/Index/getUserList');
});

// 会员模块
Route::group('admin',function (){
    Route::rule('userList','admin/User/getList');//会员列表
    Route::rule('userDetail','admin/User/getDetail');//会员详情
    Route::rule('userRecycle','admin/User/recycle');//回收站
})->middleware('IsLogin');

// 商家模块
Route::group('admin',function (){
    Route::rule('shopList','admin/Shop/getList');//商家列表
    Route::rule('shopDetail','admin/Shop/getDetail2');//商家详情
    Route::rule('shopAddShop','admin/Shop/addShop');//添加店铺
    Route::rule('shopAddQualification','admin/Shop/addQualification');//添加商家资质
    Route::rule('shopAddAccount','admin/Shop/addAccount');//添加收款信息
    Route::rule('shopCheckList','admin/Shop/checkList');//商家审核列表
    Route::rule('shopCheckDetail','admin/Shop/checkDetail');//商家审核详情
    Route::rule('shopCheckShow','admin/Shop/checkShow');//商家审核展示
    Route::rule('shopCheckStatus','admin/Shop/checkStatus');//商家审核状态
    Route::rule('shopSortInfo','admin/Shop/SortInfo');//商家排序列表
    Route::rule('shopSort','admin/Shop/Sort');//商家排序
})->middleware('IsLogin');

// 订单模块
Route::group('admin',function (){
    Route::rule('orderList','admin/Orders/getList');//订单列表
    Route::rule('orderDetail','admin/Orders/getDetail');//订单详情
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

//店铺管理
Route::group('merchants',function (){
    Route::get('shopIndex','merchants/Shop/index');
    Route::get('shopSetName','merchants/Shop/setName');//修改店铺名称
    Route::get('shopSetLogo','merchants/Shop/setLogo');//修改店铺Logo
    Route::get('shopInfo','merchants/Shop/info');//商家信息
    Route::get('shopSetInfo','merchants/Shop/setInfo');//设置商家信息
    Route::get('shopMoreInfo','merchants/Shop/moreInfo');//入驻信息
});



/*************** 用户端 *********************************************************************************************/

// 登录注册授权组
Route::group('u-login', function () {
    Route::get('/get-auth', 'getAuthInfo');
    Route::post('/base-create', 'saveUserBaseInfo');
    Route::post('/check-tel', 'checkUserPhone');
    Route::post('/send-veriyf', 'getVerify');
    Route::post('/login', 'login');
    Route::post('/celerity-login', 'celerityLogin');
})->prefix('api/login/');

// 用户中心
Route::group('u-member', function () {
    Route::get('/index/:uid', 'index');
    Route::post('/update-tel', 'setUserPhone');
})->prefix('api/Member/')->middleware('UserAuth');

// 红包组
Route::group('u-coupon', function () {
    Route::get('/index/:uid', 'index');
})->prefix('api/MyCoupon/')->middleware('UserAuth');


// 收货地址组
Route::group('u-addr', function () {
    Route::get('/index/:uid', 'index');
    Route::post('/create', 'create');
    Route::get('/edit/:id', 'edit');
    Route::post('/update', 'update');
    Route::get('/del/:id', 'delete');
})->prefix('api/ReceivingAddr/')->middleware('UserAuth');

// 学校地区组
Route::group('u-school', function () {
    Route::get('/index', 'index');
})->prefix('api/school/');

// 经营品类组
Route::group('u-manage', function () {
    Route::get('/index', 'index');
})->prefix('api/ManageCategory/');

// 图文协议组
Route::group('u-agreement', function () {
    Route::get('/index/:id', 'index');
})->prefix('api/agreement/');

// 意见反馈组
Route::group('u-feedback', function () {
    Route::post('/create', 'create');
})->prefix('api/feedback/')->middleware('UserAuth');

// 入驻招募
Route::group('u-intention', function () {
    // 商家入驻
    Route::get('/m-index', 'api/MerchantEnter/index');
    Route::post('/m-create', 'api/MerchantEnter/create');
    // 骑手招募
    Route::post('/r-create', 'api/RiderRecruit/create');

})->middleware('UserAuth');

//


