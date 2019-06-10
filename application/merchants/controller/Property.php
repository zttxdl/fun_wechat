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
    /**
     * 我的资产
     */
    public function myProperty(Request $request)
    {
        $shop_id = $request->param('shop_id');

        $data = model('IncomeExpenditure')->index($shop_id);

        $this->success('获取成功',$data);
    }

    /**
     * 收入金额
     */



    /**
     * 支出金额
     */

    /**
     * 收支明细
     */
    public function receiptPay()
    {

    }

    /**
     * 提现
     */
    public function withdraw()
    {

    }
}