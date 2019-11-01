<?php


namespace app\admin\controller;


use app\common\controller\Base;
use think\Request;
use think\Db;

class Orders extends Base
{
    /**
     * 订单列表
     * @param Request $request
     */
    public function getList(Request $request)
    {
        $page_size = $request->param('pageSize',10);
        $search = $request->param('keyword');//搜索条件
        $status = $request->param('status');//订单状态
        $school_id = $request->param('school_id/d');//学校ID

        $map = [];
        if($search) {
            $map[] = ['a.orders_sn|b.nickname|b.phone|c.shop_name','like','%'.$search.'%'];
        }

        if($status) {
            $map[] = ['a.status','=',$status];
        }

        if($school_id) {
            $map[] = ['c.school_id','=',$school_id];
        }

        // 学校列表
        $school_list = Model('school')->getSchoolList();

        $order_list = Db::name('orders')->alias('a')
            ->leftJoin('user b','a.user_id = b.id')
            ->leftJoin('shop_info c','a.shop_id = c.id')
            ->where($map)
            ->field('a.id as id,a.orders_sn,b.nickname,b.phone,c.shop_name,a.money,a.add_time,a.status,a.pay_mode,a.source,a.platform_choucheng,a.meal_sn')
            ->order('id','DESC')
            ->paginate($page_size)->toArray();

        $result = [];

        if($order_list) {
            foreach ($order_list['data'] as $row)
            {
                $shop_money = model('Withdraw')->getMoneyByOrderSn($row['orders_sn']);//商家实际收入 = 商家收支明细表money字段
                    
                $platform_choucheng = sprintf('%.2f',$row['money'] - $shop_money - $row['ping_fee'] - $row['shitang_choucheng']);//平台抽成
                
                $result['info'][] = [
                    'id' => $row['id'],
                    'orders_sn' => $row['orders_sn'],
                    'user_name' => $row['nickname'],
                    'phone' => $row['phone'],
                    'shop_name' => $row['shop_name'],
                    'money' => $row['money'],
                    'add_time' => date('Y-m-d H:i:s',$row['add_time']),
                    'status' => $this->getOrdertStatus($row['status']),
                    'pay_mode' => $row['pay_mode']==1 ? '微信支付' : '支付宝支付',
                    'source' => $row['source']==1 ? '小程序' : 'H5',
                    'platform_choucheng' => $platform_choucheng,
                    'meal_sn' => isset($row['meal_sn']) ? '#'.$row['meal_sn'] : ''
                ];
            }
        }



        $result['count'] = $order_list['total'];
        $result['page'] = $order_list['current_page'];
        $result['pageSize'] = $order_list['per_page'];
        $result['school_list'] = $school_list;
        $this->success('获取成功',$result);

    }

    /**
     * 获取订单状态
     * @param $status
     * @return mixed
     */
    public function getOrdertStatus($status)
    {
        $order_status = [
            '1'     =>  '订单待支付',
            '2'     =>  '等待商家接单',
            '3'     =>  '商家已接单',
            '4'     =>  '商家拒绝接单',
            '5'     =>  '骑手取货中',
            '6'     =>  '骑手配送中',
            '7'     =>  '订单已送达 ',
            '8'     =>  '订单已完成',
            '9'     =>  '订单已取消',
            '10'     =>  '退款中',
            '11'     =>  '退款成功',
            '12'     =>  '退款失败',
        ];
        return $order_status[$status];
    }

