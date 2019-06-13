<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/6/3
 * Time: 9:50 AM
 */
namespace app\api\controller;

use app\common\controller\ApiBase;
use EasyWeChat\Factory;
use think\Db;
use think\facade\Config;
use think\Request;


class Order extends ApiBase
{
    protected $noNeedLogin = [];

    /**
     * 订单列表
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(Request $request)
    {
        $pagesize = $request->param('pagesize',20);
        $page = $request->param('page');
        $user_id = $this->auth->id;

        $data = model('orders')->alias('a')
                ->leftJoin('shopInfo b','a.shop_id = b.id')
                ->leftJoin('ordersInfo c','a.id = c.id')
                ->field(['a.id','a.orders_sn','a.num','FROM_UNIXTIME( a.add_time, "%Y-%m-%d %H:%i" )'=> 'add_time','a.status','a.money','b.link_tel','b.logo_img','b.shop_name','c.product_id'])
                ->where('user_id',$user_id)
                ->page($page,$pagesize)
                ->select();

        if(empty($data)){
            $this->error('你暂时还没有订单，快去挑选吧！');
        }

        $result = [];
        foreach ($data as $row) {
            $product_name = model('Product')->getNameById($row['product_id']);
            $result[] = [
                'id' => $row['id'],
                'orders_sn' => $row['orders_sn'],
                'num' => $row['num'],
                'add_time' => $row['add_time'],
                'status' => config('order_status')[$row['status']],
                //'status' => $row['status'],
                'money' => $row['money'],
                'logo_img' => $row['logo_img'],
                'shop_name' => $row['shop_name'],
                'product_name' => $product_name,
                'shop_tel' => $row['link_tel']
            ];
        }

        $this->success('success',$result);
    }

    /**
     * 订单明细
     * @param Request $request
     */
    public function getDetail(Request $request)
    {
        $orders_id = $request->param('orders_id');

        if(!$orders_id) {
            $this->error('非法传参');
        }

        $result = [];
        $data = Db::name('ordersInfo')->where('orders_id',$orders_id)->select();

        if(!$data) {
            $this->error('暂无数据');
        }

        $result['detail'] = $data;

        foreach ($result['detail'] as &$row) {
            $row['attr_names'] = model('Shop')->getGoodsAttrName($row['attr_ids']);
            $result['platform_discount']['id'] = $row['platform_coupon_id'];
            $result['platform_discount']['face_value'] = $row['platform_coupon_money'];
            $result['shop_discount']['id'] = $row['shop_discounts_id'];
            $result['shop_discount']['face_value'] = $row['shop_discounts_money'];
            unset($row['attr_ids']);
        }

        $orders = Db::name('orders')->alias('a')
            ->leftJoin('rider_info b','a.rider_id = b.id')
            ->field('a.*,b.name,b.link_tel')
            ->where('a.id',$orders_id)
            ->find();

        $result['ping_info'] = [
            'address' => $orders['address'],
            'name' => $orders['name'],
            'link_tel' => $orders['link_tel'],
            'ping_time' => '尽快送达',
            'ping_type' => '平台配送',
        ];

        $result['orders'] = [
            'orders_sn' => $orders['orders_sn'],
            'add_time' => date("Y-m-d H:i",$orders['add_time']),
            'pay_type' => '在现支付',
            'pint_fee' => $orders['ping_fee'],
            'box_money' => $orders['box_money'],
            'money' => $orders['money']
        ];
        if(in_array($orders['status'],[2,5,6])) { //商家接单 和 骑手取货配货显示时间 送达时间
            $result['time'] = $orders['plan_arrive_time'];
        }

        $result['order_status'] = config('order_status')[$orders['status']];

        $this->success('获取成功',$result);
    }



