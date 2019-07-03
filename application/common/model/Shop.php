<?php


namespace app\common\model;


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
     * 获取店铺月销售额
     */
    public function getMonthSales($shop_id)
    {
        $start_time = date('Y-m-01',strtotime(date('Y-m-d H:i:s')));

        $end_time =  strtotime("$start_time +1 month -1 day");

        $start_time = strtotime($start_time);

//        dump($start_time);
//        dump($end_time);

        $data = Db::name('orders')->where('status',8)
            ->where('shop_id',$shop_id)
            ->whereBetweenTime('add_time',$start_time,$end_time)
            ->sum('money');

        return $data;

    }


    /**

    /**
     * 获取店铺销售总额
     */
    public function getCountSales($shop_id)
    {
        $data = Db::name('orders')->where('status','in',[8])
            ->where('shop_id',$shop_id)
            ->sum('money');

        return sprintf("%.2f",$data);

    }



    /**
     * 获取店铺总订单量
     */
    public function getOrderNum($shop_id)
    {
        $data = Db::name('orders')
            ->where('shop_id',$shop_id)
            ->where('status','notin',[1])
            ->count('id');

        return $data;
    }

    /**
     * 获取店铺月订单量
     */
    public function getMonthNum($shop_id)
    {
        $data = Db::name('orders')
            ->where('status','notin',[1])
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
            ->where('status','notin',[1])
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

        $data = abs($data);

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

        $data = abs($data);

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
        $settlement_money = $this->getSettlelMoney($shop_id);//结算金额
        $settlement_wait_money = $this->getNoSettlelMoney($shop_id);//待结算金额
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
        $data = $this->field('shop_name,logo_img,link_name,link_tel,manage_category_id,school_id,address')
            ->where('id',$shop_id)->find();
        return $data;
    }

    /**
     * 获取店铺补充信息
     */
    public function getInformation($shop_id)
    {
        $data = $this->field('sort,segmentation')->where('id',$shop_id)->find();
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





}