    /**
     * 获取订单详情
     * @param Request $request
     */
    public function getDetail(Request $request)
    {
        $order_id = $request->param('id','');
        if(empty($order_id)) {
            $this->error('非法传参');
        }

        $result = [];
        $list = Db::name('orders')->alias('a')
            ->leftJoin('user b','a.user_id = b.id')
            ->leftJoin('shop_info c','a.shop_id = c.id')
            ->leftJoin('rider_info d', 'a.rider_id = d.id')
            ->where('a.id',$order_id)
            ->field('a.orders_sn,
                            a.add_time,
                            a.pay_time,
                            a.pay_mode,
                            a.trade_no,
                            a.shop_discounts_id,
                            a.platform_coupon_id,
                            a.total_money,
                            a.ping_fee,
                            a.shop_discounts_money,
                            a.platform_coupon_money,
                            a.money,
                            a.status,
                            a.box_money,
                            a.num,
                            a.message,
                            a.meal_sn,
                            b.headimgurl,
                            b.nickname,
                            b.type,
                            b.phone,
                            c.logo_img,
                            c.shop_name,
                            c.link_tel,
                            c.link_name,
                            c.school_id,
                            d.headimgurl as rider_img,
                            d.phone as rider_phone,
                            d.name,
                            a.platform_choucheng,
                            a.shitang_choucheng,
                            a.hongbao_choucheng
                            '
            )->find();

        // 获取商家实际收入
        $shop_income_money = model('Withdraw')->getMoneyByOrderSn($row['orders_sn']);//商家实际收入 = 商家收支明细表money字段
        //获取平台收入
        $platform_choucheng = sprintf('%.2f',$list['money'] - $shop_income_money - $list['ping_fee'] - $list['shitang_choucheng']);

        //订单信息 
        $result['order_info'] = [
            'orders_sn' => $list['orders_sn'],
            'add_time' => date('Y-m-d H:i:s',$list['add_time']),
            'pay_time' => date('Y-m-d H:i:s',$list['pay_time']),
            'pay_mode' => $list['pay_mode'] == 1 ? '微信支付' : '其他支付',
            'trade_no' => $list['trade_no'],
            'pro_type' => $this->getPromotionType($list['shop_discounts_id'],$list['platform_coupon_id']),
            'total_money' => $list['total_money'],
            'ping_fee' => $list['ping_fee'],
            'shop_discount_money' => $list['shop_discounts_money'],
            'coupon_money' => $list['platform_coupon_money'],
            'money' => $list['money'],
            'status' => $this->getOrdertStatus($list['status']),
            'num' => $list['num'],
            'box_money' => $list['box_money'],
            'remark' => $list['message'],
            'platform_choucheng' => $platform_choucheng,
            'shitang_choucheng' => $list['shitang_choucheng'],
            'hongbao_choucheng' => $list['hongbao_choucheng'],
            'shop_income_money' => $shop_income_money,
            'meal_sn' => isset($list['meal_sn']) ? '#'.$list['meal_sn'] : ''
        ];

        //会员信息
        $result['user_info'] = [
            'headimgurl' => $list['headimgurl'],
            'nickname' => $list['nickname'],
            'type' => $list['type']==1 ? '普通会员' : '',
            'phone' => $list['phone'],
        ];

        if(!in_array($list['status'],[1,2])) {
            //商家信息
            $result['shop_info'] = [
                'logo_img' => $list['logo_img'],
                'shop_name' => $list['shop_name'],
                'link_tel' => $list['link_tel'],
                'link_name' => $list['link_name'],
                'school_name' => model('School')->getNameById($list['school_id']),
            ];
        }else{
            $result['shop_info'] = [];
        }

        if(in_array($list['status'],[5,6,7,8,10,11,12])){
            //骑手信息
            $result['rider_info'] = [
                'rider_img' => $list['rider_img'],
                'link_tel' => $list['rider_phone'],
                'name' => $list['name'],
            ];
        }else{
            $result['rider_info'] = [];
        }

        //商品信息
        $goods_list  = Db::name('orders_info')
            ->alias('a')
            ->leftJoin('product b','a.product_id = b.id')
            ->where('a.orders_id',$order_id)
            ->field('a.id,b.name,a.num,b.products_classify_id,a.attr_ids,b.price')
            ->select();

        //dump($goods_list);

        foreach ($goods_list as $row)
        {
            $result['goods_info'][] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'num' => $row['num'],
                'class_name' => model('ProductsClassify')->where('id',$row['products_classify_id'])->value('name'),
                'attr_name' => model('Shop')->getGoodsAttrName($row['attr_ids']),
                'price' => $row['price'],
            ];
        }

        $this->success('success',$result);
    }

    /**
     * 获取活动类型
     */

    public function getPromotionType($sid,$pid)
    {
        $pro_name = [];
        if($sid) {
            $pro_name['shop_dis_name'] = '商家优惠';
        }

        if($pid) {
            $pro_name['pla_dis_name'] = '平台优惠';
        }

        return implode('/',$pro_name);
    }

}
