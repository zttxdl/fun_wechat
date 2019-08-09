<?php

namespace app\api\controller;

use app\common\controller\ApiBase;
use think\Request;
use think\Db;

class Index extends ApiBase
{

    protected $noNeedLogin = ['*'];

    //通过经纬度获取最近学校
    public function  getSchool(Request $request)
    {
        $lat = $request->param('latitude','');
        $lng = $request->param('longitude','');

        $data = model('School')->field("id,name,ROUND(6371 * acos (cos ( radians($lat)) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( $lng) ) + sin ( radians( $lat) ) * sin( radians( latitude ) ) ),1 ) AS distance ")
            ->having('distance < 5')
            ->where('level',2)
            ->order('distance asc')
            ->find();

        $this->success('success',$data);
    }


    /**
     * 首页
     */
    public function index(Request $request)
    {
        $lat = $request->param('latitude','');
        $lng = $request->param('longitude','');
        $school_id = $request->param('school_id')? $request->param('school_id') : 13;

        // 调用轮播图
        $data['slide'] = $this->getSlide($school_id);
        // 调用分类导航
        $data['channel'] = $this->getChannel();

        if ($school_id) { // 如果有学校主键值，则直接获取学校信息
            $data['current_school'] = model('School')->field("id,name")->where('id','=',$school_id)->find();
        } else { // 如果没有学校主键值，通过经纬度获取最近学校
            $data['current_school'] = model('School')->field("id,name,ROUND(6371 * acos (cos ( radians($lat)) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( $lng) ) + sin ( radians( $lat) ) * sin( radians( latitude ) ) ),1 ) AS distance ")
                ->having('distance < 5')->where('level',2)->order('distance asc')->find();
        }

        $this->success('success',$data);
    }


    /**
     * 获取轮播图
     */
    public function getSlide($school_id)
    {
        // 搜索条件
        $where[] = ['start_time','<=',time()];
        $where[] = ['end_time','>=',time()];
        $where[] = ['status','=',1];
        $where[] = ['advert_id','=',1];
        $where[] = ['coverage','in',['0',$school_id]];


        $list = model('Advert')
            ->field('id,title,imgurl,link_url,type')
            ->where($where)
            ->order('sort', 'asc')
            ->select();

        return $list;
    }


    /**
     * 获取分类导航
     */
    public function getChannel()
    {
        $data = model('ManageCategory')->field('id,name,img')->order('sort','asc')->limit(8)->select();
        return $data;
    }


    /**
     * 获取今日特价
     */
    public function getSpecial(Request $request)
    {
        // 学校主键值
        $school_id = $request->param('school_id');
        if (! $school_id){
            $this->error('非法传参');
        }
        // 搜索条件
        $day = date('Y-m-d',time());
        $where[] = ['today','=',$day];
        $where[] = ['status','=',1];
        $where[] = ['school_id','=',$school_id];
        $where[] = ['end_time', '>=',time()];
        $today_sale = model('TodayDeals')
            ->field('id,name,product_id,old_price,price,num,limit_buy_num,thumb,start_time,end_time')
            ->where($where)->limit(4)->select();
        if ($today_sale){
            foreach ($today_sale as $item) {
                $item->res_time = $item->end_time - time();
            }

        }
        $this->success('success',$today_sale);
    }


