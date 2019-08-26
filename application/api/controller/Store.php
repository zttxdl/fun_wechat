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
use think\facade\Cache;
use think\Request;

class Store extends ApiBase
{
    protected $noNeedLogin = [];
    //获取商家菜单
    public function index(Request $request)
    {
        $shop_id = $request->param('shop_id');
        $this->isDisable($shop_id);

        $where[] = ['shop_id','=',$shop_id];
        $where[] = ['status','=',1];
        //获取商家提价
        $price_hike = model('ShopInfo')->getPriceHike($shop_id);

        //获取商品
        $list = model('Product')
            ->field('id,name,box_money,price,info,old_price,attr_ids,thumb,sales,products_classify_id as classId,type')
            ->where($where)
            ->where('status',1)
            ->select()
            ->toArray();

        //获取今日特价
        $today = date('Y-m-d',time());
        $toWhere[] = ['a.today','=',$today];
        $toWhere[] = ['a.shop_id','=',$shop_id];
        $toWhere[] = ['a.end_time','>=',time()];
        $days = Db::name('today_deals')->alias('a')
            ->join('product b','a.product_id = b.id ')
            ->field('b.id,b.name,a.old_price,a.price,a.num,a.limit_buy_num,a.thumb,a.start_time,a.end_time,b.products_classify_id as classId,b.attr_ids,b.box_money,b.sales')
            ->where($toWhere)
            ->find();

        if ($days){

            $list[] = $days;
        }

        foreach ($list as &$item) {
            if (isset($item['attr_ids'])) {
                $attr_list = model('ProductAttrClassify')
                    ->field('id,name')
                    ->where('id','in',$item['attr_ids'])
                    ->select();

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

            $item['sales'] = model('Product')->getMonthSales($item['id']);
            $item['price'] = floatval(sprintf("%.2f",$price_hike + $item['price']));
            $item['old_price'] = floatval(sprintf("%.2f",$price_hike + $item['old_price']));

        }

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
        $this->isDisable($shop_id);
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
            ->distinct(true)
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
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDetail(Request $request)
    {
        $shop_id = $request->param('shop_id');
        $this->isDisable($shop_id);
        $data = model('ShopInfo')
            ->field('shop_name,logo_img,ping_fee,info,up_to_send_money,run_time,address,marks,sales,notice,manage_category_id,school_id,open_status')
            ->where('id',$shop_id)
            ->find()
            ->toArray();
        //获取品类
        $data['ping_fee'] = (float)$data['ping_fee'];
        $data['marks'] = (float)$data['marks'];
        $data['up_to_send_money'] = (float)$data['up_to_send_money'];
        $data['categoryName'] = model('ManageCategory')->where('id',$data['manage_category_id'])->value('name');
        $data['sales'] = model('Shop')->getMonthNum($shop_id);
        //判断店铺是否营业
        if (! empty($data['run_time']) && $data['open_status'] == 1){
            $data['open_status'] = model('ShopInfo')->getBusiness($data['run_time']);
        }else{
            $data['open_status'] = 0;
        }
        

        //判断是否存在优惠
        $data['disc'] = model('ShopDiscounts')
            ->field('id,face_value,threshold')
            ->where('shop_id',$shop_id)
            ->where('delete',0)
            ->order('threshold','asc')
            ->select();
        //判断是否存在首单减
        $new_buy = model('User')->where('id',$this->auth->id)->value('new_buy');
        if ($new_buy == 1){
            // 首单立减红包仅 平台发放这种形式  ，搜索条件如下
            $pt_where = [['status','=',2],['type','=',2],['coupon_type','=',2],['school_id','=',$data['school_id']],['surplus_num','>',0]];
            // 这里需约束下，在红包的有效期内，每个店铺只能参与一种首单立减规格
            $pt_coupon = model('PlatformCoupon')->where($pt_where)->field('face_value,threshold,shop_ids')->select()->toArray();

            if ($pt_coupon){
                foreach ($pt_coupon as $ko => $vo) {
                    $shopids = explode(',',$vo['shop_ids']);
                    if (in_array($shop_id,$shopids)) {
                        $data['single'] = $vo['face_value'];
                        continue;
                    }
                }
            }
        }

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
            ->field('name,shop_id,box_money,sales,price,old_price,thumb,info,type,attr_ids,status,shop_id')
            ->where($where)
            ->find();

        $data = model('TodayDeals')->where('product_id',$product_id)->find();

        if (! $product){
            $this->error('商品已下架');
        }else{
            $product = $product->toArray();

            //获取商家提价
            $price_hike = model('ShopInfo')->getPriceHike($product['shop_id']);
            if ($data){
                $product['old_price'] = floatval(sprintf("%.2f",$data->old_price + $price_hike));
                $product['price'] = floatval(sprintf("%.2f",$data->price + $price_hike));
            }else{
                $product['old_price'] = floatval(sprintf("%.2f",$product['old_price'] + $price_hike));
                $product['price'] = floatval(sprintf("%.2f",$product['price'] + $price_hike));
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

    /**
     * 统计店铺当天的访客量
     */
    public function countUserVistor(Request $request)
    {

        $shop_id = $request->param('shop_id',1);
        $openid = $request->param('openid',1);
        $this->isDisable($shop_id);

        if(empty($shop_id) || empty($openid)) {
            $this->error("必传参数不能为空!");
        }

        $user_id = model('User')->getUidByOpenId($openid);

        $redis = Cache::store('redis');
        $key = "shop_uv_count";

        if($redis->hExists($key,$shop_id)) {
            //获取店铺访客
            $user_vistor = json_decode($redis->hGet($key,$shop_id));
            if(!in_array($user_id, $user_vistor)){
                array_push($user_vistor,$user_id);
            }else{//如果用户已经访问 直接return
                return true;
            }
        }else{
            $user_vistor[] = $user_id;
        }

        $user_vistor = json_encode($user_vistor);

        $redis->hSet($key,$shop_id,$user_vistor);



    }
}