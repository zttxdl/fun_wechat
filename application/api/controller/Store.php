<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/5/30
 * Time: 4:27 PM
 */
namespace app\api\controller;

use app\common\controller\ApiBase;
use think\Exception;
use think\Request;

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

        $order = [
            'order_sn' => build_order_no(),//订单
            'user_id' => $order['user_id'],
            'shop_id' => $order['shop_id'],
            'money' => $order['money'],//实付金额
            'total_money' => $order['total_money'],//订单总价
            ''

        ];














    }
}