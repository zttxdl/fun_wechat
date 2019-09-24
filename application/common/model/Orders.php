<?php

namespace app\common\model;

use think\Model;
use think\Db;

class Orders extends Model
{
    // 设置json类型字段
    protected $json = ['address'];
    //
    /**
     * 获取会员累计消费金额、次数
     * @param $uid
     */
    public function getUserConsume($uid)
    {
        $data = model('Orders')->where([['user_id','=',$uid],['status','notin','1']])->field('SUM(money) as total_money,count(id) as count_num')->find();
        return $data;
    }


    /**
     * 新增订单
     */
    public function addOrder($data)
    {
        return $this->name('orders')->insertGetId($data);
    }

    /**
     * 取消订单
     */
    public function cancelOrder($order_sn,$status)
    {
        return $this->name('orders')->where('orders_sn',$order_sn)->setField(['status'=>$status,'cancle_time'=>time()]);
    }

    /**
     * 获取订单
     */
    public function getOrderById($order_id)
    {
        return $this->where('id',$order_id)->find();
    }

    /**
     * 订单详情
     */
    public function getOrderDetail($order_id)
    {
        $data = $this->name('orders_info')->where('orders_id',$order_id)->select()->toArray();
        return $data;
    }

    /**
     * 获取订单
     */
    public function getOrder($order_sn)
    {
        return $this->where('orders_sn',$order_sn)->find();
    }



    /**
     * 获取订单编号
     */
    public function getOrderSnById($order_id)
    {
        return $this->where('id',$order_id)->value('orders_sn');
    }

    /**
     * 添加订单详情
     */
    public function addOrderDetail($data)
    {
        return $this->name('orders_info')->insertAll($data);
    }

    /**
     * 获取订单列表
     * @param $page_no
     * @param $page_size
     * @return mixed
     */
    public function getOrderList($page_no, $page_size)
    {
        return $this->page($page_no,$page_size)->select()->toArray();
    }

    /**
     * 用户是否首单
     * @param $uid
     * @return bool
     */
    public function isFirstOrder($map)
    {
        return $this->where($map)->value('new_buy');
    }

    /**
     * 更新订单状态
     * @param $order_sn
     * @param $status
     * @return int
     */
    public function updateStatus($order_sn,$status)
    {
        return $this->where('orders_sn',$order_sn)->setField('status',$status);
    }

    /**
     * 获取订单total_money
     * @param $product_id
     */
    public function getTotalMoney($order,$detail)
    {
        $goods_total_money = 0.00;
        $goods_money = 0.00;
        //获取商家提价
        $shop_info = model('ShopInfo')->where('id','=',$order['shop_id'])->field('price_hike,hike_type,ping_fee')->find();

        //今日特价
        $today_id = model('TodayDeals')->getTodayProduct($order['shop_id']);

        foreach ($detail as $item)
        {
            $price = Db::name('product')->where('id',$item['product_id'])->value('price');
            $old_price = Db::name('product')->where('id',$item['product_id'])->value('old_price');
            if ($shop_info['hike_type'] == 1) {
                $price = floatval(sprintf("%.2f",$shop_info['price_hike'] + $price));
                $old_price = floatval(sprintf("%.2f",$shop_info['price_hike'] + $old_price));
            } else {
                $price = floatval(sprintf("%.2f",$price * (1 + $shop_info['price_hike'] * 0.01)));
                $old_price = floatval(sprintf("%.2f",$old_price * (1 + $shop_info['price_hike'] * 0.01)));
            }

            //今日特价第二件按原价算
            if($today_id && $item['num'] > 1) {
                $goods_money = $price + ($old_price * ($item['num'] - 1));
            }else{
                $goods_money = $price * $item['num'];
            }

            $product_info = model('Product')->getProductById($item['product_id'])->toArray();

            //优惠商品第二件按原价算
            if($product_info['type'] == 3 && $item['num'] > 1) {
                $goods_money = $price + ($old_price * ($item['num'] - 1));//优惠商品第二件按原价算
            }else{
                $goods_money = $price * $item['num'];
            }

            $goods_total_money += $goods_money;


        }
        //订单总价 = 商品总价 + 配送费
        $total_money = $goods_total_money + $shop_info['ping_fee'];

        return $total_money;
    }

    /**
     *获取平台优惠金额
     */
    public function getPlatformDisCountMoney($id)
    {
        $data = Db::name('platformCoupon')->where('id',$id)->value('face_value');
        return isset($data) ? $data : 0.00;
    }

    /**
     * 获取商家优惠金额
     */
    public function getShopDisCountMoney($id)
    {
        $data = Db::name('shopDiscounts')->where('id',$id)->value('face_value');
        return isset($data) ? $data : 0.00;
    }

    /**
     * 获取订单优惠金额
     */
    public function getDisMoney($shop_dis,$plat_dis)
    {
        if($shop_dis['id']) {
            $shop_dis_money = $this->getShopDisCountMoney($shop_dis['id']);
        }else{
            $shop_dis_money = 0.00;
        }

        if($plat_dis['id']) {
            $plat_dis_money = $this->getPlatformDisCountMoney($plat_dis['id']);
        }else{
            $plat_dis_money = 0.00;
        }

        $dis_money = $shop_dis_money + $plat_dis_money;
        return $dis_money;

    }


}
