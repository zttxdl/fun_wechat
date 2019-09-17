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

    public function __construct()
    {
        parent::__construct();
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

        $acount_money = model('Withdraw')->getAcountMoney($shop_id);
        Cache::store('redis')->hSet($this->shop_balance_key,$shop_id,$acount_money);

        $totalMoney = model('Withdraw')->getCountSales($shop_id);
        $monthMoney = model("Withdraw")->getMonthSales($shop_id);
        $card = model('shop_more_info')->where('shop_id',$shop_id)->value('back_card_num');

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

        $start_time = date('Y-m-01',strtotime($time)).' 00:00:00';
        $end_time = date('Y-m-30',strtotime($time)).' 23:59:59';

        // 获取本月收入支出集合
        $res = Db::name('withdraw')
            ->where('shop_id','=',$shop_id)
            ->whereBetweenTime('add_time',$start_time,$end_time)
            ->order('add_time DESC')
            ->select();


        if(empty($res)) {
            $this->error('暂时没有数据!');
        }

        $money = '';
        $szmx = [];
        foreach ($res as $key => $row)
        {
            //提现审核显示调整
            if($row['type'] == '2'){  // 提现
                $type = 2;
                if($row['status'] == '1'){ // 提现待审核
                    $money = '-'.$row['money'].'（待审核）';
                }
                if($row['status'] == '2') { // 提现审核失败
                    $money = $row['money'].'（审核失败）';
                }
                if($row['status'] == '3') { // 已提现
                    $money = '-'.$row['money'];
                }
            }elseif ($row['type'] == '1'){ // 收入【订单收入】
                $type = 1;
                $money = '+'.$row['money'];
            }else{ // 其他支出【3活动支出  5推广支出 6退款】
                if ($row['type'] == '6') {
                    $type = 1;
                } else {
                    $type = 2;
                }
                $money = sprintf('%.2f',-1 * $row['money']);
            }

            $szmx['info'][] = [
                'id'    => $row['id'],
                'type'  => $type,
                'withdraw_sn' => $row['withdraw_sn'],
                'title' => $row['title'],
                'add_time' => date('Y-m-d H:i:s',$row['add_time']),
                'money' => $money,
            ];
        }
        // 本月收入统计
        $income = model('withdraw')->getIncome($shop_id,$start_time,$end_time);
        // 本月支出统计
        $expenditure = model('withdraw')->getExpenditure($shop_id,$start_time,$end_time);

        $szmx['income'] = isset($income) ? $income : '0.00';
        $szmx['expenditure'] = isset($expenditure) ? $expenditure : '0.00';


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
        $account_money = Cache::store('redis')->hGet($this->shop_balance_key,$shop_id);

        if($account_money) {
            $balance_money = $account_money;
        }else{
            $balance_money = model('Withdraw')->getAcountMoney($shop_id);
        }


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


    /**
     * 收支详情 
     * mike 已调整
     * 
     */
    public function withdrawDetails(Request $request)
    {
        $id = $request->param('id');

        // 查询出当前的收支明细数据
        $info = Db::name('withdraw')->find($id);
        if (empty($info)) {
            $this->error('网络加载失败');
        }
        
        // 订单表详细信息
        $order_info = Db::name('orders')->where('orders_sn','=',$info['withdraw_sn'])
                    ->field('add_time,box_money,platform_choucheng,shitang_choucheng,hongbao_choucheng,total_money,shop_discounts_money,num,ping_fee')->find();

        // 商品提价金额
        $hike_arr = Db::name('shop_info')->where('id',$info['shop_id'])->field('price_hike,hike_type')->find();
        // 商品原价 = 订单总价 - 配送费 - 包装费 - 商品提价金额
        if ($hike_arr['hike_type'] == 1) {
            $original_money = $order_info['total_money'] - $order_info['box_money'] - $order_info['ping_fee'] - ($order_info['num'] * $hike_arr['price_hike']);
        } else {
            $original_money = ($order_info['total_money'] - $order_info['box_money'] - $order_info['ping_fee']) / (1 + $hike_arr['price_hike'] * 0.01);
        }

        // 抽成支出
        $choucheng = $order_info['platform_choucheng'] + $order_info['shitang_choucheng'] + $order_info['hongbao_choucheng'];

        $data = [
            'orders_sn'  =>  $info['withdraw_sn'],
            'add_time'  =>  date('Y-m-d H:i:s',$order_info['add_time']),
            'original_money' =>  sprintf('%.2f',$original_money),
            'choucheng' =>sprintf('%.2f',$choucheng),
            'discounts_money' => $order_info['shop_discounts_money'],
            'money' =>  $info['money']
        ];
        
        $this->success('获取成功',['info'=>$data]);
    }


}