    /**
     * 获取推荐商家
     * @param $school_id  学校主键值
     * @param $openid  用户openid值
     */
    public function getRecommendList(Request $request)
    {
        // 学校主键值
        $school_id = $request->param('school_id');
        // 判断是否传入openid值，如果传入， 判断当前的openid值，如果有值且能在用户表中找到，并且该用户为首单用户，则判断当前学校是否有首单立减红包， 有则展示，没有则不展示
        $openid = $request->param('openid','0');
        $uid = model('User')->where([['openid','=',$openid],['new_buy','=',1]])->value('id');

        // 搜索条件
        $where[] = ['school_id','=',$school_id];
        $where[] = ['status','=',3];
        $pagesize = $request->param('pagesize',10);


        // 依据商家排序获取推荐商家
        $shop_list = model('ShopInfo')->where($where)->field('id,shop_name,logo_img,marks,ping_fee,up_to_send_money,open_status as business,run_time')->order('sort','asc')->paginate($pagesize)->each(function ($item) {

            // 判断是否休息中
            if ($item->business == 1 && !empty($item->run_time)) {
                $item->business = model('ShopInfo')->getBusiness($item->run_time);
            } else {
                $item->business = 0;
            }

            // 获取优惠券信息
            $item->disc = model('ShopDiscounts')->field('face_value,threshold')->where('shop_id',$item->id)->where('delete',0)->order('threshold','asc')->select();
            // 获取月销售额
            $item->sales = model('Shop')->getMonthNum($item->id);
        });
        // 组装店铺满减信息
        $shop_list = $shop_list->toArray();
        foreach ($shop_list['data'] as &$v) {
            foreach ($v['disc'] as &$vv) {
                $v['discounts'][] = $vv['threshold'].'减'. $vv['face_value'];
                unset($v['disc']);
            }
            $v['marks'] = (float)$v['marks'];
        }

        $pt_coupon = [];
        if ($uid) {
            // 首单立减红包仅 平台发放这种形式  ，搜索条件如下
            $pt_where = [['status','=',2],['type','=',2],['coupon_type','=',2],['school_id','=',$school_id]];
            // 这里需约束下，在红包的有效期内，每个店铺只能参与一种首单立减规格
            $pt_coupon_ids = model('PlatformCoupon')->where($pt_where)->column('id');
            
            // 获取当前用户的首单红包
            if ($pt_coupon_ids) {
                $pt_coupon = Db::name('my_coupon m')->join('platform_coupon p','m.platform_coupon_id = p.id')
                                ->where([['m.platform_coupon_id','in',$pt_coupon_ids],['m.user_id','=',$uid]])
                                ->field('p.face_value,p.threshold,p.shop_ids')->select();
            }
        }

        // 组装首单立减信息
        if ($pt_coupon) {
            foreach ($shop_list['data'] as $k => &$v) {
                foreach ($pt_coupon as $ko => $vo) {
                    $shopids = explode(',',$vo['shop_ids']);
                    if (in_array($v['id'],$shopids)) {
                        $v['discounts'][] = '首单减'.$vo['face_value'];

                        continue;
                    }
                }
            }
        }


        $this->success('success',$shop_list);
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
        $school_id = $request->param('school_id');
        // 一级经营品类导航主键值
        $class_id = $request->param('class_id');

        // 判断是否传入openid值，如果传入， 判断当前的openid值，如果有值且能在用户表中找到，并且该用户为首单用户，则判断当前学校是否有首单立减红包， 有则展示，没有则不展示
        $openid = $request->param('openid','0');
        $uid = model('User')->where([['openid','=',$openid],['new_buy','=',1]])->value('id');

        if (!$school_id || !$class_id) {
            $this->error('非法参数');
        }

        /********* 搜索条件 ***************************************************************/
        $where[] = ['school_id','=',$school_id];
        $where[] = ['manage_category_id','=',$class_id];
        $where[] = ['status','=',3];
        $pagesize = $request->param('pagesize',10);

        /********* 依据商家排序、搜索条件，获取二级经营品类的商家信息 ********************************/
        $shop_list = model('ShopInfo')->where($where)->field('id,shop_name,logo_img,marks,ping_fee,up_to_send_money,open_status as business,run_time')->order('sort','asc')->paginate($pagesize)->each(function ($item) {
            // 判断是否休息中
            if ($item->business == 1 && !empty($item->run_time)) {
                $item->business = model('ShopInfo')->getBusiness($item->run_time);
            } else {
                $item->business = 0;
            }

            // 获取优惠券信息
            $item->disc = model('ShopDiscounts')->field('face_value,threshold')->where('shop_id',$item->id)->where('delete',0)->order('threshold','asc')->select();
            // 获取月销售额
            $item->sales = model('Shop')->getMonthNum($item->id);
        });

        // 组装店铺满减信息
        $shop_list = $shop_list->toArray();
        foreach ($shop_list['data'] as &$v) {
            foreach ($v['disc'] as &$vv) {
                $v['discounts'][] = $vv['threshold'].'减'. $vv['face_value'];
                unset($v['disc']);
            }
            $v['marks'] = (float)$v['marks'];
        }

        $pt_coupon = [];
        if ($uid) {
            // 首单立减红包仅 平台发放这种形式  ，搜索条件如下
            $pt_where = [['status','=',2],['type','=',2],['coupon_type','=',2],['school_id','=',$school_id]];
            // 这里需约束下，在红包的有效期内，每个店铺只能参与一种首单立减规格
            $pt_coupon_ids = model('PlatformCoupon')->where($pt_where)->column('id');
            
            // 获取当前用户的首单红包
            if ($pt_coupon_ids) {
                $pt_coupon = Db::name('my_coupon m')->join('platform_coupon p','m.platform_coupon_id = p.id')
                                ->where([['m.platform_coupon_id','in',$pt_coupon_ids],['m.user_id','=',$uid]])
                                ->field('p.face_value,p.threshold,p.shop_ids')->select();
            }
        }

        // 组装首单立减信息
        if ($pt_coupon) {
            foreach ($shop_list['data'] as $k => &$v) {
                foreach ($pt_coupon as $ko => $vo) {
                    $shopids = explode(',',$vo['shop_ids']);
                    if (in_array($v['id'],$shopids)) {
                        $v['discounts'][] = '首单减'.$vo['face_value'];
                        continue;
                    }
                }
            }
        }
        $this->success('success',$shop_list);
    }


