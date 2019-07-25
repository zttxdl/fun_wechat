<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/3
 * Time: 2:40 AM
 */

namespace app\merchants\controller;


use app\common\controller\MerchantsBase;
use think\facade\Cache;
use think\Request;
use think\Db;

class Property extends MerchantsBase
{

    protected $noNeedLogin = [];




    /**
     * 我的资产
     */
    public function myIndex()
    {
        $shop_id = $this->shop_id;//从Token中获取

        if(!isset($shop_id)) {
            $this->error('shop_id 不能为空!');
        }


        $acount_money = model('Withdraw')->getAcountMoney($shop_id);

        $totalMoney = model('Shop')->getCountSales($shop_id);
        $monthMoney = model("Shop")->getMonthSales($shop_id);

        $data = [
            'balanceMoney' => !empty($acount_money) ? $acount_money : 0,//可提现余额
            'totalMoney' => !empty($totalMoney) ? $totalMoney: 0,//总收入
            'monthMoney' => !empty($monthMoney) ? $totalMoney: 0//本月收入
        ];

        $this->success('获取成功',$data);

    }

    /**
     * 收支明细
     */
    public function receiptPay(Request $request)
    {
        $shop_id = $this->shop_id;
        $type = $request->param('type',0);//1 收入;2 支出; 0 默认全部

        isset($shop_id) ? $shop_id : $request->param('shop_id');

        $map = [];

        if($shop_id) {
            $map[] = ['shop_id','=',$shop_id];
        }

        if($type) {
            $map[] = ['type','=',$type];
        }

        $szmx = [];//收支明细

        $res = Db::name('withdraw')->where($map)->select();

        if(!$res) {
            $this->error('暂时没有提现记录');
        }

        foreach ($res as $key => $row){
            $szmx[$key] = [
                'id' => $row['id'],
                'shop_id' => $row['shop_id'],
                'title' => $row['title'],
                'add_time' => date('Y-m-d H:i:s',$row['add_time']),
                'money' => $row['money'],//收入支出金额
                'code' => $row['withdraw_sn'],
            ];
            if($row['type'] == 2) {
                if($row['status'] == 1) {
                    $szmx[$key]['money'] = '审核中';
                }
                if($row['status'] == 2) {
                    $szmx[$key]['money'] = '提现失败';
                }
            }
        }


        $this->success('success',$szmx);

    }

    /**
     * 提现
     */
    public function withdraw(Request $request)
    {
        $shop_id = $this->shop_id;
        $withdraw_sn = build_order_no('TXBH');
        $money = $request->param('money');//提现金额

        if($money < 1) {
            $this->error('提现金额不能少于1元');
        }


        $start = strtotime(date('Y-m-d').'00:00:00');
        $end = strtotime(date('Y-m-d').'23:59:59');

        $key = 'shop_tx_'.$shop_id;

        //提现次数
        $check = Cache::store('redis')->tag('shop_tx')->get($key); 

        if($check){
            $this->error('一天只能提现一次哦!');
        }
        

        //账户余额
        $balance_money = model('Withdraw')->getAcountMoney($shop_id);

        if($balance_money < $money) {
            $this->error('您的提现金额大于可提现金额！');
        }

        //提现申请入库
        $txsq = [
            'shop_id' => $shop_id,
            'withdraw_sn' => $withdraw_sn,
            'money' => $money,
            'status' => 1,
            'type' => 2,
            'add_time'=>time(),
            'title'=>'提现'
        ];

        $res = DB::name('withdraw')->insert($txsq);

        if($res) {
            Cache::store('redis')->tag('shop_tx')->set($key,1,3600*24);
            $this->success('申请成功');
        }
        $this->error('申请失败');

    }


}