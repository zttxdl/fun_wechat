<?php

namespace app\api\controller;

use app\common\controller\ApiBase;
use think\Request;

class IndexMike extends ApiBase
{

    protected $noNeedLogin = ['*'];

    /**
     * 首页
     */
    public function index(Request $request)
    {
        $lat = $request->param('latitude','');
        $lng = $request->param('longitude','');

        // 调用轮播图
        $data['slide'] = $this->getSlide();
        // 调用分类导航
        $data['channel'] = $this->getChannel();
        
        // 通过经纬度获取最近学校
        $data['current_school'] = model('School')->field("id,name,ROUND(6371 * acos (cos ( radians($lat)) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( $lng) ) + sin ( radians( $lat) ) * sin( radians( latitude ) ) ),1 ) AS distance ")
            ->having('distance < 5')->where('level',2)->order('distance asc')->find();

        $this->success('success',$data);
    }


    /**
     * 获取轮播图
     */
    protected function getSlide()
    {
        $where[] = ['status','=',1];
        $where[] = ['platfrom','=',1];

        $data = model('Advers')->field('id,name,img,link_url')->where($where)->order('sort','asc')->select();
        return $data;
    }


    /**
     * 获取分类导航
     */
    public function getChannel()
    {
        $where[] = ['status','=',1];
        $where[] = ['level','=',1];

        $data = model('ManageCategory') ->field('id,name,img')->where($where)->order('sort','asc')->limit(4)->select();
        return $data;
    }


    /**
     * 获取今日特价
     */
    public function getSpecial(Request $request)
    {
        // 学校主键值
        $school_id = $request->get('school_id');
        // 搜索条件
        $day = date('Y-m-d',time());
        $where[] = ['today','=',$day];
        $where[] = ['status','=',1];
        $where[] = ['school_id','=',$school_id];

        $today_sale = model('TodayDeals')->field('name,shop_id,product_id,old_price,price,num,limit_buy_num,thumb')
            ->where($where)->whereTime('end_time', '>=', time())->limit(4)->select();

        $this->success('success',['today_sale'=>$today_sale]);
    }


    /**
     * 获取推荐商家
     */
    public function getRecommendList(Request $request)
    {
        // 学校主键值
        $school_id = $request->get('school_id');
        // 搜索条件
        $where[] = ['school_id','=',$school_id];
        $where[] = ['status','=',3]; 
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 6;

        // 依据商家排序获取推荐商家
        $shop_list = model('ShopInfo')->where($where)->field('id,shop_name,logo_img,marks,ping_fee,up_to_send_money,open_status as business,run_time')->order('sort','asc')->paginate($pagesize)->each(function ($item, $key) {
            // 判断是否休息中
            if ($item['business'] == 1 && !empty($item['run_time'])) {
                $item['business'] = model('ShopInfo')->getBusiness($item['run_time']);
            } else {
                $item['business'] = 0;
            }

           // 获取优惠券信息
            $item['disc'] = model('ShopDiscounts')->field('face_value,threshold')->where('shop_id',$item['id'])->where('delete',0)->select();
            // 获取月销售额
            $item['sales'] = model('Shop')->getMonthNum($item['id']);
            return $item;
        });

        $this->success('success',['shop_list'=>$shop_list]);
    }


    /**
     * 二级经营品类导航
     */
    public function getNavigation(Request $request)
    {
        // 学校主键值
        $school_id = $request->get('school_id');
        // 一级经营品类导航主键值
        $class_id = $request->get('class_id');
        
        if (!$school_id || !$class_id) {
            $this->error('非法参数');
        }

        // 获取当前一级经营品类下的所有二级经营品类
        $class_ids = model('ManageCategory')->where('fid','=',$class_id)->column('id');

        /********* 搜索条件 ***************************************************************/
        $where[] = ['school_id','=',$school_id];
        $where[] = ['manage_category_id','in',$class_ids];

        // // 判断是否有搜索控件
        // if (!empty($request->get('name/s'))) {
        //     // 查询商家表，获取商家主键值集合
        //     $shop_shop_ids = model('ShopInfo')->where(['shop_name','like',$request->get('name/s').'%'])->column('id');
        //     // 查询商品表，获取商家主键值集合
        //     $product_shop_ids = model('Product')->where(['name','like',$request->get('name/s').'%'])->distinct(true)->column('shop_id');
        //     // 去重商家主键值
        //     $shop_ids = array_unique(array_merge($shop_shop_ids,$product_shop_ids));
        //     $where[] = ['id','in',$shop_ids];
        // }
        !empty($request->get('shop_name/s')) ?  : null;
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 6;
        
        /********* 依据商家排序、搜索条件，获取二级经营品类的商家信息 ********************************/
        $shop_list = model('ShopInfo')->where($where)->field('id,shop_name,logo_img,marks,ping_fee,up_to_send_money,open_status as business,run_time')->order('sort','asc')->paginate($pagesize)->each(function ($item, $key) {
            // 判断是否休息中
            if ($item['business'] == 1 && !empty($item['run_time'])) {
                $item['business'] = model('ShopInfo')->getBusiness($item['run_time']);
            } else {
                $item['business'] = 0;
            }

           // 获取优惠券信息
            $item['disc'] = model('ShopDiscounts')->field('face_value,threshold')->where('shop_id',$item['id'])->where('delete',0)->select();
            // 获取月销售额
            $item['sales'] = model('Shop')->getMonthNum($item['id']);
            return $item;
        });

        $this->success('success',['shop_list'=>$shop_list]);
    }


    /**
     * 获取更多今日特价
     * 
     */
    public function getSpecialList(Request $request)
    {
        // 学校主键值
        $school_id = $request->get('school_id');
        // 搜索条件
        $day = date('Y-m-d',time());
        $where[] = ['t.today','=',$day];
        $where[] = ['t.status','=',1];
        $where[] = ['t.school_id','=',$school_id];
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 6;

        $today_sale = model('TodayDeals')->alias('t')->join('shop_info s','t.shop_id = s.id')->field('t.name,t.shop_id,t.product_id,t.old_price,t.price,t.num,t.limit_buy_num,t.thumb,s.shop_name,s.up_to_send_money,s.ping_fee')
            ->where($where)->whereTime('t.end_time', '>=', time())->paginate($pagesize);

        $this->success('success',['today_sale'=>$today_sale]);
    }
     
}
