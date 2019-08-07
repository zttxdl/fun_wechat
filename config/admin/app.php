<?php

return [
    // 优惠券的状态
    'coupon_status' => [
        '1' => '未发放',        
        '2' => '已发放',
        '3' => '暂停发放',
        '4' => '已作废',
        '5' => '已过期',
    ],

    // 优惠券的发放类型
    'coupon_type' => [
        '1' => '自主领取',        
        '2' => '平台发放',
        '3' => '消费赠送',
        '4' => '邀请赠送',
    ],

    // 优惠券的用户类型
    'user_type'   =>  [
        '1' => '所有用户',
        '2' => '普通用户',
        '3' => '会员用户'
    ],

    // 广告的展示平台
    'show_platfrom'   =>  [
        '1' => '用户端',
        '2' => '商家端',
        '3' => '骑手端',
    ],

    // 广告的状态
    'advers_status'   =>  [
        '1' => '启用',
        '2' => '禁用',
    ],

    // 反馈建议的状态【反馈意见、意向商家、意向骑手】
    'dispose_status'   =>  [
        '1' => '未处理',
        '2' => '已处理',
        '3' => '不处理',
    ],

    // 骑手审核状态 
    'rider_check_status'    =>  [
        '0' =>  '全部',
        '1' =>  '待审核',
        '2' =>  '未通过',
        '3' =>  '已通过',
    ],

    // 商家审核状态
    'shop_check_status' => [
        '0' => '未激活',
        '1' => '待审核',
        '2' => '未通过',
        '3' => '已通过',
        '4' => '禁用',

    ],

    // 订单状态
    'order_status'  =>  [
        '1'     =>  '订单待支付',
        '2'     =>  '等待商家接单',
        '3'     =>  '商家已接单',
        '4'     =>  '商家拒绝接单',
        '5'     =>  '骑手取货中',
        '6'     =>  '骑手配送中',
        '7'     =>  '商家出单',
        '8'     =>  '订单已送达 ',
        '9'     =>  '订单已完成',
        '10'     =>  '交易关闭',
        '11'     =>  '订单已取消',
    ],

    // 骑手订单状态 1:待接单;2:商家取消订单;3:待取餐;4:取餐中;5:配送中;6:配送完成
    'rider_order_status'=>  [
        '3'     =>  '骑手取餐中',
        '4'     =>  '骑手已到店',
        '5'     =>  '骑手配送中',
        '6'     =>  '已送达',
    ],
    
    //分页参数设置
    'page_size' => 20,

    //验证码设置
    'captcha' => [
        // 验证码字体大小
        'fontSize' => 30,
        //验证码位数
        'length' => 4,
    ],
    //性别
    'sex' => [
        1 => '男',
        0 => '女',
        2 => '保密'
],

];