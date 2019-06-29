<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/3
 * Time: 2:40 AM
 */

namespace app\merchants\controller;


use app\common\controller\MerchantsBase;
use think\Request;
use think\Db;

class Property extends MerchantsBase
{

    protected $noNeedLogin = ['*'];




    /**
     * 我的资产
     */
    public function myIndex($shop_id)
    {

        $shop_id = $this->shop_id;//从Token中获取

        set_log('shop_id',$shop_id,'MyIndex');

        $acount_money = model('Withdraw')->getAcountMoney($shop_id);

        $totalMoney = model('Shop')->getCountSales($shop_id);
        $monthMoney = model("Shop")->getMonthSales($shop_id);

        $data = [
            'balanceMoney' => isset($acount_money) ? $acount_money : 0,//可提现余额
            'totalMoney' => isset($totalMoney) ? $totalMoney: 0,//总收入
            'monthMoney' => isset($monthMoney) ? $totalMoney: 0//本月收入
        ];

        $this->success('获取成功',$data);

    }

    /**
     * 收支明细
     */
    public function receiptPay(Request $request)
    {
        $shop_id = isset($this->shop_id) ? $this->shop_id : 15;
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
        //echo $res;exit;
        //dump($res);
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
        //$shop_id = $this->shop_id;
        $shop_id = 15;
        $withdraw_sn = build_order_no('TXBH');
        $moeny = $request->param('money');//提现金额


        $start = strtotime(date('Y-m-d').'00:00:00');
        $end = strtotime(date('Y-m-d').'23:59:59');

        //提现次数
        /*$num = Db::name('Withdraw')
            ->where('add_time','between time',[$start,$end])
            ->where('shop_id',$shop_id)
            ->find();

        if($num){
            $this->error('一天只能提现一次哦!');
        }*/

        //账户余额
        $balance_money = model('Withdraw')->getAcountMoney($shop_id);

        if($balance_money < $moeny) {
            $this->error('提现金额不正确');
        }

        //提现申请入库
        $txsq = [
            'shop_id' => $shop_id,
            'withdraw_sn' => $withdraw_sn,
            'money' => -$moeny,
            'status' => 1,
            'type' => 2,
            'add_time'=>time(),
            'title'=>'提现'
        ];

        $res = DB::name('withdraw')->insert($txsq);

        if($res) {
            $this->success('申请成功');
        }
        $this->error('申请失败');

    }


}