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

class Property extends MerchantsBase
{

    protected $noNeedLogin = ["*"];

    protected $type = [1=>'收入',2=>'支出'];



    /**
     * 我的资产
     */
    public function myProperty(Request $request)
    {

        $shop_id = $this->shop_id;//从Token中获取

        isset($shop_id) ? $shop_id : $request->param('shop_id');


        $data = model('IncomeExpenditure')->index($shop_id);

        $data = [
            'balanceMoney' => $data['balance_money'],//可提现余额
            'totalMoney' => model('Shop')->getCountSales($shop_id),//总收入
            'monthMoney' => model("Shop")->getMonthSales($shop_id)//本月收入
        ];

        $this->success('获取成功',$data);

    }

    /**
     * 收支明细
     */
    public function receiptPay(Request $request)
    {
        $shop_id = $this->shop_id;

        isset($shop_id) ? $shop_id : $request->param('shop_id');

    }

    /**
     * 提现
     */
    public function withdraw()
    {

    }
}