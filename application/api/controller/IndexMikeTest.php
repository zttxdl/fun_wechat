<?php

namespace app\api\controller;

use app\common\controller\ApiBase;
use think\Request;

class IndexMikeTest extends ApiBase
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
     * @param $school_id  学校主键值
     * @param $openid  用户openid值
     */
    public function getRecommendList(Request $request)
    {
        // 学校主键值
        $school_id = $request->get('school_id');
        // 判断是否传入openid值，如果传入， 判断当前的openid值，如果有值且能在用户表中找到，并且该用户为首单用户，则判断当前学校是否有首单立减红包， 有则展示，没有则不展示
        $openid = $request->get('openid','0');
        $uid = model('User')->where([['openid','=',$openid],['new_buy','=',1]])->value('id');

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
            $item['disc'] = model('ShopDiscounts')->field('face_value,threshold')->where('shop_id',$item['id'])->where('delete',0)->order('id','desc')->select();
            // 获取月销售额
            $item['sales'] = model('Shop')->getMonthNum($item['id']);
            return $item;
        });

        if ($uid) {
            // 首单立减红包仅 平台发放这种形式  ，搜索条件如下
            $pt_where = [['status','=',2],['type','=',2],['coupon_type','=',2],['school_id','=',$school_id],['surplus_num','>',0]];
            $pt_coupon = model('PlatformCoupon')->where($pt_where)->field('face_value,threshold,shop_ids')->select();  // 这里需约束下，在红包的有效期内，每个店铺只能参与一种首单立减规格
        }
        // 组装店铺满减信息
        foreach ($shop_list as $k => $v) {
            $temp = [];
            foreach ($v['disc'] as $kk => $vv) {
                $temp[] = $vv['threshold'].'减'.$vv['face_value'];
            }
            unset($v['disc']);
            $v['discounts'] = $temp;
        }

        // 组装首单立减信息
        if (!$pt_coupon->isEmpty()) {
            foreach ($shop_list as $k => $v) {
                $temp = $v['discounts'];
                foreach ($pt_coupon as $ko => $vo) {
                    if (in_array($v['id'],explode(',',$vo['shop_ids']))) {
                        $temp[] =  '首单减'.$vo['face_value'];
                        continue;
                    }
                }
                $v['discounts'] = $temp;
            }
        }

        $this->success('success',['shop_list'=>$shop_list]);
    }


    /**
     * 二级经营品类导航
     * @param $school_id  学校主键值
     * @param $class_id  一级经营品类导航主键值
     * @param $openid  用户openid值
     */
    public function getNavigation(Request $request)
    {
        // 学校主键值
        $school_id = $request->get('school_id');
        // 一级经营品类导航主键值
        $class_id = $request->get('class_id');

        // 判断是否传入openid值，如果传入， 判断当前的openid值，如果有值且能在用户表中找到，并且该用户为首单用户，则判断当前学校是否有首单立减红包， 有则展示，没有则不展示
        $openid = $request->get('openid','0');
        $uid = model('User')->where([['openid','=',$openid],['new_buy','=',1]])->value('id');
        
        if (!$school_id || !$class_id) {
            $this->error('非法参数');
        }

        // 获取当前一级经营品类下的所有二级经营品类
        $class_ids = model('ManageCategory')->where('fid','=',$class_id)->column('id');

        /********* 搜索条件 ***************************************************************/
        $where[] = ['school_id','=',$school_id];
        $where[] = ['manage_category_id','in',$class_ids];
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

        if ($uid) {
            // 首单立减红包仅平台发放这种形式  ，搜索条件如下
            $pt_where = [['status','=',2],['type','=',2],['coupon_type','=',2],['school_id','=',$school_id],['surplus_num','>',0]];
            $pt_coupon = model('PlatformCoupon')->where($pt_where)->field('face_value,threshold,shop_ids')->select();  // 这里需约束下，在红包的有效期内，每个店铺只能参与一种首单立减规格
        }
        // 组装店铺满减信息
        foreach ($shop_list as $k => $v) {
            $temp = [];
            foreach ($v['disc'] as $kk => $vv) {
                $temp[] = $vv['threshold'].'减'.$vv['face_value'];
            }
            unset($v['disc']);
            $v['discounts'] = $temp;
        }

        
        // 组装首单立减信息
        if (!$pt_coupon->isEmpty()) {
            foreach ($shop_list as $k => $v) {
                $temp = $v['discounts'];
                foreach ($pt_coupon as $ko => $vo) {
                    if (in_array($v['id'],explode(',',$vo['shop_ids']))) {
                        $temp[] =  '首单减'.$vo['face_value'];
                        continue;
                    }
                }
                $v['discounts'] = $temp;
            }
        }
        
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
