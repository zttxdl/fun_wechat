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
header("Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type, Accept, api-token");
header('Access-Control-Allow-Methods: POST,GET,PUT');
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
    Route::get('/c-show', 'admin/RiderInfo/checkStatusList');
    Route::post('/c-status', 'admin/RiderInfo/setCheckStatus');

})->middleware('IsLogin');

//系统模块
Route::group('admin',function (){
    Route::rule('login','admin/Login/login');//用户登录
    Route::rule('loginOut','admin/Login/loginOut');//用户退出
    Route::rule('register','admin/Login/register');//用户注册
    Route::rule('verify','admin/Login/verify');//验证码
    Route::rule('clearCache','admin/Login/clear_all');//缓存清理
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


// 企业打款给用户
Route::group('transfer',function (){
    Route::rule('send-money','admin/Transfer/sendMoney');
    Route::rule('pass','admin/Transfer/riderTxCheckPass');
    Route::rule('list','admin/Transfer/riderTxList');
    Route::rule('nopass','admin/Transfer/riderTxCheckNopass');
})->middleware('IsLogin');

// 会员模块
Route::group('admin',function (){
    Route::rule('userList','admin/User/getList');//会员列表
    Route::rule('set_user_status','admin/User/setStatus');//会员状态
    Route::rule('userDetail','admin/User/getDetail');//会员详情
    Route::rule('userRecycle','admin/User/recycle');//回收站
})->middleware('IsLogin');

// 商家模块
Route::group('admin',function (){
    Route::rule('shopList','admin/Shop/getList');//商家列表
    Route::rule('shopDetail','admin/Shop/getDetail');//商家详情
    Route::rule('shopAddShop','admin/Shop/addShop');//添加店铺
    Route::rule('shopAddQualification','admin/Shop/addQualification');//添加商家资质
    Route::rule('shopSetStatus','admin/Shop/setStatus');//启用禁用商家
    Route::rule('shopAddAccount','admin/Shop/addAccount');//添加收款信息
    Route::rule('shopCheckList','admin/Shop/checkList');//商家审核列表
    Route::rule('shopCheckDetail','admin/Shop/checkDetail');//商家审核详情
    Route::rule('shopCheckShow','admin/Shop/checkShow');//商家审核展示
    Route::rule('shopCheckStatus','admin/Shop/checkStatus');//商家审核状态
    Route::rule('shopSortInfo','admin/Shop/SortInfo');//展示商家排序列表
    Route::rule('shopSort','admin/Shop/Sort');//商家排序
})->middleware('IsLogin');

// 订单模块
Route::group('admin',function (){
    Route::rule('orderList','admin/Orders/getList');//订单列表
    Route::rule('orderDetail','admin/Orders/getDetail');//订单详情
    Route::rule('orderDetail','admin/Orders/getList');//退单列表
    Route::rule('orderDetail','admin/Orders/getDetail');//退单详情
});

// 图片上传接口
Route::group('a-upload', function () {
    Route::post('/upload', 'upload');
})->prefix('admin/upload/');




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
    Route::get('check_status', 'merchants/Merchants/checkStatus');
    Route::get('set_status', 'merchants/Merchants/setCheckStatus');
	//文件上传
	Route::rule('upload','merchants/Upload/up');



});

//店铺管理
Route::group('merchants',function (){
    Route::rule('shopIndex','merchants/Shop/index');//店铺管理
    Route::rule('shopSetName','merchants/Shop/setName');//修改店铺名称
    Route::rule('shopSetLogo','merchants/Shop/setLogo');//修改店铺Logo
    Route::rule('shopSetStatus','merchants/Shop/setOpenStatus');//修改店铺营业状态
    //商家信息
    Route::rule('shopInfo','merchants/Shop/info');//商家信息
    Route::rule('shopSetInfo','merchants/Shop/setInfo');//设置商家信息
    Route::rule('shopMoreInfo','merchants/Shop/moreInfo');//入驻信息
    Route::rule('checkStatus','merchants/Shop/checkStatus');//检查审核状态
    //我的资产
    Route::post('propertyIndex','merchants/Property/myIndex');//列表
    Route::post('propertyDetail','merchants/Property/receiptPay');//明细
    Route::post('propertyWithdraw','merchants/Property/withdraw');//提现
    //设置
    Route::rule('updatePwd','merchants/Shop/updatePwd');//忘记密码
    Route::rule('loginOut','merchants/Shop/loginOut');//退出
    Route::rule('ShopInfo','merchants/Shop/ShopInfo');//关于我们
    //评价
    Route::rule('getEvaluation','merchants/Merchants/getEvaluation');
});

//订单
Route::group('merchants',function (){
    Route::post('orderQuery','merchants/Order/query');//订单管理
    Route::post('orderDetail','merchants/Order/orderDetail');//订单详情
    Route::post('orderDel','merchants/Order/del');//订单删除
    Route::post('orderAccept','merchants/Order/accept');//商家接单
    Route::post('orderRefuse','merchants/Order/refuse');//商家拒单
    Route::post('refund','merchants/refund/refund');//退款
    Route::post('refuse','merchants/refund/refuse');//拒绝退款
    Route::post('wxRefund','merchants/refund/wxRefund');//微信退款
    Route::post('refundQuery','merchants/refund/refundQuery');//查询退款
    Route::post('orderIndex','merchants/Order/index');//订单管理

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
    Route::get('/index', 'index');
    Route::post('/update-tel', 'setUserPhone');
    Route::post('/bind-tel', 'BindUserPhone');
})->prefix('api/Member/');

