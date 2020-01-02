<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Db;
use think\Request;

class Statistics extends Base
{
    /**
     * 用户活跃指标
     *
     */
    public function user(Request $request)
    {
        $time = $request->param('times');
        // 调取条件
        $data = conditions($time);
        $search_time = $data['search_time'];
        $res = $data['res'];
        $nums = $data['nums'];
        $temp_time = $data['temp_time'];
        $result['today_active'] = model('UserActive')->where('save_time','=',date('Y-m-d'))->value('count');
        $result['user_new'] = model('UserNew')->getUserNewCount($search_time,$res,$nums);
        $result['user_active'] = model('UserActive')->getUserActiveCount($search_time,$res,$nums);
        $result['all_user'] = model('User')->count('*');
        $newNum = model('User')->where('add_time','between',$search_time)->count('*');
        $result['new_user_percent'] = floatval(sprintf("%.2f", $newNum / $result['all_user'] * 100)) ;
        $result['time'] = implode('~',$temp_time);
        // 待更新、、、


        
        $this->success('获取数据成功',["list"=>$result]);

    }


    /**
     * 订单统计
     *
     */
    public function order()
    {
        //
    }


    /**
     * 财务统计
     *
     */
    public function finance()
    {
        //
    }


    /**
     * 优惠券统计
     *
     */
    public function coupon()
    {
        //
    }


    /**
     * 学校下的应付商家金额
     */
    public function shopMoneyToday(Request $request)
    {
        $time = $request->param('time');
        $school_id = $request->param('school_id');
        $school_name = model('School')->getNameById($school_id);
        $start_time = strtotime($time." 00:00:00");
        $end_time = strtotime($time." 23:59:59");
        $shop_ids = Db::name('shop_info')->where([['school_id','=',$school_id],['status','=',3]])->column('id');
        // 当前收入
        $income_list = Db::name('withdraw w')->join('shop_info s','w.shop_id = s.id')
                        ->where('w.shop_id','in',$shop_ids)
                        ->where('w.type','=',1)
                        ->where('w.add_time','between',[$start_time,$end_time])
                        ->group('w.shop_id')
                        ->field('s.shop_name,sum(w.money) as income_sum,w.shop_id')
                        ->select();
        // 当前支出
        $expend_list = Db::name('withdraw w')->join('shop_info s','w.shop_id = s.id')
                        ->where('w.shop_id','in',$shop_ids)
                        ->where('w.type','=',6)
                        ->where('w.add_time','between',[$start_time,$end_time])
                        ->group('w.shop_id')
                        ->field('s.shop_name,sum(w.money) as expend_sum,w.shop_id')
                        ->select();

        // 因数据库架构没做好，此处处理比较烦                
        foreach ($income_list as $k => &$v) {
            $v['expend_sum'] = 0;
            $v['sum'] = sprintf('%.2f',$v['income_sum']);
            foreach($expend_list as $ko => $vo){
                if ($v['shop_id'] == $vo['shop_id']) {
                    $v['expend_sum'] = '-'.sprintf('%.2f',$vo['expend_sum']);
                    $v['sum'] = sprintf('%.2f',$v['income_sum'] - $vo['expend_sum']);
                }
            }
        }
        unset($v);
        if(empty($income_list)) {
            $this->error('暂时没有数据!');
        }
        create_excel($income_list,$school_name.'_'.$time);
    }


    /**
     * 今日应付商家金额
     */
    public function shopMoneyTodayDetails(Request $request)
    {
        $time = $request->param('time');
        $shop_id = $request->param('shop_id');
        $shop_name = Db::name('shop_info')->where('id','=',$shop_id)->value('shop_name');
        $start_time = strtotime($time." 00:00:00");
        $end_time = strtotime($time." 23:59:59");

        // 获取当天收入支出集合
        $list = Db::name('withdraw')
            ->where('shop_id','=',$shop_id)
            ->where('add_time','between',[$start_time,$end_time])
            ->field('type,money,add_time,title,withdraw_sn')
            ->order('add_time DESC')
            ->select();


        if(empty($list)) {
            $this->error('暂时没有数据!');
        }

        foreach ($list as $key => &$row)
        {
            if ($row['type'] == '1'){ // 收入【订单收入】
                $row['money'] = sprintf('%.2f', $row['money']);
            }elseif ($row['type'] == '6'){ // 退款
                $row['money'] = sprintf('%.2f', -1 * $row['money']);
            }
            $row['add_time'] = date('Y-m-d H:i:s',$row['add_time']);
        }
        create_shop_excel($list,$shop_name.'_'.$time);

    }


    
}