    //订单支付真实
    public function orderPayment(Request $request)
    {
        $orders_sn = $request->param('orders_sn');


        if(!$orders_sn){

            $this->error('订单号不能为空');
        }

        $order = model('Orders')->getOrder($orders_sn);

        if(!$order){
            $this->error('订单id错误');
        }

        if($order->user_id != 1){
            $this->error('非法操作');
        }
        if($order->pay_status==1){
            $this->error('订单已支付');
        }

//        if((time()-$order->add_time) > 15*60){//15分钟失效
//            $this->error('订单已失效');
//        }
        $data['price'] = $order['money'];

        $config = config('wx_pay');
        $app_id = config('wx_pay')['app_id'];
        $app = Factory::payment($config);
        $openid= $this->auth->openid;



        $ip   = request()->ip();
        $result = $app->order->unify([
            'body' => '商品支付',
            'out_trade_no' => $orders_sn,
            'total_fee' => $data['price']*100,
            'spbill_create_ip' => $ip, // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
            'notify_url' => 'https' . "://" . $_SERVER['HTTP_HOST'].'/api/order/wxNotify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI',
            'openid' => $openid,
        ]);
        // print_r($result);
        if($result['return_code'] == "SUCCESS" && $result['result_code']=="SUCCESS"){
            $result['openid']=$openid;
            $result['timeStamp']=strval(time());
            $result['package']="prepay_id=".$result['prepay_id'];
            $result['paySign']=MD5("appId=".$app_id."&nonceStr=".$result['nonce_str']."&package=".$result['package']."&signType=MD5&timeStamp=".$result['timeStamp']."&key=10S9a3A3EdF2a60e04cb1b8G8b507AF4");

            $this->success('success',$result);
        }else{

             $this->error('下单失败'.$result['err_code_des']);
        }

    }


