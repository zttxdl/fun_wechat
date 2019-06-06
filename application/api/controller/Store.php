<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/5/30
 * Time: 4:27 PM
 */
namespace app\api\controller;

use app\common\controller\ApiBase;
use think\Db;
use think\Exception;
use think\Request;
use think\Db;

class Store extends ApiBase
{
    protected $noNeedLogin = ['*'];
    //获取商家详情-菜单
    public function index(Request $request)
    {
        $shop_id = $request->param('shop_id');

        $where = ['shop_id'=>$shop_id];
        //获取商品
        $list = model('Product')
            ->field('id,name,price,old_price,attr_ids,thumb,sales,products_classify_id as classId,type')
            ->where($where)
            ->where('status',1)
            ->select()
            ->toArray();
        foreach ($list as &$item) {
            $attr = '';
            if ($item['attr_ids']) {
                $attr_list = model('ProductAttrClassify')
                    ->field('id,name,pid')
                    ->where('id','in',$item['attr_ids'])
                    ->select()
                    ->toArray();
                $attr = $this->getSonCategory($attr_list);

            }
            $item['attr'] = $attr;
        }


        $cakes = [];
        $preferential = [];
        //获取热销商品
        foreach ($list as $item) {
            if ($item['type'] == 1){
                $cakes[] = $item;
            }elseif($item['type'] == 2){
                $preferential[] = $item;
            }
        }
        $data['cakes'] = $cakes;
        $data['preferential'] = $preferential;

        //获取分类
        $class = model('ProductsClassify')
            ->field('id as classId,name as className')
            ->where($where)
            ->select()
            ->toArray();

        foreach ($class as &$item) {
            $item['goods'] = [];

            foreach ($list as $value) {
                if ($item['classId'] == $value['classId']){
                    $item['goods'][] = $value;
                }
            }
        }
        $data['class'] = $class;


        return json_success('success',$data);
    }