// 首页
Route::group('u-index', function () {
    Route::rule('/index', 'index');
    Route::rule('/special', 'getSpecial');
    Route::rule('/recommend', 'getRecommendList');
    Route::rule('/navigation', 'getNavigation');
    Route::rule('/special-list', 'getSpecialList');
})->prefix('api/Index/');

// 红包组
Route::group('u-coupon', function () {
    Route::get('/index', 'index');
    Route::get('/mycoupon', 'myCoupon');
    Route::get('/myorder_coupon', 'myOrderCoupon');
    Route::get('/get_coupon', 'getCoupon');
    Route::get('/show_coupon', 'showCoupon');
    Route::get('/judge_coupon', 'judgeActiveCoupon');

})->prefix('api/MyCoupon/');


// 收货地址组
Route::group('u-addr', function () {
    Route::rule('/index', 'index');
    Route::post('/create', 'create');
    Route::get('/edit/:id', 'edit');
    Route::post('/update', 'update');
    Route::get('/del/:id', 'delete');
})->prefix('api/ReceivingAddr/');

// 学校地区组
Route::group('u-school', function () {
    Route::get('/index', 'index');
    Route::get('/school-level2', 'schoolLevel2');
    Route::get('/choose-school', 'chooseSchool');
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
})->prefix('api/feedback/');

// 入驻招募
Route::group('u-intention', function () {
    // 商家入驻
    Route::get('/m-index', 'api/MerchantEnter/index');
    Route::post('/m-create', 'api/MerchantEnter/create');
    // 骑手招募
    Route::post('/r-create', 'api/RiderRecruit/create');

});

// 邀请有奖
Route::group('u-invitation', function () {
    Route::get('/invitation', 'index');
})->prefix('api/invitation/');

// 图片上传接口
Route::group('u-upload', function () {
    Route::post('/upload', 'upload');
})->prefix('api/upload/');


//订单
Route::group('api',function () {
    //提交订单
    Route::post('shopAddOrder','api/Order/sureOrder');
    //取消订单
    Route::post('cancelOrder','api/Order/cancelOrder');
    //订单列表
    Route::post('getOrderList','api/Order/getList');
    //订单详情
    Route::post('getOrderDetail','api/Order/getDetail');
    //退款
    Route::post('orderRefund','api/Order/orderRefund');
    //微信支付
    Route::post('orderPayment','api/Order/OrderPayment');
    //小程序支付
    Route::post('orderPay','api/Order/OrderPay');
    //支付查询
    Route::post('orderQuery','api/Order/orderQuery');
    //再来一单
    Route::post('againOrder','api/Order/againOrder');
    //获取订单骑手信息
    Route::post('getRiderInfo','api/Order/getRiderInfo');
    //获取订单商家信息
    Route::post('getShopInfo','api/Order/getShopInfo');
    // 判断用户是否是禁用状态
    Route::get('checkUserStatus','api/Order/checkUserDisabled');
});





/*************** 骑手端 *********************************************************************************************/
// 登录注册授权组
Route::group('r-login', function () {
    Route::get('/get-auth', 'getAuthInfo');
    Route::post('/base-create', 'saveRiderBaseInfo');
    Route::post('/check-tel', 'checkRiderPhone');
    Route::post('/send-veriyf', 'getVerify');
    Route::post('/login', 'login');
    Route::post('/celerity-login', 'celerityLogin');
})->prefix('rider/Login/');


// 骑手中心组
Route::group('r-member', function () {
    Route::get('/index', 'index');
    Route::get('/check_status', 'checkStatus');
    Route::get('/set_status', 'setCheckStatus');
    Route::post('/update-tel', 'setRiderPhone');
    Route::post('/apply', 'applyRider');
    Route::get('/edit', 'edit');
    Route::post('/update', 'update');
    Route::post('/bind-tel', 'BindRiderPhone');
    Route::get('/status', 'openStatus');
    Route::get('/check_identity_status', 'checkIdentityStatus');
    Route::post('/tojoin', 'toJoin');
    Route::rule('/getEvaluation', 'getEvaluation');
})->prefix('rider/Member/');

// 骑手订单组
Route::group('r-orders', function () {
    Route::rule('/index', 'index');
    Route::rule('/details', 'details');
    Route::rule('/grabSingle', 'grabSingle');
    Route::rule('/statusUpdate', 'statusUpdate');

})->prefix('rider/Orders/');

// 我的钱包租
Route::group('r-inc-exp', function () {
    Route::get('/mywallet', 'myWallet');
    Route::get('/detail', 'detail');
    Route::post('/withdraw', 'withdraw'); // 因前期每天都可提现， 所以暂不走中间件（每周二提现）
    // Route::post('/withdraw', 'withdraw')->middleware('WithdrawalAuth'); 
})->prefix('rider/IncomeExpend/');


// 图片上传接口
Route::group('r-upload', function () {
    Route::post('/upload', 'upload');
})->prefix('rider/Upload/');



/*************** 定时脚本接口 *********************************************************************************************/

// 用户端
Route::group('auto', function () {
    Route::rule('/zero_execute', 'zeroExecute');
    Route::rule('/cancel_orders', 'cancelOrders');
})->prefix('api/AutoShell/');


// 测试
//Route::group('test', function () {
    Route::rule('add', 'index/Test/add');
    Route::rule('get', 'index/Test/get');
    Route::rule('del', 'index/Test/del');
    Route::rule('edit', 'index/Test/edit');
//});
