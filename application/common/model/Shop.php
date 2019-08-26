<?php


namespace app\common\model;


use think\facade\Cache;
use think\Model;
use think\Db;

class Shop extends Model
{
    protected $table = 'fun_shop_info';
    private $shop_id;
    /**
     * 获取已经审核的商家店铺
     * @param $page_no
     * @param $page_size
     * @param $school_id
     * @return mixed
     */
    public function getShopList($page_no,$page_size,$school_id)
    {
        $data = $this->where('status',3)
            ->where('school_id',$school_id)
            ->order('id','desc')
            ->page($page_no,$page_size)
            ->select();

        return $data;

    }
    

    /**
     * 获取指定商家信息
     * @param $shop_id
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopInfo($shop_id)
    {
        $data = $this->where('id',$shop_id)
            ->select();

        return $data;
    }


    /**
     * 获取店铺在售商品
     */
    public function getShopStock($shop_id)
    {
        $data = DB::name('product')->where('shop_id',$shop_id)->count('id');
        return $data;
    }


    /**
     * 获取店铺销售总额
     */
    public function getCountSales($shop_id)
    {
        //1:订单待支付;2等待商家接单;3商家已接单;4商家拒绝接单;5骑手取货中;6骑手配送中;7订单已送达;8订单已完成;9订单已取消;10退款中;11退款成功;12退款失败
        $total_moeny = Db::name('orders')
            ->where('status','notin',[1,4,9,10,11])
            ->where('shop_id',$shop_id)
            ->sum('money');

        $total_ping = Db::name('orders')
            ->where('status','notin',[1,4,9,10,11])
            ->where('shop_id',$shop_id)
            ->sum('ping_fee');
        $data = $total_moeny - $total_ping;

        return sprintf("%.2f",$data);

    }


    /**
     * 获取店铺月销售额
     */
    public function getMonthSales($shop_id)
    {
        $total_moeny = Db::name('orders')
            ->where('status','notin',[1,4,9,10,11])
            ->where('shop_id',$shop_id)
            ->whereTime('add_time', 'month')
            ->sum('money');

        $total_ping = Db::name('orders')
            ->where('status','notin',[1,4,9,10,11])
            ->where('shop_id',$shop_id)
            ->whereTime('add_time', 'month')
            ->sum('ping_fee');
        $data = $total_moeny - $total_ping;
        return sprintf("%.2f",$data);
    }

    /**
     * 获取店铺日销售总额
     */
    public function getDaySales($shop_id)
    {
        $total_moeny = Db::name('orders')
            ->where('status','notin',[1,4,9,10,11])
            ->where('shop_id',$shop_id)
            ->whereTime('add_time', 'today')
            ->sum('money');

        $total_ping = Db::name('orders')
            ->where('status','notin',[1,4,9,10,11])
            ->where('shop_id',$shop_id)
            ->whereTime('add_time', 'today')
            ->sum('ping_fee');
        $data = $total_moeny - $total_ping;
        return sprintf("%.2f",$data);

    }



    /**
     * 获取店铺总订单量
     */
    public function getOrderNum($shop_id)
    {
        $data = Db::name('orders')
            ->where('shop_id',$shop_id)
            ->where('status','notin',[1,4,9])
            ->count('id');

        return $data;
    }

    /**
     * 获取店铺月订单量
     */
    public function getMonthNum($shop_id)
    {
        $data = Db::name('orders')
            ->where('status','notin',[1,4,9])
            ->where('shop_id',$shop_id)
            ->whereTime('add_time', 'month')
            ->count('id');

        return $data;
    }


    /**
     * 获取店铺日订单量
     */
    public function getDayNum($shop_id)
    {
        $data = Db::name('orders')
            ->where('status','notin',[1,4,9])
            ->where('shop_id',$shop_id)
            ->whereTime('add_time', 'today')
//            ->fetchSql('true')
            ->count('id');

        return $data;
    }

    /**
     * 获取店铺日取消订单量
     */
    public function getDayCancelNum($shop_id)
    {
        $data = Db::name('orders')
            ->where('status','=',9)
            ->where('shop_id',$shop_id)
            ->whereTime('add_time', 'today')
//            ->fetchSql('true')
            ->count('id');

        return $data;
    }

    /**
     * 获取店铺结算金额
     */
    public function getSettlelMoney($shop_id)
    {
        $data = Db::name('withdraw')
            ->where('type','2')//支出
            ->where('status','3')//审核成功
            ->where('shop_id',$shop_id)
            ->sum('money');

        $data = sprintf("%.2f",abs($data));

        return $data;
    }

