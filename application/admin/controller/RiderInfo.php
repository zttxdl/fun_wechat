<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use think\Db;
use app\common\service\SendMsg;

class RiderInfo extends Base
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
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 10;
        $where[] = ['status','in','3,4'];

        $list = Db::name('rider_info')->where($where)->field('id,name,link_tel,status,add_time,last_login_time')->order('id desc')
                ->paginate($pagesize)->each(function ($item, $key) {
                    // 当前骑手的送餐信息
                    $temp = Db::name('rider_income_expend')->where('rider_id','=',$item['id'])->where('type','=',1)->field('count(id) as order_nums, sum(current_money) as earnings')->find();
                    // 完成送餐单数
                    $item['order_nums'] = $temp['order_nums'];
                    // 累计收益
                    $item['earnings'] = !empty($temp['earnings']) ? sprintf("%.2f", $temp['earnings']) : 0;
                    // 注册时间
                    $item['add_time'] = date('Y-m-d H:i:s',$item['add_time']);
                    // 最近登录时间
                    $item['last_login_time'] = date('Y-m-d H:i:s',$item['last_login_time']);
                    $item['mb_status'] = $item['status'] == 3 ? '禁用' : '启用';

                    return $item;
                });

        $this->success('ok',['list'=>$list]);
    }


    /**
     * 设置骑手状态【禁用】
     * @param $id 骑手信息表主键值
     * @param $status 禁用状态值
     */
    public function status($id,$status)
    {
        if ($status == 4) { // 当禁用店铺时，需判断该店铺是否存在未完结的订单往来
            // 获取未完结的订单
            $count = model('Takeout')->where([['rider_id','=',$id],['status','in',[3,4,5]]])->count();
            if ($count) {
                $this->error('该骑手还存在未处理的订单，暂时不可禁用此骑手',202);
            }
        }

        $result = Db::name('rider_info')->where('id','=',$id)->setField('status',$status);
        if (!$result) {
            $this->error('设置失败');
        }
        $this->success('设置成功');
    }


    /**
     * 展示审核骑手详情 
     * @param $id 骑手信息表主键值
     */
    public function checkShow($id)
    {
        // 骑手的基本信息
        $info = Db::name('rider_info')->field('id,nickname,name,headimgurl,link_tel,identity_num,card_img,back_img,hand_card_img,status,school_id')->find($id);
        $info['school_name'] = model('School')->getNameById($info['school_id']);

        $info['mb_status'] = config('rider_check_status')[$info['status']];

        $this->success('ok',['info'=>$info]);
    }


    /**
     * 展示骑手详情 
     * @param $id 骑手信息表主键值
     */
    public function show($id)
    {
        // 骑手的基本信息
        $info = Db::name('rider_info')->field('id,nickname,name,headimgurl,link_tel,identity_num,card_img,back_img,hand_card_img,status,pass_time,school_id')->find($id);
        $info['school_name'] = model('School')->getNameById($info['school_id']);

        /** 结算信息 */
        // 已结算收入【骑手除去当天未结算的所有金额】
        $settlement['already_money'] = (string)model('RiderIncomeExpend')->getAlreadyJsMoney($id);

        // 提现金额【包括 申请提现、申请提现】
        $tx_money = (string)model('RiderIncomeExpend')->getTxMoney($id);
        
        // 可结算金额【骑手可提现金额】
        $settlement['can_tx_money'] = $settlement['already_money'] - $tx_money;

        // 待结算金额【骑手当天未结算的金额】
        $settlement['not_tx_money'] = model('RiderIncomeExpend')->getNotJsMoney($id);

        // 总配送单数
        $settlement['all_nums'] = (string)model('RiderIncomeExpend')->allNums($id);

        // 本月配送单数
        $settlement['mouth_nums'] = (string)model('RiderIncomeExpend')->mouthNums($id);

        // 当前骑手入驻天数
        $days = date('d',time()-$info['pass_time']);
        // 日配送单数【总配送单数 / 入驻天数】
        $settlement['avg_nums'] = sprintf("%.2f", $settlement['all_nums'] / $days) ;

        // 订单流水
        $order_list = Db::name('orders o')->join('takeout t','o.id = t.order_id')->join('user u','o.user_id = u.id')->join('shop_info s','o.shop_id = s.id')->where('t.rider_id',$id)->field('o.orders_sn,s.shop_name,o.money,o.add_time,t.create_time,t.status,u.nickname as user_name,u.phone as user_phone')->select();
        
        foreach ($order_list as $k => &$v) {
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $v['issuing_time'] = date('Y-m-d H:i:s',$v['create_time']);
            $v['mb_status'] = config('rider_order_status')[$v['status']];
        }

        $this->success('ok',['info'=>$info,'order_list'=>$order_list,'settlement'=>$settlement]);
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
        !empty($request->get('status/d')) ? $where[] = ['status','=',$request->get('status/d')] : $where[] = ['status','notin','0,4'];
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 10;

        $list = Db::name('rider_info')->where($where)->field('id,nickname,name,headimgurl,link_tel,identity_num,hand_card_img,status')->order('id desc')->paginate($pagesize)
                ->each(function ($item, $key) {
                    // 审核状态
                    $item['mb_status'] = config('rider_check_status')[$item['status']];

                    return $item;
                });

        $this->success('ok',['list'=>$list]);  
    }


    /**
     * 设置审核操作 
     * 
     */
    public function setCheckStatus(Request $request)
    {
        $data = $request->post();
        $data['pass_time'] = time();

        $result = Db::name('rider_info')->update($data);
        if (!$result) {
            $this->error('设置失败');
        }

        // 推送微信模板消息
        $sendMsg = new sendMsg();
        $sendMsg->passCheckSend($data['id']);

        $this->success('设置成功');
    }


    /**
     * 骑手审核展示
     */
    public function checkStatusList()
    {
        $data = config('check_status')['rider'];
        $this->success('获取成功',$data);
    }

     
     
     
     



     
}