    /**
     * 获取更多今日特价
     *
     */
    public function getSpecialList(Request $request)
    {
        // 学校主键值
        $school_id = $request->param('school_id');
        if (!$school_id){
            $this->error('非法传参');
        }
        // 搜索条件
        $day = date('Y-m-d',time());
        $where[] = ['t.today','=',$day];
        $where[] = ['t.status','=',1];
        $where[] = ['s.status','=',3];
        $where[] = ['t.school_id','=',$school_id];
        $pagesize = $request->param('pagesize',10);

        $today_sale = model('TodayDeals')->alias('t')->join('shop_info s','t.shop_id = s.id')->field('t.name,t.shop_id,t.product_id,t.old_price,t.price,t.num,t.limit_buy_num,t.thumb,t.start_time,t.end_time,s.shop_name,s.up_to_send_money,s.ping_fee')
            ->where($where)->whereTime('t.end_time', '>=', time())->paginate($pagesize);

        if ($today_sale){
            foreach ($today_sale as $item) {
                $item->res_time = $item->end_time - time();
            }
        }
        $this->success('success',$today_sale);
    }

    /**
     * 专属推荐
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getExclusive(Request $request)
    {
        // 学校主键值
        $school_id = $request->param('school_id');
        if (!$school_id){
            $this->error('非法传参');
        }
        $where[] = ['a.start_time','<=',time()];
        $where[] = ['a.end_time','>=',time()];
        $where[] = ['a.status','=',1];
        $where[] = ['a.advert_id','=',2];
        $where[] = ['s.status','=',3];
        $where[] = ['a.coverage','=',$school_id];
        $list = model('Advert')->alias('a')
            ->Join('shop_info s','a.shop_id = s.id')
            ->field('a.shop_id,a.imgurl,s.shop_name,s.logo_img')
            ->where($where)
            ->order('a.sort','asc')
            ->limit(6)
            ->select();

        $this->success('success',$list);
    }

    /**
     * 专属推荐
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMoreExclusive( Request $request)
    {
        // 学校主键值
        $school_id = $request->param('school_id');
        if (!$school_id){
            $this->error('非法传参');
        }

        $openid = $request->param('openid','0');
        $uid = model('User')->where([['openid','=',$openid],['new_buy','=',1]])->value('id');

        // 搜索条件
        $where[] = ['a.start_time','<=',time()];
        $where[] = ['a.end_time','>=',time()];
        $where[] = ['a.status','=',1];
        $where[] = ['a.advert_id','=',2];
        $where[] = ['s.status','=',3];
        $where[] = ['a.coverage','=',$school_id];
        $pagesize = $request->param('pagesize',20);


        $shop_list =  model('Advert')->alias('a')
            ->Join('shop_info s','a.shop_id = s.id')
            ->field('s.id,s.shop_name,s.logo_img,s.marks,s.ping_fee,s.up_to_send_money,s.open_status as business,s.run_time')
            ->where($where)
            ->order('a.sort','asc')
            ->paginate($pagesize)->each(function ($item) {

                // 判断是否休息中
                if ($item->business == 1 && !empty($item->run_time)) {
                    $item->business = model('ShopInfo')->getBusiness($item->run_time);
                } else {
                    $item->business = 0;
                }

                // 获取优惠券信息
                $item->disc = model('ShopDiscounts')->field('face_value,threshold')->where('shop_id',$item->id)->where('delete',0)->order('threshold','asc')->select();
                // 获取月销售额
                $item->sales = model('Shop')->getMonthNum($item->id);
        });
        // 组装店铺满减信息
        $shop_list = $shop_list->toArray();
        foreach ($shop_list['data'] as &$v) {
            foreach ($v['disc'] as &$vv) {
                $v['discounts'][] = $vv['threshold'].'减'. $vv['face_value'];
                unset($v['disc']);
            }
            $v['marks'] = (float)$v['marks'];
        }

        $pt_coupon = [];
        if ($uid) {
            // 首单立减红包仅 平台发放这种形式  ，搜索条件如下
            $pt_where = [['status','=',2],['type','=',2],['coupon_type','=',2],['school_id','=',$school_id]];
            // 这里需约束下，在红包的有效期内，每个店铺只能参与一种首单立减规格
            $pt_coupon_ids = model('PlatformCoupon')->where($pt_where)->column('id');

            // 获取当前用户的首单红包
            if ($pt_coupon_ids) {
                $pt_coupon = Db::name('my_coupon m')->join('platform_coupon p','m.platform_coupon_id = p.id')
                    ->where([['m.platform_coupon_id','in',$pt_coupon_ids],['m.user_id','=',$uid]])
                    ->field('p.face_value,p.threshold,p.shop_ids')->select();
            }
        }

        // 组装首单立减信息
        if ($pt_coupon) {
            foreach ($shop_list['data'] as $k => &$v) {
                foreach ($pt_coupon as $ko => $vo) {
                    $shopids = explode(',',$vo['shop_ids']);
                    if (in_array($v['id'],$shopids)) {
                        $v['discounts'][] = '首单减'.$vo['face_value'];

                        continue;
                    }
                }
            }
        }

        $this->success('success',$shop_list);

    }
}
