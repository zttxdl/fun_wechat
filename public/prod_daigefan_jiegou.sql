/*
Navicat MySQL Data Transfer

Source Server         : fun_wechat-正式服务器
Source Server Version : 50726
Source Host           : 118.31.111.94:3306
Source Database       : prod_daigefan

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2019-09-06 16:47:25
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for fun_admin
-- ----------------------------
DROP TABLE IF EXISTS `fun_admin`;
CREATE TABLE `fun_admin` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号（登录账号）',
  `password` char(32) NOT NULL COMMENT '登录密码',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2禁用）',
  `last_login_time` int(10) DEFAULT '0' COMMENT '最近登录时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='管理员表';

-- ----------------------------
-- Table structure for fun_advert
-- ----------------------------
DROP TABLE IF EXISTS `fun_advert`;
CREATE TABLE `fun_advert` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键值',
  `advert_id` int(10) NOT NULL DEFAULT '0' COMMENT '广告位表的主键值',
  `advert_name` varchar(50) DEFAULT NULL COMMENT '广告位名称',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '广告名称',
  `coverage` smallint(4) NOT NULL DEFAULT '0' COMMENT '覆盖范围',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '广告指向1：商家广告，2：外链广告，3：静态图',
  `link_url` varchar(200) NOT NULL DEFAULT '' COMMENT '链接',
  `start_time` int(10) NOT NULL DEFAULT '0' COMMENT '广告开始时间',
  `end_time` int(10) NOT NULL DEFAULT '0' COMMENT '广告结束时间',
  `imgurl` varchar(255) NOT NULL DEFAULT '' COMMENT '广告位轮播图',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=暂未投放， 1=投放中, 2=暂停投放，3=已过期',
  `sort` tinyint(1) NOT NULL DEFAULT '1' COMMENT '位置排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='广告详情表';

-- ----------------------------
-- Table structure for fun_advert_position
-- ----------------------------
DROP TABLE IF EXISTS `fun_advert_position`;
CREATE TABLE `fun_advert_position` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '广告位名称',
  `white` varchar(30) NOT NULL DEFAULT '' COMMENT '广告图宽度',
  `height` varchar(30) NOT NULL DEFAULT '' COMMENT '广告图高度',
  `num` tinyint(1) NOT NULL DEFAULT '0' COMMENT '广告位数量',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 正常投放 2禁止投放',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='广告位表';

-- ----------------------------
-- Table structure for fun_agreement
-- ----------------------------
DROP TABLE IF EXISTS `fun_agreement`;
CREATE TABLE `fun_agreement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL DEFAULT '' COMMENT '图文协议标题',
  `content` text NOT NULL COMMENT '内容',
  `platfrom` tinyint(1) NOT NULL DEFAULT '1' COMMENT '位置（1用户端、2商家端、3骑手端）',
  `save_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='图文协议表';

-- ----------------------------
-- Table structure for fun_back
-- ----------------------------
DROP TABLE IF EXISTS `fun_back`;
CREATE TABLE `fun_back` (
  `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '银行名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='银行表';

-- ----------------------------
-- Table structure for fun_canteen
-- ----------------------------
DROP TABLE IF EXISTS `fun_canteen`;
CREATE TABLE `fun_canteen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '所属学校',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '食堂名称',
  `cut_proportion` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '抽成比例（百分制）',
  `account` varchar(30) NOT NULL DEFAULT '' COMMENT '食堂账户',
  `cleartext` varchar(20) NOT NULL DEFAULT '' COMMENT '明文密码',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `withdraw_cycle` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '提现周期',
  `last_login_time` int(11) DEFAULT '0' COMMENT '上次登录时间',
  `can_balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '可提现余额',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='食堂表';

-- ----------------------------
-- Table structure for fun_canteen_account
-- ----------------------------
DROP TABLE IF EXISTS `fun_canteen_account`;
CREATE TABLE `fun_canteen_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `canteen_id` int(10) NOT NULL,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '抬头',
  `back_name` varchar(200) NOT NULL DEFAULT '' COMMENT '开户行',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '开户人姓名',
  `back_num` varchar(30) NOT NULL DEFAULT '' COMMENT '银行卡号',
  PRIMARY KEY (`id`),
  UNIQUE KEY `canteen_id` (`canteen_id`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='食堂开户信息';

-- ----------------------------
-- Table structure for fun_canteen_income_expend
-- ----------------------------
DROP TABLE IF EXISTS `fun_canteen_income_expend`;
CREATE TABLE `fun_canteen_income_expend` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `canteen_id` smallint(4) NOT NULL COMMENT '食堂主键值',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '商家名称 or 提现',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `money` decimal(10,2) NOT NULL COMMENT '每笔收入/提现的金额',
  `type` tinyint(1) NOT NULL COMMENT '状态（1收入 2提现 3退款）',
  `serial_number` varchar(30) NOT NULL COMMENT '收入时：订单编号 支出时：提现编号',
  `add_time` int(10) NOT NULL COMMENT '记录时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（1审核中2审核失败3提现成功）',
  `payment_time` int(10) NOT NULL DEFAULT '0' COMMENT '企业打款给用户的时间',
  `remark` varchar(32) DEFAULT '' COMMENT '审核不通过集合',
  PRIMARY KEY (`id`),
  KEY `rider_income_exp` (`canteen_id`) USING BTREE,
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='食堂收支明细表';

-- ----------------------------
-- Table structure for fun_check_status
-- ----------------------------
DROP TABLE IF EXISTS `fun_check_status`;
CREATE TABLE `fun_check_status` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '状态名称',
  `type` tinyint(1) NOT NULL COMMENT '状态（1 商家审核状态值 2骑手审核状态值）',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='审核状态表';

-- ----------------------------
-- Table structure for fun_feedback
-- ----------------------------
DROP TABLE IF EXISTS `fun_feedback`;
CREATE TABLE `fun_feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户表主键值',
  `content` varchar(200) NOT NULL DEFAULT '' COMMENT '意见反馈内容',
  `imgs` varchar(500) DEFAULT '' COMMENT '意见反馈图片',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（默认1，1未处理，2已处理  3不处理）',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '记录时间',
  PRIMARY KEY (`id`),
  KEY `feedback_user` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='意见反馈表';

-- ----------------------------
-- Table structure for fun_hot_search
-- ----------------------------
DROP TABLE IF EXISTS `fun_hot_search`;
CREATE TABLE `fun_hot_search` (
  `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `keywords` varchar(20) NOT NULL COMMENT '搜索关键字',
  `add_time` int(10) NOT NULL COMMENT '记录时间',
  `sort` tinyint(1) unsigned NOT NULL DEFAULT '10' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='热销搜索';

-- ----------------------------
-- Table structure for fun_income_expenditure
-- ----------------------------
DROP TABLE IF EXISTS `fun_income_expenditure`;
CREATE TABLE `fun_income_expenditure` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` smallint(4) NOT NULL COMMENT '商家表主键值',
  `current_money` decimal(10,2) NOT NULL COMMENT '每笔收入/支出的金额',
  `type` tinyint(1) NOT NULL COMMENT '状态（1收入 2支出）',
  `balance_money` decimal(10,2) NOT NULL COMMENT '目前账户中的余额',
  `serial_number` varchar(30) NOT NULL COMMENT '收入时：订单编号 支出时：提现编号',
  `add_time` int(10) NOT NULL COMMENT '记录时间',
  `remark` varchar(30) DEFAULT '' COMMENT '收支说明',
  PRIMARY KEY (`id`),
  KEY `shop_income_exp` (`shop_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商家收支明细表';

-- ----------------------------
-- Table structure for fun_invitation
-- ----------------------------
DROP TABLE IF EXISTS `fun_invitation`;
CREATE TABLE `fun_invitation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `referee_user_id` int(10) NOT NULL COMMENT '邀请者用户的主键值',
  `lucky_money` decimal(10,2) NOT NULL COMMENT '邀请用户所获得的金额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邀请有奖记录表';

-- ----------------------------
-- Table structure for fun_manage_category
-- ----------------------------
DROP TABLE IF EXISTS `fun_manage_category`;
CREATE TABLE `fun_manage_category` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL DEFAULT '' COMMENT '经营品类名称',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `sort` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0禁言1启用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='经营品类表';

-- ----------------------------
-- Table structure for fun_merchant_enter
-- ----------------------------
DROP TABLE IF EXISTS `fun_merchant_enter`;
CREATE TABLE `fun_merchant_enter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '用户表主键值',
  `school_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '学校编号（开店所在学校）',
  `manage_category_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '经营品类编号（经营品类）',
  `name` varchar(10) NOT NULL DEFAULT '' COMMENT '姓名',
  `phone` bigint(11) NOT NULL DEFAULT '0' COMMENT '手机号',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '记录时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1未处理 2已处理 3不处理',
  PRIMARY KEY (`id`),
  KEY `merchant_enter_user` (`user_id`),
  KEY `merchant_enter_school` (`school_id`),
  KEY `merchant_enter_manage_category` (`manage_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商家入驻【意向表单】';

-- ----------------------------
-- Table structure for fun_my_car
-- ----------------------------
DROP TABLE IF EXISTS `fun_my_car`;
CREATE TABLE `fun_my_car` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '用户表主键值',
  `shop_id` smallint(4) NOT NULL COMMENT '商家表主键值',
  `product_id` int(10) NOT NULL COMMENT '商品主键值',
  `product_classify_id` int(10) NOT NULL COMMENT '商品分类表主键值',
  `num` tinyint(1) unsigned NOT NULL COMMENT '购买商品的数量',
  `attr_one` smallint(4) DEFAULT NULL COMMENT '商品特殊属性1',
  `attr_two` smallint(4) DEFAULT NULL COMMENT '商品特殊属性2',
  `attr_three` smallint(4) DEFAULT NULL COMMENT '商品特殊属性3',
  PRIMARY KEY (`id`),
  KEY `shop_user_mycar` (`shop_id`,`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='购物车表';

-- ----------------------------
-- Table structure for fun_my_coupon
-- ----------------------------
DROP TABLE IF EXISTS `fun_my_coupon`;
CREATE TABLE `fun_my_coupon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '用户主键值',
  `platform_coupon_id` int(10) NOT NULL COMMENT '平台红包主键值',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（默认1，1 未使用 2已使用 3已过期）',
  `add_time` int(10) NOT NULL COMMENT '记录时间',
  `order_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '订单编号',
  `indate` varchar(30) NOT NULL DEFAULT '' COMMENT '有效期',
  `phone` char(30) DEFAULT '' COMMENT '领取人手机号',
  `first_coupon` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0表示普通红包， 1表示首单红包',
  PRIMARY KEY (`id`),
  KEY `user_mycoupon` (`user_id`) USING BTREE,
  KEY `pcoupon_coupon` (`platform_coupon_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='我的红包';

-- ----------------------------
-- Table structure for fun_orders
-- ----------------------------
DROP TABLE IF EXISTS `fun_orders`;
CREATE TABLE `fun_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `orders_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '订单编号',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户表主键值',
  `shop_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '商家表主键值',
  `rider_id` smallint(4) DEFAULT '0' COMMENT '骑手表主键值',
  `address` text CHARACTER SET utf8 NOT NULL COMMENT '收货地址',
  `pay_mode` tinyint(1) DEFAULT '1' COMMENT '支付方式（1微信支付 2支付宝支付）',
  `source` tinyint(1) DEFAULT '1' COMMENT '订单来源（1 小程序 2 h5）',
  `trade_no` varchar(50) CHARACTER SET utf8 DEFAULT '' COMMENT '支付交易单号',
  `money` decimal(10,2) NOT NULL COMMENT '订单实付金额',
  `total_money` decimal(10,2) NOT NULL COMMENT '订单总价',
  `num` smallint(4) unsigned NOT NULL COMMENT '订单商品总数量',
  `active_type` varchar(20) CHARACTER SET utf8 DEFAULT '' COMMENT '活动类型',
  `add_time` int(10) NOT NULL COMMENT '订单创建时间',
  `plan_arrive_time` int(10) DEFAULT NULL COMMENT '骑手预计送达时间',
  `pay_time` int(10) DEFAULT NULL COMMENT '订单支付时间（商家已接单）',
  `cancle_time` int(10) DEFAULT NULL COMMENT '订单取消时间',
  `shop_receive_time` int(10) DEFAULT NULL COMMENT '商家接单（拒单）时间',
  `rider_receive_time` int(10) DEFAULT NULL COMMENT '骑手接单时间',
  `send_time` int(10) DEFAULT NULL COMMENT '骑手开始配送时间',
  `issuing_time` int(10) DEFAULT NULL COMMENT '商家出单时间',
  `trading_closed_time` int(10) DEFAULT NULL COMMENT '交易关闭时间',
  `arrive_time` int(10) DEFAULT NULL COMMENT '骑手订单已送达时间(未评价)',
  `complete_time` int(10) DEFAULT NULL COMMENT '订单完成时间(已评价)',
  `message` varchar(60) CHARACTER SET utf8 DEFAULT '' COMMENT '订单备注',
  `status` tinyint(2) unsigned DEFAULT '1' COMMENT '1:订单待支付;2等待商家接单;3商家已接单;4商家拒绝接单;5骑手取货中;6骑手配送中;7订单已送达;8订单已完成;9订单已取消;10退款中;11退款成功;12退款失败',
  `pay_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未支付 1已支付',
  `ping_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单配送费',
  `box_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单包装费（餐盒费）',
  `shop_discounts_id` int(10) DEFAULT '0' COMMENT '商家优惠券主键值',
  `shop_discounts_money` decimal(10,2) DEFAULT '0.00' COMMENT '商家优惠券折扣金额',
  `platform_coupon_id` int(10) DEFAULT '0' COMMENT '平台优惠券主键值',
  `platform_coupon_money` decimal(10,2) DEFAULT '0.00' COMMENT '平台优惠券折扣金额',
  `issuing_status` int(1) NOT NULL DEFAULT '0' COMMENT '出餐状态: 1 商家已出餐 ; 0 商家未出餐',
  `meal_sn` int(10) unsigned DEFAULT NULL COMMENT '店铺当天的取餐号',
  `platform_choucheng` decimal(10,2) DEFAULT '0.00' COMMENT '平台抽成',
  `shitang_choucheng` decimal(10,2) DEFAULT '0.00' COMMENT '食堂抽成',
  `hongbao_choucheng` decimal(10,2) DEFAULT '0.00' COMMENT '红包抽成',
  PRIMARY KEY (`id`),
  KEY `user_orders` (`user_id`) USING BTREE,
  KEY `shop_orders` (`shop_id`) USING BTREE,
  KEY `rider_orders` (`rider_id`) USING BTREE,
  KEY `orders_sn` (`orders_sn`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

-- ----------------------------
-- Table structure for fun_orders_info
-- ----------------------------
DROP TABLE IF EXISTS `fun_orders_info`;
CREATE TABLE `fun_orders_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orders_id` int(10) NOT NULL COMMENT '订单表主键值',
  `product_id` int(10) NOT NULL COMMENT '商品表主键值',
  `num` tinyint(1) unsigned NOT NULL COMMENT '商品下单数量',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品的订单金额',
  `shop_discounts_id` int(10) DEFAULT NULL COMMENT '商家优惠券主键值',
  `shop_discounts_money` float(10,2) DEFAULT NULL COMMENT '商家优惠券折扣金额',
  `platform_coupon_id` int(10) DEFAULT NULL COMMENT '平台优惠券主键值',
  `platform_coupon_money` float(10,2) DEFAULT '0.00' COMMENT '平台优惠券折扣金额',
  `attr_one` smallint(4) DEFAULT NULL COMMENT '商品特殊属性1',
  `attr_two` smallint(4) DEFAULT NULL COMMENT '商品特殊属性2',
  `attr_three` smallint(4) DEFAULT NULL COMMENT '商品特殊属性3',
  `ping_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品配送费',
  `box_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品包装费（餐盒费）',
  `attr_ids` varchar(20) DEFAULT '' COMMENT '规格属性集合',
  `total_money` decimal(10,2) DEFAULT '0.00' COMMENT '商品售价总价',
  `old_money` decimal(10,2) DEFAULT '0.00' COMMENT '商品原价总价',
  `price` float(10,2) DEFAULT '0.00' COMMENT '商品售价',
  PRIMARY KEY (`id`),
  KEY `orders_orders_info` (`orders_id`) USING BTREE,
  KEY `product_orders_info` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单详情表';

-- ----------------------------
-- Table structure for fun_platform_coupon
-- ----------------------------
DROP TABLE IF EXISTS `fun_platform_coupon`;
CREATE TABLE `fun_platform_coupon` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `batch_id` varchar(20) NOT NULL DEFAULT '' COMMENT '红包批次ID',
  `face_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券面值',
  `threshold` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券门槛',
  `start_time` int(10) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int(10) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `other_time` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '有效期',
  `user_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户类型 \r\n1所有用户 \r\n2普通用户 \r\n3会员用户',
  `coupon_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '红包类型： \r\n1普通红包 \r\n2首单立减',
  `limit_use` varchar(500) NOT NULL DEFAULT '0' COMMENT '限品类',
  `num` smallint(4) NOT NULL COMMENT '发放量',
  `surplus_num` smallint(4) NOT NULL DEFAULT '0' COMMENT '剩余量',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发放方式（1自主领取 2平台发放 3消费赠送 4邀请赠送）',
  `assume_ratio` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '商家承担比例',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '优惠券名称',
  `school_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '学校主键值（覆盖范围）',
  `shop_ids` varchar(255) NOT NULL DEFAULT '0' COMMENT '商家主键值集合（覆盖范围）',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1未发放 2已发放 3暂停发放 4已作废 5已过期）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_id` (`batch_id`),
  KEY `school_pcoupon` (`school_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='平台红包';

-- ----------------------------
-- Table structure for fun_platform_info
-- ----------------------------
DROP TABLE IF EXISTS `fun_platform_info`;
CREATE TABLE `fun_platform_info` (
  `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `customer_service` varchar(20) NOT NULL COMMENT '客服电话',
  `email` varchar(30) NOT NULL COMMENT '邮箱',
  `channel` varchar(20) NOT NULL COMMENT '招商渠道',
  `company_addr` varchar(30) NOT NULL COMMENT '公司地址',
  `content` text NOT NULL COMMENT '关于我们内容',
  `run_send` float(10,2) NOT NULL COMMENT '配送费',
  `shop_constraint_time` tinyint(1) NOT NULL COMMENT '平台约束商家出菜时间',
  `rider_constraint_time` tinyint(1) NOT NULL COMMENT '平台约束骑手送餐时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='平台信息【关于我们】';

-- ----------------------------
-- Table structure for fun_product
-- ----------------------------
DROP TABLE IF EXISTS `fun_product`;
CREATE TABLE `fun_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '商品名称',
  `shop_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '商家主键值',
  `products_classify_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '商品分类主键值',
  `price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
  `old_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
  `thumb` varchar(300) NOT NULL COMMENT '商品封面图',
  `imgs` varchar(900) DEFAULT NULL COMMENT '商品轮播图',
  `info` varchar(200) NOT NULL DEFAULT '' COMMENT '商品详情',
  `box_money` float(10,2) DEFAULT '0.00' COMMENT '餐盒费',
  `sales` smallint(4) NOT NULL DEFAULT '0' COMMENT '月销量',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '设置热销（1普通商品 2热销商品 3优惠）',
  `attr_ids` varchar(100) DEFAULT NULL COMMENT '一级商品规格属性主键值集合',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1上线， 2下线）',
  `delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未删除，1删除',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `shop_product` (`shop_id`) USING BTREE,
  KEY `product_products_classify` (`products_classify_id`)
) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8 COMMENT='商品信息表';

-- ----------------------------
-- Table structure for fun_product_attr_classify
-- ----------------------------
DROP TABLE IF EXISTS `fun_product_attr_classify`;
CREATE TABLE `fun_product_attr_classify` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '商家表主键值',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '属性分类名称',
  `pid` smallint(4) NOT NULL DEFAULT '0' COMMENT '父级编号',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2禁用）',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='商品属性分类';

-- ----------------------------
-- Table structure for fun_product_sales
-- ----------------------------
DROP TABLE IF EXISTS `fun_product_sales`;
CREATE TABLE `fun_product_sales` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` int(10) NOT NULL COMMENT '商家id',
  `product_id` int(10) NOT NULL COMMENT '商品id',
  `num` int(10) NOT NULL COMMENT '数量',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for fun_products_classify
-- ----------------------------
DROP TABLE IF EXISTS `fun_products_classify`;
CREATE TABLE `fun_products_classify` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` smallint(4) NOT NULL COMMENT '商家表主键值',
  `name` varchar(30) NOT NULL COMMENT '商品分类名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2下架）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='商品分类表';

-- ----------------------------
-- Table structure for fun_receiving_addr
-- ----------------------------
DROP TABLE IF EXISTS `fun_receiving_addr`;
CREATE TABLE `fun_receiving_addr` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户表主键值',
  `sex` tinyint(1) NOT NULL DEFAULT '1' COMMENT '性别：  1男   2女',
  `school_id` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '学校表主键值（收货地址）',
  `name` varchar(10) NOT NULL DEFAULT '' COMMENT '联系人',
  `phone` varchar(11) NOT NULL DEFAULT '0' COMMENT '手机号',
  `area_detail` varchar(64) NOT NULL DEFAULT '' COMMENT '具体收货地址（门牌号）',
  `house_number` varchar(20) NOT NULL DEFAULT '' COMMENT '门牌号',
  `longitude` double(10,6) NOT NULL COMMENT '经纬',
  `latitude` double(10,6) NOT NULL COMMENT '维度',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '记录时间',
  PRIMARY KEY (`id`),
  KEY `user_raddr` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='收货地址表';

-- ----------------------------
-- Table structure for fun_refund
-- ----------------------------
DROP TABLE IF EXISTS `fun_refund`;
CREATE TABLE `fun_refund` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orders_id` int(10) NOT NULL DEFAULT '0' COMMENT '订单表主键值',
  `orders_info_ids` varchar(100) NOT NULL DEFAULT '' COMMENT '订单详情表主键值集合',
  `content` varchar(200) NOT NULL DEFAULT '' COMMENT '退款原因',
  `imgs` varchar(500) DEFAULT NULL COMMENT '退款图片',
  `refund_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `total_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '退款申请时间',
  `refund_time` int(10) DEFAULT NULL COMMENT '退款完成时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（1申请退款， 2退款成功， 3退款失败）',
  `num` tinyint(1) NOT NULL DEFAULT '0' COMMENT '退款数量',
  `out_refund_no` varchar(64) NOT NULL DEFAULT '' COMMENT '商户退款单号',
  `transaction_id` varchar(64) NOT NULL DEFAULT '' COMMENT '微信订单号',
  `out_trade_no` varchar(64) NOT NULL DEFAULT '' COMMENT '商户订单号',
  `refund_id` varchar(32) NOT NULL DEFAULT '' COMMENT '微信退款单号',
  `shop_id` int(10) DEFAULT '0' COMMENT '店铺ID',
  `ping_fee` decimal(10,2) DEFAULT '0.00' COMMENT '配送费',
  `user_id` int(10) DEFAULT '0' COMMENT '用户ID',
  PRIMARY KEY (`id`),
  KEY `orders_refund` (`orders_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='退款表';

-- ----------------------------
-- Table structure for fun_rider_comments
-- ----------------------------
DROP TABLE IF EXISTS `fun_rider_comments`;
CREATE TABLE `fun_rider_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orders_id` varchar(50) NOT NULL DEFAULT '0' COMMENT '订单表主键值',
  `rider_id` int(10) NOT NULL DEFAULT '0' COMMENT '骑手表主键值',
  `shop_id` int(10) NOT NULL DEFAULT '0' COMMENT '商家id',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户表主键值',
  `star` tinyint(1) NOT NULL DEFAULT '0' COMMENT '骑手评分；1=差评，3=一般，5=好评',
  `content` varchar(200) DEFAULT NULL COMMENT '骑手评价内容',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1前台展示，2管理平台展示）',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `rider_rcomment` (`rider_id`) USING BTREE,
  KEY `user_rcomment` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='骑手评价表';

-- ----------------------------
-- Table structure for fun_rider_income_expend
-- ----------------------------
DROP TABLE IF EXISTS `fun_rider_income_expend`;
CREATE TABLE `fun_rider_income_expend` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rider_id` smallint(4) NOT NULL COMMENT '骑手表主键值',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '商家名称 or 提现',
  `current_money` decimal(10,2) NOT NULL COMMENT '每笔收入/提现的金额',
  `type` tinyint(1) NOT NULL COMMENT '状态（1收入 2提现）',
  `serial_number` varchar(30) NOT NULL COMMENT '收入时：订单编号 支出时：提现编号',
  `add_time` int(10) NOT NULL COMMENT '记录时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（1审核中2审核失败3提现成功）',
  `payment_time` int(10) NOT NULL DEFAULT '0' COMMENT '企业打款给用户的时间',
  `remark` varchar(32) DEFAULT '' COMMENT '审核不通过集合',
  PRIMARY KEY (`id`),
  KEY `rider_income_exp` (`rider_id`) USING BTREE,
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='骑手收支明细表';

-- ----------------------------
-- Table structure for fun_rider_info
-- ----------------------------
DROP TABLE IF EXISTS `fun_rider_info`;
CREATE TABLE `fun_rider_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL DEFAULT '' COMMENT '骑手姓名',
  `headimgurl` varchar(300) NOT NULL DEFAULT '' COMMENT '头像',
  `sex` tinyint(1) NOT NULL DEFAULT '1' COMMENT '性别 1男 2女',
  `identity_num` varchar(30) NOT NULL DEFAULT '' COMMENT '身份证号',
  `phone` bigint(11) NOT NULL DEFAULT '0' COMMENT '手机号【申请入驻时填写的手机号，用于订单相关方向】',
  `link_tel` bigint(11) NOT NULL DEFAULT '0' COMMENT '联系电话',
  `card_img` varchar(255) NOT NULL DEFAULT '' COMMENT '身份证正面照',
  `back_img` varchar(255) NOT NULL DEFAULT '' COMMENT '身份证反面照',
  `hand_card_img` varchar(255) NOT NULL DEFAULT '' COMMENT '手持身份证照',
  `openid` varchar(100) NOT NULL DEFAULT '' COMMENT '骑手唯一身份编号',
  `school_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '学校表主键值',
  `nickname` varchar(30) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '昵称',
  `remark` varchar(100) NOT NULL DEFAULT '0' COMMENT '审核不通过状态',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（0未激活 1待审核 2未通过 3已通过 4禁用）',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `last_login_time` int(10) NOT NULL DEFAULT '0' COMMENT '最近一次登录时间',
  `open_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1开工  2休息',
  `check_join` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0填写加入表单， 1已填写加入表单【前端用】',
  `pass_time` int(10) DEFAULT '0' COMMENT '审核通过时间',
  `formid` varchar(200) NOT NULL DEFAULT '',
  `overtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COMMENT='骑手信息表';

-- ----------------------------
-- Table structure for fun_rider_recruit
-- ----------------------------
DROP TABLE IF EXISTS `fun_rider_recruit`;
CREATE TABLE `fun_rider_recruit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户主键值',
  `school_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '学校编号',
  `phone` bigint(11) NOT NULL DEFAULT '0' COMMENT '手机号',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '记录时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1未处理 2已处理 3不处理',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='骑手招募【意向表单】';

-- ----------------------------
-- Table structure for fun_rider_withdraw_period
-- ----------------------------
DROP TABLE IF EXISTS `fun_rider_withdraw_period`;
CREATE TABLE `fun_rider_withdraw_period` (
  `id` int(10) NOT NULL,
  `school_id` smallint(4) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '提现等级（1 每天都可提现，2每周固定某一天可提现）',
  `days` tinyint(1) NOT NULL DEFAULT '0' COMMENT '针对每周提现的某一天（1-6，代表周一到周六，0代表周天）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='骑手提现周期细化表【暂时用不到，因骑手金额较小，所以暂定每天都可提现】';

-- ----------------------------
-- Table structure for fun_school
-- ----------------------------
DROP TABLE IF EXISTS `fun_school`;
CREATE TABLE `fun_school` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL COMMENT '学校名称',
  `fid` smallint(4) NOT NULL DEFAULT '0' COMMENT '父级主键值',
  `level` tinyint(1) NOT NULL DEFAULT '2' COMMENT '等级',
  `longitude` varchar(20) NOT NULL DEFAULT '' COMMENT '经度',
  `latitude` varchar(20) NOT NULL DEFAULT '' COMMENT '纬度',
  `completion_time` int(10) NOT NULL DEFAULT '0' COMMENT '订单预估送达时间值',
  `fetch_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '骑手约定取餐时间值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='学校表';

-- ----------------------------
-- Table structure for fun_search
-- ----------------------------
DROP TABLE IF EXISTS `fun_search`;
CREATE TABLE `fun_search` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '用户表主键值',
  `keywords` varchar(20) NOT NULL COMMENT '搜索关键字',
  `add_time` int(10) NOT NULL COMMENT '记录时间',
  PRIMARY KEY (`id`),
  KEY `user_search` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='历史搜索表';

-- ----------------------------
-- Table structure for fun_shop_comments
-- ----------------------------
DROP TABLE IF EXISTS `fun_shop_comments`;
CREATE TABLE `fun_shop_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orders_id` varchar(50) NOT NULL COMMENT '订单表主键值',
  `shop_id` smallint(4) NOT NULL COMMENT '商家表主键值',
  `user_id` int(10) NOT NULL COMMENT '用户表主键值',
  `star` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '店铺星评分（1~5）',
  `content` varchar(200) NOT NULL COMMENT '店铺评价内容',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1前台展示，2管理平台展示）',
  `add_time` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `shop_scomment` (`shop_id`) USING BTREE,
  KEY `user_scomment` (`user_id`),
  KEY `star` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商家评价表';

-- ----------------------------
-- Table structure for fun_shop_comments_tips
-- ----------------------------
DROP TABLE IF EXISTS `fun_shop_comments_tips`;
CREATE TABLE `fun_shop_comments_tips` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comments_id` int(10) NOT NULL COMMENT '评价表主键值',
  `tips_id` tinyint(1) NOT NULL COMMENT '标签表主键值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评价标签与店铺关联表';

-- ----------------------------
-- Table structure for fun_shop_discounts
-- ----------------------------
DROP TABLE IF EXISTS `fun_shop_discounts`;
CREATE TABLE `fun_shop_discounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '商家主键值',
  `face_value` int(10) NOT NULL DEFAULT '0' COMMENT '满减券面值',
  `threshold` int(10) NOT NULL DEFAULT '0' COMMENT '满减券门槛',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未删除 1删除（软删）',
  PRIMARY KEY (`id`),
  KEY `shop_sdiscounts` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺满减券';

-- ----------------------------
-- Table structure for fun_shop_info
-- ----------------------------
DROP TABLE IF EXISTS `fun_shop_info`;
CREATE TABLE `fun_shop_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(20) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '商家名称',
  `logo_img` varchar(255) DEFAULT NULL COMMENT '商家封面图',
  `account` varchar(20) NOT NULL COMMENT '账户',
  `password` char(32) NOT NULL COMMENT '密码 ',
  `info` varchar(500) DEFAULT '待更新。。。' COMMENT '商家信息',
  `up_to_send_money` decimal(10,2) DEFAULT '0.00' COMMENT '起送价格',
  `run_time` varchar(200) DEFAULT '06:00-22:00' COMMENT '配送[营业]时间段',
  `balance` decimal(10,2) DEFAULT '0.00' COMMENT '余额（可提现金额）',
  `address` varchar(50) DEFAULT '' COMMENT '地址',
  `marks` decimal(10,1) DEFAULT '5.0' COMMENT '评分',
  `sales` smallint(4) DEFAULT '0' COMMENT '总销量',
  `notice` varchar(255) DEFAULT '待更新。。。' COMMENT '商家公告',
  `check_status` tinyint(1) DEFAULT '0' COMMENT '0未通过审核， 1已通过审核【前端用】',
  `remark` varchar(50) DEFAULT '' COMMENT '审核不通过的状态值',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态（ 0未激活 1待审核 2未通过 3已通过[启用] 4禁用）',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '记录时间',
  `longitude` double(10,6) DEFAULT NULL COMMENT '经度',
  `latitude` double(10,6) DEFAULT NULL COMMENT '纬度',
  `school_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '学校编号（开店所在学校）',
  `manage_category_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '经营品类编号（经营品类）',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `link_name` varchar(10) NOT NULL DEFAULT '' COMMENT '联系人',
  `link_tel` varchar(11) NOT NULL DEFAULT '' COMMENT '联系电话',
  `ping_fee` decimal(10,2) NOT NULL DEFAULT '2.00' COMMENT '配送价',
  `open_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '1:营业中 0:暂停营业',
  `segmentation` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '平台抽成(百分制)',
  `run_type` varchar(64) NOT NULL DEFAULT '平台配送' COMMENT '配送方式',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `canteen_id` int(10) NOT NULL DEFAULT '0' COMMENT '食堂ID',
  `price_hike` decimal(10,2) NOT NULL DEFAULT '1.00' COMMENT '提价',
  `canteen_open_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '食堂启用营业状态 1:营业中 0:暂停营业',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='商家信息表';

-- ----------------------------
-- Table structure for fun_shop_more_info
-- ----------------------------
DROP TABLE IF EXISTS `fun_shop_more_info`;
CREATE TABLE `fun_shop_more_info` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '商家信息表主键值',
  `business_license` varchar(255) NOT NULL DEFAULT '' COMMENT '营业执照',
  `proprietor` varchar(10) NOT NULL DEFAULT '' COMMENT '经营者',
  `hand_card_front` varchar(255) NOT NULL DEFAULT '' COMMENT '手持身份证正面照',
  `hand_card_back` varchar(255) NOT NULL DEFAULT '' COMMENT '手持身份证反面照',
  `user_name` varchar(10) NOT NULL DEFAULT '' COMMENT '姓名',
  `identity_num` varchar(30) NOT NULL DEFAULT '' COMMENT '身份证号',
  `sex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别（1男 2女）',
  `licence` varchar(255) NOT NULL DEFAULT '' COMMENT '许可证',
  `branch_back` varchar(100) NOT NULL DEFAULT '' COMMENT '银行支行名称',
  `back_hand_name` varchar(10) NOT NULL DEFAULT '' COMMENT '开户人姓名',
  `back_card_num` varchar(30) NOT NULL DEFAULT '' COMMENT '银行卡号',
  `account_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '账户类型（1对公 2对私）',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `shop_smi` (`shop_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='商家更多详情信息';

-- ----------------------------
-- Table structure for fun_takeout
-- ----------------------------
DROP TABLE IF EXISTS `fun_takeout`;
CREATE TABLE `fun_takeout` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '订单id（关联订单表id）',
  `rider_id` int(10) DEFAULT NULL COMMENT '骑手id',
  `shop_id` int(10) NOT NULL DEFAULT '0' COMMENT '商家id',
  `ping_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '配送费',
  `meal_sn` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '取餐号',
  `school_id` int(10) NOT NULL DEFAULT '0' COMMENT '学校id（关联学校的id）',
  `expected_time` int(10) NOT NULL DEFAULT '0' COMMENT '预计送达时间',
  `accomplish_time` int(10) DEFAULT NULL COMMENT '送达时间',
  `single_time` int(10) DEFAULT NULL COMMENT '接单时间',
  `toda_time` int(10) DEFAULT NULL COMMENT '到店时间',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除: 0-否 1-是',
  `shop_address` varchar(255) NOT NULL DEFAULT '' COMMENT '商家地址信息',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1:待接单;2:商家取消订单;3:待取餐;4:取餐中;5:配送中;6:配送完成',
  `user_address` varchar(255) NOT NULL DEFAULT '' COMMENT '用户收货信息',
  `cancel_desc` varchar(50) DEFAULT NULL COMMENT '取消原因',
  `cancel_time` int(10) DEFAULT NULL COMMENT '取消时间',
  `fetch_time` int(10) DEFAULT NULL COMMENT '骑手约定取餐时间值',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_id` (`order_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='外卖表';

-- ----------------------------
-- Table structure for fun_tips
-- ----------------------------
DROP TABLE IF EXISTS `fun_tips`;
CREATE TABLE `fun_tips` (
  `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL COMMENT '标签名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='标签表';

-- ----------------------------
-- Table structure for fun_today_deals
-- ----------------------------
DROP TABLE IF EXISTS `fun_today_deals`;
CREATE TABLE `fun_today_deals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品表主键值',
  `school_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '学校主键值',
  `shop_id` smallint(4) NOT NULL DEFAULT '0' COMMENT '商家表主键值',
  `start_time` int(10) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int(10) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `price` decimal(10,2) NOT NULL COMMENT '今日特惠售价',
  `num` smallint(4) NOT NULL DEFAULT '0' COMMENT '商家限量',
  `limit_buy_num` tinyint(1) NOT NULL DEFAULT '1' COMMENT '限制用户单次购买数量',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1上架 2下架）',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `old_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `today` varchar(50) NOT NULL DEFAULT '' COMMENT '日期',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='今日特价表';

-- ----------------------------
-- Table structure for fun_user
-- ----------------------------
DROP TABLE IF EXISTS `fun_user`;
CREATE TABLE `fun_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(100) NOT NULL COMMENT '用户小程序端唯一身边编号',
  `nickname` varchar(20) CHARACTER SET utf8mb4 NOT NULL COMMENT '微信昵称',
  `headimgurl` varchar(300) NOT NULL DEFAULT '' COMMENT '头像',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别【1男，2女，3保密】',
  `phone` bigint(11) DEFAULT '0' COMMENT '手机号',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '会员类型 1普通会员 ',
  `formid` varchar(100) DEFAULT '' COMMENT '表单提交临时编号（用于推送模板消息）',
  `overtime` int(10) DEFAULT '0' COMMENT '过期时间（针对表单提交临时编号）',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '记录时间',
  `new_buy` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态判断是否为新用户【平台首单减红包】（默认1，1新用户 2老用户）',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态（默认1，1正常 2禁用）',
  `invitation_id` int(11) DEFAULT '0' COMMENT '邀请者的id值',
  `last_login_time` int(10) DEFAULT '0' COMMENT '最近一次登录时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8 COMMENT='用户表';

-- ----------------------------
-- Table structure for fun_visitors
-- ----------------------------
DROP TABLE IF EXISTS `fun_visitors`;
CREATE TABLE `fun_visitors` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ip` varchar(30) CHARACTER SET utf8 DEFAULT '' COMMENT 'ip地址',
  `forms` varchar(100) CHARACTER SET utf8 DEFAULT '' COMMENT '归属地',
  `add_time` varchar(30) CHARACTER SET utf8 DEFAULT '' COMMENT '添加时间',
  `system` varchar(60) CHARACTER SET utf8 DEFAULT '' COMMENT '操作系统',
  `browser` varchar(100) CHARACTER SET utf8 DEFAULT '' COMMENT '浏览器',
  `pageview` varchar(100) CHARACTER SET utf8 DEFAULT '' COMMENT '受访页面',
  `source_link` varchar(100) CHARACTER SET utf8 DEFAULT '' COMMENT '来源链接',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='访客表';

-- ----------------------------
-- Table structure for fun_withdraw
-- ----------------------------
DROP TABLE IF EXISTS `fun_withdraw`;
CREATE TABLE `fun_withdraw` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` smallint(4) unsigned NOT NULL COMMENT '商家表主键值',
  `withdraw_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '提现编号（收入时为订单编号）',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '提现金额(收入时为结算金额)',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（1审核中2审核失败3提现成功）',
  `type` int(1) DEFAULT '1' COMMENT '1收入 2提现 3活动支出 4抽成支出  5推广支出 6退款',
  `title` varchar(32) DEFAULT '' COMMENT '收支说明',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `card` varchar(100) DEFAULT '' COMMENT '银行卡号',
  `remark` varchar(32) DEFAULT '' COMMENT '审核不通过原因(集合)',
  PRIMARY KEY (`id`),
  KEY `shop_withdraw` (`shop_id`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商家提现表';