    //获取商户评价
    public function getEvaluation(Request $request)
    {
        $shop_id = $request->param('shop_id');
        $page = $request->param('page',1);
        $pagesize = $request->param('pagesize',20);
        $order = $request->param('order');
        $tips_id = $request->param('tips_id');



        $where[] = ['shop_id','=',$shop_id];


        $count = model('ShopComments')->where($where)->count();
        $sum = model('ShopComments')->where($where)->sum('star');

        if ($count != 0){
            $data['star'] = round($sum / $count,2);
        }else{
            $data['star'] = 0;
        }
        //获取评价标签
        $data['tips'] = Db::query("SELECT a.tips_id,a.comments_id,b.`name`,COUNT(a.tips_id) as conuts  FROM fun_shop_comments_tips as a 
LEFT JOIN fun_tips as b  ON a.tips_id = b.id 
LEFT JOIN fun_shop_comments as c ON a.comments_id = c.id WHERE c.shop_id = $shop_id GROUP BY a.tips_id");

        if ($tips_id){
            $where[] = ['c.tips_id','=',$tips_id];
        }

        if ($order){
            $time = time() - 86400*30;
            $where[] = ['a.add_time','>',$time];
        }

        $list = Db::table('fun_shop_comments a ')
            ->join('fun_user b','a.user_id = b.id ')
            ->join('fun_shop_comments_tips c','a.id = c.comments_id')
            ->field('a.id,a.star,a.add_time,a.content,b.headimgurl,b.nickname')
            ->where($where)
            ->order('add_time desc')
            ->page($page,$pagesize)
            ->select();

        foreach ($list as &$value){
            $value['add_time'] = date('Y-m-d',$value['add_time']);
            $value['topis'] = Db::table('fun_shop_comments_tips a')
                ->join('fun_tips b','a.tips_id = b.id')
                ->field('b.name')
                ->where('a.comments_id',$value['id'])
                ->select();
        }

        $data['list']  =$list;

        return json_success('success',$data);


    }


    /**
     * 获取商家详情
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function getDetail(Request $request)
    {
        $shop_id = $request->param('shop_id');

        $data = model('ShopInfo')
            ->field('shop_name,logo_img,info,up_to_send_money,run_time,address,open_time,marks,sales,notice,manage_category_id')
            ->where('id',$shop_id)
            ->find()
            ->toArray();
        //获取品类
        $data['categoryName'] = model('ManageCategory')->where('id',$data['manage_category_id'])->value('name');
        //判断是否存在优惠
        $data['disc'] = model('ShopDiscounts')
            ->field('face_value,threshold')
            ->where('shop_id',$shop_id)
            ->where('delete',0)
            ->select();

        return json_success('success',$data);
    }

    /**
     * 获取商品详情
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function getProduct(Request $request)
    {
        $product_id = $request->param('product_id');

        $where[] = ['id', '=', $product_id];
//        $where[] = ['status', '=', 1];

        $product = model('Product')
            ->field('name,sales,price,old_price,thumb,info,type,attr_ids,status,shop_id')
            ->where($where)
            ->find()
            ->toArray();

        $data = model('TodayDeals')->where('product_id',$product_id)->find();

        if (! $product){
            return json_error('商品已下架');
        }else{
            if ($data){
                $product['old_price'] = $data->old_price;
                $product['price'] = $data->price;
            }
        }

        //判断是否存在属性规格
        $attr = '';
        if (isset($product['attr_ids'])) {
            $data = model('ProductAttrClassify')
                ->field('id,name,pid')
                ->where('id','in',$product['attr_ids'])
                ->select()
                ->toArray();

            $attr = $this->getSonCategory($data);

        }
        $product['attr'] = $attr;

        //判断是否存在优惠
        $product['disc'] = model('ShopDiscounts')
            ->field('face_value,threshold')
            ->where('shop_id',$product['shop_id'])
            ->where('delete',0)
            ->select();
        unset($product['attr_ids']);
        unset($product['shop_id']);

        return json_success('success',$product);

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

        /*dump($order);
        dump($detail);
        dump($platform_discount);
        dump($shop_discount);*/


        if(!$order || !$detail || !$platform_discount || !$shop_discount) {
            return json_error('非法传参');
        }

        $orders_sn = build_order_no();//生成唯一订单号


        //启动事务
        Db::startTrans();
        try{
            $orderData = [
                'orders_sn' => $orders_sn,//订单
                'user_id' => isset($order['user_id']) ? $order['user_id'] : 0,
                'shop_id' => isset($order['shop_id']) ? $order['shop_id'] : 0,
                'money' => isset($order['money']) ? (float)$order['money'] : 0.00,//实付金额
                'total_money' => isset($order['total_money']) ? (float)$order['total_money'] : 0.00,//订单总价
                'box_money' => isset($order['box_money']) ? (float)$order['box_money'] : 0.00,//订单参盒费
                'ping_fee' => isset($order['ping_fee']) ? (float)$order['ping_fee'] : 0.00,//订单配送费
                'pay_mode' => $order['pay_mode'],//支付方式
                'address' => isset($order['address']) ? $order['address'] : '',//配送地址
                'num' => isset($order['num']) ? $order['num'] : '',//商品总数
                'message' => isset($order['remark']) ? $order['remark'] : '',//订单备注
                'source' => 1,//订单来源
                'add_time' => time(),//订单创建时间
            ];

            $orders_id = model('Orders')->addOrder($orderData);

            if(!$orders_id) {
                throw new \Exception('订单添加失败');
            }


            $detailData = [];
            $product_money = 0;//商品单价

            foreach ($detail as $row) {

                $product_money = $row['money'];

                $product_info = model('Product')->geProductById($row['product_id'])->toArray();
                //dump($product_info);

                if($product_info['type'] == 2 && count($row['num']) > 1) {//优惠商品
                    $product_money = $product_info['price'] + ($product_info['old_price'] * ($row['num'] - 1));//优惠商品第二价按原价算
                }

                $detailData[] = [
                    'orders_id' => $orders_id,
                    'orders_sn' => $orders_sn,
                    'product_id' => $row['product_id'],
                    'num' => $row['num'],
                    'money' => $product_money,
                    'box_money' => $row['box_money'],
                    'platform_coupon_id' => isset($platform_discount['id']) ? $platform_discount['id'] : 0,
                    'platform_coupon_money' => isset($platform_discount['face_value']) ? (float)$platform_discount['face_value'] : 0.00,
                    'shop_discounts_id' => isset($shop_discount['id']) ? $shop_discount['id'] : 0,
                    'shop_discounts_money' => isset($shop_discount['face_value']) ? (float)$shop_discount['face_value'] : 0.00
                ];
            }


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

    public function test()
    {
        $data['order'] = [
            'money' => 200,
            'total_money' => 230,
            'pay_mode' => 0,
            'address' => '清河南园北园',
            'num' => '5',
            'remark' => '微麻,微辣',
        ];
        $data['detail'] = [
          [
              'product_id'=>'1',
              'name'=>'1',
              'money'=>'100'
          ],
          [
              'product_id'=>'2',
              'name'=>'2',
              'money'=>'50'
          ],
          [
              'product_id'=>'3',
              'name'=>'3',
              'money'=>'10'
          ]
        ];
        $data['platform_discount'] = [
            'platform_coupon_id' => 1,
            'platform_coupon_money' => 10
        ];
        $data['shop_discounts_money'] = [
            'shop_discounts_id' => 1,
            'shop_discounts_money' => 10,
        ];

        return json($data);
    }
}