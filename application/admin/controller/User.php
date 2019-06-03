<?php


namespace app\admin\controller;


use think\Db;
use think\facade\Request;
//use think\facade\Validate;

class User
{
    public function getList()
    {

        $page_no = Request::param('page_no');
        $page_size = 5;
        $user_list = Db::name('user')->order('id','desc')->page($page_no,$page_size)->select();

        return json_success('获取成功',$user_list);


    }

    public function getDetail()
    {

        //验证参数
        /*$validate = new \app\index\validate\User;
        if(!$validate->test($data)) {
            return json_error($validate->getError());
        }*/


        $uid = Request::param('uid');

        if(!$uid) {
            return json_error('Uid 不能为空');
        }

        $result = [];


        $result['user_detail'] = Db::name('user')->where('id',$uid)->field('nickname,phone,last_login_time,add_time')->find();
        $result['user_detail']['type'] = '普通会员';
        $result['user_detail']['head_img'] = '';

        $data = Model('Orders')->getUserConsume($uid);
        //会员消费总金额
        $result['user_detail']['total_money'] = $data['total_money'];
        //累计交易次数
        $result['user_detail']['order_num'] = $data['order_num'];

        //收货地址信息
        $result['user_address'] = Db::name('receiving_addr')->alias('a')
            ->join('user b','a.user_id = b.id')
            ->join('school c','a.school_id = c.id')
            ->field('a.name,a.phone,b.sex,c.name as school_name ,a.area_detail')
            ->select();

        //$result['user_coupon'] = [];
        //红包信息
        $result['user_coupon'] = Db::name('my_coupon')->alias('a')
            ->leftJoin('platform_coupon b','a.platform_coupon_id = b.id')
            ->field('a.id,a.phone,b.name as coupon_name,b.face_value,b.other_time,b.type,b.limit_use,b.threshold')
            ->select();

        /*foreach ($coupon as $v){
            if($v['platform_coupon_id']) {
                $result['user_coupon'][] = Db::name('platform_coupon')->where('id',$v['platform_coupon_id'])->field('id,name as coupon_name,face_value,other_time,type,limit_use,threshold')->find();
            }
        }*/
         return json_success('获取成功',$result);

    }
}