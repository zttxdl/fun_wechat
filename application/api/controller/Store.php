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
use think\Request;

class Store extends ApiBase
{
    protected $noNeedLogin = [];
    //获取商家详情-菜单
    public function index(Request $request)
    {
        $shop_id = $request->param('shop_id');

        $where = ['shop_id'=>$shop_id];
        //获取商品
        $list = model('Product')
            ->field('id,name,box_money,price,info,old_price,attr_ids,thumb,sales,products_classify_id as classId,type')
            ->where($where)
            ->where('status',1)
            ->select()
            ->toArray();
        foreach ($list as &$item) {

            if ($item['attr_ids']) {
                $attr_list = model('ProductAttrClassify')
                    ->field('id,name')
                    ->where('id','in',$item['attr_ids'])
                    ->select();
                // dump($attr_list);die;
                foreach ($attr_list as  $v) {
                    $v->son = model('ProductAttrClassify')
                        ->field('id,name')
                        ->where('pid', '=', $v->id)
                        ->select();
                }

                $item['attr'] = $attr_list;
            }else{
                $item['attr'] = '';
            }

        }
    //    dump($list);exit;
        $data['goods'] = $list;
        $cakes = [];
        $preferential = [];
        //获取热销商品
        foreach ($list as $value) {
            if ($value['type'] === 2){
                $cakes[] = $value;
            }elseif($value['type'] == 3){
                $preferential[] = $value;
            }
        }

        $data['cakes'] = $cakes;
        $data['preferential'] = $preferential;

        //获取分类
        $data['class'] = model('ProductsClassify')
            ->field('id as classId,name as className')
            ->where($where)
            ->select();

        $this->success('success',$data);
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

        //获取商家评论评分
        $data['star'] = (float)model('ShopInfo')->where('id',$shop_id)->value( 'marks');
        //获取商家配送评分
        $data['r_star'] = model('RiderComments')->getStar($shop_id);
        //获取评价标签
        $data['tips'] = Db::query("SELECT a.tips_id,a.comments_id,b.`name`,COUNT(a.tips_id) as counts  FROM fun_shop_comments_tips as a 
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

        $this->success('success',$data);


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
            ->field('shop_name,logo_img,ping_fee,info,up_to_send_money,run_time,address,open_time,marks,sales,notice,manage_category_id,school_id')
            ->where('id',$shop_id)
            ->find()
            ->toArray();
        //获取品类
        $data['ping_fee'] = (float)$data['ping_fee'];
        $data['marks'] = (float)$data['marks'];
        $data['up_to_send_money'] = (float)$data['up_to_send_money'];
        $data['categoryName'] = model('ManageCategory')->where('id',$data['manage_category_id'])->value('name');
        //判断店铺是否营业
        if (! empty($data['run_time'])){
            $data['business'] = model('ShopInfo')->getBusiness($data['run_time']);
        }else{
            $data['business'] = 0;
        }

        //判断是否存在优惠
        $data['disc'] = model('ShopDiscounts')
            ->field('id,face_value,threshold')
            ->where('shop_id',$shop_id)
            ->where('delete',0)
            ->select();

        $this->success('success',$data);
    }

    /**
     * 获取商品详情
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getProduct(Request $request)
    {
        $product_id = $request->param('product_id');

        $where[] = ['id', '=', $product_id];
        $where[] = ['status', '=', 1];

        $product = model('Product')
            ->field('name,box_money,sales,price,old_price,thumb,info,type,attr_ids,status,shop_id')
            ->where($where)
            ->find()
            ->toArray();

        $data = model('TodayDeals')->where('product_id',$product_id)->find();

        if (! $product){
            $this->error('商品已下架');
        }else{
            if ($data){
                $product['old_price'] = $data->old_price;
                $product['price'] = $data->price;
            }
        }

        //判断是否存在属性规格
        $attr = '';
        if (isset($product['attr_ids'])) {
            $attr = model('ProductAttrClassify')
                ->field('id,name,pid')
                ->where('id','in',$product['attr_ids'])
                ->select();

            foreach ($attr as  $v) {
                $v->son = model('ProductAttrClassify')
                    ->field('id,name')
                    ->where('pid', '=', $v->id)
                    ->select();
            }

        }
        $product['attr'] = $attr;

        //判断是否存在优惠
        $product['disc'] = model('ShopDiscounts')
            ->field('id,face_value,threshold')
            ->where('shop_id',$product['shop_id'])
            ->where('delete',0)
            ->select();
        unset($product['attr_ids']);
        unset($product['shop_id']);

        $this->success('success',$product);

    }

}