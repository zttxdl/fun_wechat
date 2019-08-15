<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/3
 * Time: 2:40 AM
 */

namespace app\merchants\controller;


use app\common\controller\MerchantsBase;
use think\App;
use think\facade\Cache;
use think\Request;
use think\Db;

class Property extends MerchantsBase
{

    protected $noNeedLogin = [];

    //提现时间规则当天可提现七天之前的结算金额
    protected $startTime;

    public function __construct()
    {
        parent::__construct();

        $this->startTime = date('Y-m-d',strtotime("-7 days")).'23:59:59';
        $this->shop_tx_key = 'shop_tx_key';//店铺提现key
        $this->shop_balance_key = 'shop_balance_key';//店铺余额key
    }


    /**
     * 我的资产
     */
    public function myIndex()
    {
        $shop_id = $this->shop_id;//从Token中获取

        if(!isset($shop_id)) {
            $this->error('shop_id 不能为空!');
        }

        $data = Cache::store('redis')->hGet($this->shop_balance_key,$shop_id);

        if($data) {
            $data = json_decode($data,true);
            $acount_money = $data['acount_money'];
            $totalMoney = $data['totalMoney'];
            $monthMoney = $data['monthMoney'];
            $card = $data['card'];

        }else{
            $acount_money = model('Withdraw')->getAcountMoney($shop_id,$this->startTime);
            $totalMoney = model('Shop')->getCountSales($shop_id);
            $monthMoney = model("Shop")->getMonthSales($shop_id);
            $card = model('shop_more_info')->where('shop_id',$shop_id)->value('back_card_num');

            $cache_data = [
                'acount_money' => $acount_money,
                'totalMoney' => $totalMoney,
                'monthMoney' => $monthMoney,
                'card' => $card,
            ];

            Cache::store('redis')->hSet($this->shop_balance_key,$shop_id,json_encode($cache_data,JSON_UNESCAPED_UNICODE));
        }



        $data = [
            'balanceMoney' => !empty($acount_money) ? $acount_money : 0,//可提现余额
            'totalMoney' => !empty($totalMoney) ? $totalMoney: 0,//总收入
            'monthMoney' => !empty($monthMoney) ? $totalMoney: 0,//本月收入
            'card' => !empty($card) ? $card: '',//银行卡号
        ];



        $this->success('获取成功',$data);

    }

    /**
     * 收支明细
     */
    public function receiptPay(Request $request)
    {
        $shop_id = $this->shop_id;
        $time = $request->param('time',0);

        isset($shop_id) ? $shop_id : $request->param('shop_id');

        $start_time = date('Y-m-01',strtotime($time)).' 00:00:00';
        $end_time = date('Y-m-30',strtotime($time)).' 23:59:59';


        $szmx['income'] = model('withdraw')->getIncome($shop_id,$start_time,$end_time);//收入

        $szmx['expenditure'] = model('withdraw')->getExpenditure($shop_id,$start_time,$end_time);//支出

        $res = Db::name('withdraw')
            ->where('shop_id','=',$shop_id)
            ->whereBetweenTime('add_time',$start_time,$end_time)
            ->order('add_time DESC')
            ->select();


        if(empty($res)) {
            $this->error('暂时没有数据!');
        }

        foreach ($res as $row)
        {
            if($row['money'] == 0) {
                continue;
            }
            $szmx['info'][] = [
                'title' => $row['title'],
                'add_time' => date('Y-m-d H:i:s',$row['add_time']),
                'money' => $row['type'] == 1 ? '+'.$row['money'] : sprintf('%.2f',-1 * $row['money']),
            ];
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
        $card = $request->param('card');//提现卡号

        if($money < 0.3) {
            $this->error('提现金额不能少于0.3元');
        }

        if($money > 5000) {
            $this->error('提现金额不能大于5000元');
        }

        //提现次数
        $check = Cache::store('redis')->hGet($this->shop_tx_key,$shop_id);

        if($check){
            $this->error('一天只能提现一次哦!');
        }
        

        //账户余额
        $balance_money = model('Withdraw')->getAcountMoney($shop_id,$this->startTime);

        if($balance_money < $money) {
            $this->error('您的提现金额大于可提现金额！');
        }

        //提现申请入库
        $txsq = [
            'shop_id' => $shop_id,
            'withdraw_sn' => $withdraw_sn,
            'card' => $card,
            'money' => $money,
            'status' => 1,
            'type' => 2,
            'add_time'=>time(),
            'title'=>'提现'
        ];

        $res = DB::name('withdraw')->insert($txsq);

        if($res) {
            Cache::store('redis')->hSet($this->shop_tx_key,$shop_id,1);
            $this->success('申请成功');
        }
        $this->error('申请失败');

    }


}