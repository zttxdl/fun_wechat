<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\common\model\CheckStatus;

class RiderInfo extends Controller
{
    /**
     * 骑手列表 
     * 
     */
    public function index(Request $request)
    {
        // 搜索条件
        $where= [];
        !empty($request->get('name/s')) ? $where[] = ['name','like',$request->get('name/s').'%'] : null;
        $where[] = ['status','=',3];

        $list = Db::name('rider_info')->where($where)->field('id,name,link_tel,status,add_time,last_login_time')->order('id desc')
                ->paginate(10)->each(function ($item, $key) {
                    // 完成送餐单数
                    $item['order_nums'] = Db::name('orders')->where('rider_id',$item['id'])->count('id');
                    // 累计收益
                    $item['earnings'] = '';
                    // 注册时间
                    $item['add_time'] = date('Y-m-d H:i:s',$item['add_time']);
                    // 最近登录时间
                    $item['last_login_time'] = date('Y-m-d H:i:s',$item['last_login_time']);

                    return $item;
                });

        return json_success('ok',['list'=>$list]);
    }


    /**
     * 设置骑手状态【禁用】
     * 
     */
    public function status($id,$status)
    {
        $result = Db::name('rider_info')->where('id','=',$id)->setField('status',$status);
        if (!$result) {
            return json_error('设置失败');
        }
        return json_success('ok');
    }


    /**
     * 展示审核骑手详情 
     * 
     */
    public function checkShow($id)
    {
        // 骑手的基本信息
        $info = Db::name('rider_info')->field('id,nickname,name,img,link_tel,identity_num,card_img,back_img,hand_card_img,status')->find($id);

        return json_success('ok',['info'=>$info]);
    }


    /**
     * 展示审核骑手详情 
     * 
     */
    public function show($id)
    {
        // 骑手的基本信息
        $info = Db::name('rider_info')->field('id,nickname,name,img,link_tel,identity_num,card_img,back_img,hand_card_img,status')->find($id);

        // 结算信息
        // $statistics_info = '';

        // 订单流水
        $order_list = Db::name('orders o')->join('shop_info s','o.shop_id = s.id')->where('o.rider_id',$id)->field('o.orders_sn,o.address,s.shop_name,o.money,o.add_time,o.issuing_time,o.status')->select();
        
        foreach ($order_list as $k => &$v) {
            $temp = json_decode($v['address'],true);
            $v['user_name'] = $temp['name'];
            $v['user_phone'] = $temp['phone'];
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $v['issuing_time'] = date('Y-m-d H:i:s',$v['issuing_time']);
            $v['mb_status'] = config('order_status')[$v['status']];
        }

        return json_success('ok',['info'=>$info,'order_list'=>$order_list]);
    }


    /**
     * 骑手审核列表 
     * 
     */
    public function checkRiderList(Request $request)
    {
        // 搜索条件
        $where= [];
        !empty($request->get('name/s')) ? $where[] = ['name','like',$request->get('name/s').'%'] : null;
        !empty($request->get('status/d')) ? $where[] = ['status','=',$request->get('status/d')] : $where[] = ['status','=',1];

        $list = Db::name('rider_info')->where($where)->field('id,nickname,name,img,link_tel,identity_num,hand_card_img,status')->order('id desc')->paginate(10)
                ->each(function ($item, $key) {
                    // 审核状态
                    $item['mb_status'] = config('rider_check_status')[$item['status']];

                    return $item;
                });

        return json_success('ok',['list'=>$list]);  
    }


    /**
     * 设置审核操作 
     * 
     */
    public function setCheckStatus(Request $request)
    {
        $data = $request->post();

        $result = Db::name('rider_info')->update($data);
        if (!$result) {
            return json_error('设置失败');
        }

        return json_success('ok');
    }

     
     
     
     



     
}