    /**
     * 获取店铺待结算金额
     */
    public function getNoSettlelMoney($shop_id)
    {
        $data = Db::name('withdraw')
            ->where('type','2')//支出
            ->where('status','2')//待审核
            ->where('shop_id',$shop_id)
            ->sum('money');

        $data = sprintf("%.2f",abs($data));

        return $data;
    }

    /**
     * 获取结算
     */
    public function getSettle($shop_id)
    {
        $total_num = $this->getOrderNum($shop_id);//总订单量
        $month_order_num = $this->getMonthNum($shop_id);//月订单量
        $day_order_num = $this->getDayNum($shop_id);//日均订单量
        $settlement_money = model('Withdraw')->getAcountMoney($shop_id); //结算金额
        $settlement_wait_money =  model('Withdraw')->getNotJsMoney($shop_id); //待结算金额
        $month_money = $this->getMonthSales($shop_id);//月销售额
        $total_money = $this->getCountSales($shop_id);//总销售额

        return [
            'total_num' => $total_num,
            'month_order_num' => $month_order_num,
            'day_order_num' => $day_order_num,
            'settlement_money' => $settlement_money,
            'settlement_wait_money' => $settlement_wait_money,
            'month_money' => $month_money,
            'total_money' => $total_money,
        ];


    }



    /**
     * 获取店铺明细
     * @param $shop_id
     */
    public function getShopDetail($shop_id)
    {
        $data = $this->field('shop_name,logo_img,link_name,link_tel,manage_category_id,school_id,address,latitude,longitude')
            ->where('id',$shop_id)->find();
        return $data;
    }

    /**
     * 获取店铺补充信息
     */
    public function getInformation($shop_id)
    {
        $data = $this->field('sort,segmentation,price_hike')->where('id',$shop_id)->find();
        return $data;

    }

    /**
     * 获取店铺更多信息
     * @param $shop_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopMoreInfo($shop_id)
    {
        $data = Db::name('shop_more_info')->where('shop_id',$shop_id)->select();
        return $data;
    }

    /**
     * 获取商家资质
     */
    public function getShopQualification($shop_id)
    {
        $data = Db::name('shop_more_info')
            ->where('shop_id',$shop_id)
            ->field('business_license,proprietor,hand_card_front,user_name,identity_num,sex,licence')
            ->find();
        return $data;
    }


    /**
     * 获取收款信息
     * @param $shop_id
     */
    public function getAccount($shop_id)
    {
        $data = Db::name('shop_more_info')
            ->where('shop_id',$shop_id)
            ->field('branch_back,back_hand_name,back_card_num')
            ->find();
        return $data;
    }

    /**
     * 获取店铺在售商品
     * @param $shop_id
     */
    public function getIsOnlineGoods($shop_id)
    {
        $data = DB::name('product')
            ->alias('a')
            ->join('products_classify b','a.products_classify_id = b.id')
            ->where('a.shop_id',$shop_id)
            ->where('a.status',1)
            ->field('a.id,a.name,a.attr_ids,b.name as class_name,a.price')
            ->select();
        return $data;
    }

    /**
     * 获取一级商品属性名称集合
     * @param string $attr
     * @return array|mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function getGoodsAttrName($attr_ids = '')
    {
        $res = Db::name('product_attr_classify')->field('name')->whereIn('id',$attr_ids)->select();
        $res = array_column($res,'name');
        $attr_names = implode(",",$res);
        return $attr_names;
    }

    /**
     * 获取二级商品属性名称集合
     * @param string $attr
     * @return array|mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function getGoodsAttrSonName($attr_ids = '')
    {
        $res = Db::name('product_attr_classify')->field('name')->whereIn('pid',$attr_ids)->select();
        $res = array_column($res,'name');
        $attr_names = implode(",",$res);
        return $attr_names;
    }



    /**
     * 商家排序更新
     * @param $map
     * @return bool|int
     */
    public function sortEdit($map)
    {
        $res = $this->where('id',$map['shop_id'])->setField('sort',$map['sort']);

        return isset($res) ? $res :false ;
    }


    /**
     * 获取当前学校下的已审核通过的商家列表【不分页】 
     * 
     */
    public function getCurSchShopList($where)
    {
        $data = $this->where('status',3)->where($where)->select()->toArray();

        return $data;
    }

    /**
     * 获取学校ID
     */
    public function getSchoolIdByID($shop_id){
        $school_id = $this->where('id',$shop_id)->value('school_id');
        return $school_id;
    }

    /**
     * 获取店铺日访问用户
     */
    public function getShopVistor($shop_id)
    {
        $redis = Cache::store('redis');
        $day_uv = $redis->hGet('shop_uv_count',$shop_id);

        if(empty($day_uv)) {
           return  0;
        }

        return count(json_decode($day_uv));
    }




}