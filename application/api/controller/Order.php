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
        //dump($request->param());
        //$this->param_empty($request->param());
        $page_size = config('page_size');
        $page_no = $request->param('page_no');
        $user_id = $this->auth->id;
//        $user_id = $request->param('user_id');

        if(!$user_id || !$page_no) {
            $this->error('非法传参');
        }


        $data = model('orders')->alias('a')
                ->leftJoin('shopInfo b','a.shop_id = b.id')
                ->leftJoin('ordersInfo c','a.id = c.id')
                ->field(['a.id','a.orders_sn','a.num','FROM_UNIXTIME( a.add_time, "%Y-%m-%d %H:%i" )'=> 'add_time','a.status','a.money','b.link_tel','b.logo_img','b.shop_name','c.product_id'])
                ->where('user_id',$user_id)
                ->page($page_no,$page_size)
                ->select();

        if(empty($data)){
            $this->error('暂无数据');
        }

        //dump($data);
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

        $this->success('获取成功',$result);
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

        foreach ($result['detail'] as $row) {
            $result['platform_discount']['id'] = $row['platform_coupon_id'];
            $result['platform_discount']['face_value'] = $row['platform_coupon_money'];
            $result['shop_discount']['id'] = $row['shop_discounts_id'];
            $result['shop_discount']['face_value'] = $row['shop_discounts_money'];
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

        $order = model('Orders')->where('orders_sn',$orders_sn)->find();

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
        $data['price'] = $order->money;

        $config = config('wx_pay');
        $app_id = config('wx_pay')['app_id'];
        $app = Factory::payment($config);
//        $openid = model('user')->where('id',$order['user_id'])->value('openid');
        $openid= 'o2pfj5FQLRFUf3O-aLtXKsVekMfo';


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




            //更新库存



            //更新红包信息
            if($orders['platform_coupon_id']) {
                model('myCoupon')->where('platform_coupon_id',$orders['platform_coupon_id'])
                    ->update(['status'=>'2','order_sn'=>$orders_sn]);
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            $this->error($e->getMessage());
            //$this->error($e->getMessage());
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



}