    //微信支付回调
    public function wxNotify(){
        //获取返回的xml
        $xml = file_get_contents("php://input");
        $log = './uploads/'.date('Ymd').'.txt';
        // FILE_APPEND 不写第三个参数默认是覆盖，写的话是追加
        file_put_contents($log,date('Y-m-d H:i:s')."\n".$xml."\n",FILE_APPEND);
        //将xml转化为json格式
        $jsonxml = json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA));
        //转成数组
        $result = json_decode($jsonxml, true);
        file_put_contents($log,date('Y-m-d H:i:s')."\n".print_r($result,1)."\n",FILE_APPEND);
        if($result){
            //如果成功返回了
            if($result['return_code'] == 'SUCCESS' && $result['result_code']=="SUCCESS"){

                $this->returnResult($result['out_trade_no'],$result['transaction_id']);
                echo "success";
            }else{
                echo "fail";
            }
        }else{
            echo "fail";
        }
    }

    //微信支付回调处理业务
    public function returnResult($orders_sn,$wx_id)
    {
        Db::startTrans();
        try {
            $orders = model('orders')->where('orders_sn',$orders_sn)->find();


            //处理的业务逻辑，更新订单
            model('orders')
                ->where('orders_sn',$orders_sn)
                ->update(['status'=>2,'pay_status'=>1,'pay_time'=>time(),'trade_no'=>$wx_id]);






            if($orders['platform_coupon_id']) {
                model('myCoupon')->where('platform_coupon_id',$orders['platform_coupon_id'])
                    ->update(['status'=>'2','order_sn'=>$orders_sn]);
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        return true;
    }

    //评价
    public function addEvaluation(Request $request)
    {
        $orders_id = $request->param('orders_id');
        $shop_id = $request->param('shop_id');
        $star = $request->param('star');
        $content = $request->param('content','');
        $tips_ids = $request->param('tips_ids','');
        $type = $request->param('type');

        $data = [
            'orders_id'=>$orders_id,
            'shop_id'=>$shop_id,
            'star'=>$star,
            'content'=>$content,
            'user_id'=>1,
            'type'=>$type,
            'add_time'=>time(),
        ];
        $ret = model('ShopComments')->where('orders_id',$orders_id)->find();

        if ($ret){
            $this->error('该商品已评价');
        }

        $id = model('ShopComments')->insertGetId($data);

        if ($tips_ids){
            $res = explode(',',$tips_ids);
            $com = [];
            foreach ($res as $v) {
                $com[] = ['comments_id'=>$id,'tips_id'=>$v];
            }

            model('ShopCommentsTips')->insertAll($com);
        }
        //改变商品状态
        model('Orders')->where('id',$orders_id)->update(['status'=>9,'update_time'=>time()]);

        $this->success('success');
    }

    //获取评价标签
    public function getTips()
    {
        $list = model('Tips')->select();

        $this->success('success',$list);
    }

    /**
     * 退款申请
     */
    public function  orderRefund(Request $request)
    {
        $orders_id = $request->param('orders_id');

        $orders_info_ids = $request->param('orders_info_ids');

        $content = $request->param('content');

        $imgs = $request->param('imgs');

        $money = $request->param('money');

        $num = $request->param('num');

        $data = Db::name('refund')->where('orders_id',$orders_id)->find();

        if(is_array($data)){
            $this->error('退单已提交申请,请耐心等待');
        }

        $data = [
            'orders_id' => $orders_id,
            'orders_info_ids' => $orders_info_ids,
            'content' => $content,
            'imgs' => $imgs,
            'money' => $money,
            'num' => $num,
            'status' => '1',
            'add_time' => time(),

        ];

        $res = Db::name('refund')->insert($data);

        if($res) {
            $this->success('售后申请已提交成功,等待商家处理');
        }
    }

    /**
     * 是否首单
     */
    public function is_first_order()
    {
        $uid = $this->auth->id;

        $data  = model('orders')->isFirstOrder($uid);

        if(!$data) {
            return json_success('success',['is_first_order'=> 1]);
        }

        return json_success('success',['is_first_order'=> 0]);


    }

    /**
     * 确认订单，生成订单
     * @param Request $request
     * @return bool
     */
    public function sureOrder(Request $request)
    {

        $order = $request->param('order');//主表
        $detail = $request->param('detail');//明细
        $platform_discount = $request->param('platform_discount');//平台活动
        $shop_discount = $request->param('shop_discount');//店铺活动
        $hongbao_status = 2;//红包已经使用

        /*dump($order);
        dump($detail);
        dump($platform_discount);
        dump($shop_discount);*/


        if(!$order || !$detail || !$platform_discount || !$shop_discount) {
            $this->error('非法传参');
        }

        $orders_sn = build_order_no();//生成唯一订单号


        //启动事务
        Db::startTrans();
        try{
            $orderData = [
                'orders_sn' => $orders_sn,//订单
                'user_id' => $this->auth->id,
                'shop_id' => isset($order['shop_id']) ? $order['shop_id'] : 0,
                'money' => isset($order['money']) ? (float)$order['money'] : 0.00,//实付金额
                'total_money' => isset($order['total_money']) ? (float)$order['total_money'] : 0.00,//订单总价
                'box_money' => isset($order['box_money']) ? (float)$order['box_money'] : 0.00,//订单参盒费
                'ping_fee' => isset($order['ping_fee']) ? (float)$order['ping_fee'] : 0.00,//订单配送费
                'pay_mode' => isset($order['pay_mode']) ? $order['pay_mode'] : 1,//支付方式
                'address' => isset($order['address']) ? $order['address'] : '',//配送地址
                'num' => isset($order['num']) ? $order['num'] : '',//商品总数
                'message' => isset($order['remark']) ? $order['remark'] : '',//订单备注
                'source' => 1,//订单来源
                'add_time' => time(),//订单创建时间
                //店铺优惠信息
                'shop_discounts_id' => isset($shop_discount['id']) ? $shop_discount['id']: 0,
                'shop_discounts_money' => isset($shop_discount['face_value']) ? $shop_discount['face_value'] : 0.00,
                //平台优惠信息
                'platform_coupon_id' => isset($platform_discount['id']) ? $platform_discount['id'] : 0 ,
                'platform_coupon_money' => isset($platform_discount['face_value']) ? $platform_discount['face_value'] : 0.00,
            ];

            $orders_id = model('Orders')->addOrder($orderData);

            if(!$orders_id) {
                throw new \Exception('订单添加失败');
            }

            //更新红包状态
            if($orderData['platform_coupon_money'] > 0){

                $res = Model('MyCoupon')->updateStatus($orderData['platform_coupon_id'],$hongbao_status);
                if(!$res) {
                    throw new \Exception('红包使用失败');
                }
            }


            $detailData = [];
            $total_money = $order['total_money'];//订单总价
            $money = $order['money'];//订单结算金额
            $order_discount = $orderData['shop_discounts_money'] + $orderData['platform_coupon_money'];//订单优惠金额
            $product_total_money = '0.00';//商品总价和
            $product_money = '0.00';//商品结算金额(如果有优惠会把运费和包装费去除计算)

            foreach ($detail as $row) {
                $product_total_money += $row['total_money'];
            }

            if($total_money != ($product_total_money + $orderData['box_money'] + $orderData['ping_fee'])) {
                throw new \Exception('订单总价不正确');
            }


            if($money != ($total_money - $order_discount)) {
                throw new \Exception('订单结算金额不正确');
            }


            foreach ($detail as $row) {

                $product_money = isset($row['total_money']) ? $row['total_money'] : '0.00';

                $product_info = model('Product')->getProductById($row['product_id'])->toArray();
                //dump($product_info);



                if($product_info['type'] == 2 && $row['num'] > 1) {//优惠商品
                    $product_money = $product_info['price'] + ($product_info['old_price'] * ($row['num'] - 1));//优惠商品第二件按原价算
                }

                //如果订单包含 商家或者店铺优惠均摊到 商品结算金额
                if($orderData['shop_discounts_id'] || $orderData['platform_coupon_id']){
                    $product_money = (float)(($product_money/$order['total_money']) * ($money - $order['box_money'] - $order['ping_fee']));
                }
                $detailData[] = [
                    'orders_id' => $orders_id,
                    'orders_sn' => $orders_sn,
                    'product_id' => isset($row['product_id']) ? $row['product_id'] : 0,
                    'attr_ids' => isset($row['attr_ids']) ? $row['attr_ids'] : '',
                    'num' => isset($row['num']) ? $row['num'] : 0,
                    'total_money' => isset($row['total_money']) ? $row['total_money'] : 0.00,
                    'money' => $product_money,//商品结算金额
                    'box_money' => isset($row['box_money']) ? $row['box_money'] : 0.00,
                    'platform_coupon_id' => isset($platform_discount['id']) ? $platform_discount['id'] : 0,
                    'platform_coupon_money' => isset($platform_discount['face_value']) ? (float)$platform_discount['face_value'] : 0.00,
                    'shop_discounts_id' => isset($shop_discount['id']) ? $shop_discount['id'] : 0,
                    'shop_discounts_money' => isset($shop_discount['face_value']) ? (float)$shop_discount['face_value'] : 0.00
                ];

            }

            //订单明细入库
            $res = model('Orders')->addOrderDetail($detailData);

            //dump($res);

            if(!$res) {
                throw new \Exception('明细添加失败');
            }

            Db::commit();
            $result['orders_id'] = $orders_id;
            $result['orders_sn'] = $orders_sn;
            return json_success('提交成功',$result);

        } catch (\Exception $e) {
            Db::rollback();
            return json_error($e->getMessage());
        }

    }

    /**
     * 取消订单
     */
    public function cancelOrder(Request $request)
    {
        $order_sn = $request->param('order_sn');
        $order_status = 11;//已取消
        $hongbao_status = 1;//未使用

        if(isset($order_sn)) {
           $orders_sn = trim($order_sn);
        }

        $order_info = Model('Orders')->getOrder($order_sn);
        try{
            //如果使用红包 状态回滚
            if($order_info['platform_coupon_money'] > 0){
                Model('MyCoupon')->updateStatus($order_info['platform_coupon_id'],$hongbao_status);
            }

            $res = Model('Orders')->cancelOrder($order_sn,$order_status);

            if($res) {
                Db::commit();
                return json_success('订单取消成功');
            }
            return json_error('订单取消失败');

        }catch (\Exception $e) {
            Db::rollback();
            return json_error($e->getMessage());
        }

    }



}