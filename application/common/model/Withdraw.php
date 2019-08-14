<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/26
 * Time: 4:49 PM
 */

namespace app\common\model;


use think\Model;
use think\Db;

class Withdraw extends Model
{
    /**
     * 获取账户余额
     */
    public function getAcountMoney($shop_id,$startTime = '')
    {

        if($startTime == '') {
            $startTime = time();
        }
        //收入
        $shouru_money = $this->getIncome($shop_id,$startTime);

        //支出
        $zc_money = $this->getExpenditure($shop_id,$startTime);

        $acount_money = $shouru_money - $zc_money;

        return sprintf("%.2f",$acount_money);
    }

    /**
     * 获取支出
     */
    public function getExpenditure($shop_id,$startTime)
    {
        //提现过程中的金额【包括 `已提现`，`申请提现`】
        $tx_money = $this->where([['shop_id','=',$shop_id],['type','=',2],['status','in','1,3']])
            ->whereTime('add_time', '<',$startTime)
            ->sum('money');

        //总支出
        $zc_money = $this->where([['shop_id','=',$shop_id],['type','notin','1,2']])
            ->whereTime('add_time', '<',$startTime)
            ->sum('money');

        return sprintf("%.2f",$tx_money + $zc_money);
    }



    /**
     * 获取收入
     */
    public function getIncome($shop_id,$startTime)
    {
        $shouru_money = $this->where([['shop_id','=',$shop_id],['type','=',1]])
            ->whereTime('add_time', '<',$startTime)
            ->sum('money');

        return sprintf("%.2f",$shouru_money);
    }

    /**
     * 提现规则
     */
    public function getWithdrawRule($shop_id)
    {
        
    }

    /**
     * 计算商家收入和支出
     */
    public function income($order_id)
    {
        $Order = \app\common\model\Orders::get($order_id);
        $shop_info = Db::name('shop_info')->where('id',$Order->shop_id)->field('canteen_id,segmentation')->find();
        $cut_proportion = Db::name('canteen')->where('id',$shop_info['canteen_id'])->value('cut_proportion');
        $assume_ratio = Db::name('platform_coupon')->where('id',$Order->platform_coupon_id)->value('assume_ratio');

        $data = [
            'withdraw_sn' => $Order->orders_sn,
            'shop_id' => $Order->shop_id,
            'money' => $Order->money + $Order->platform_coupon_money,
            'type' => 1,
            'title' => '用户下单',
            'add_time' => time()
        ];

        Db::name('withdraw')->insert($data);

        //活动支出
        if(!empty($Order->shop_discounts_id) && $Order->shop_discounts_money > 0 ) {

            $data = [
                'withdraw_sn' => $Order->orders_sn,
                'shop_id' => $Order->shop_id,
                'money' => $Order->shop_discounts_money,
                'type' => 3,
                'title' => '活动支出',
                'add_time' => time()
            ];
            Db::name('withdraw')->insert($data);
        }

        //抽成支出 平台抽成 + 食堂抽成 + 红包抽成
        $ptExpenditure = ($Order->total_money - $Order->ping_fee) * ($shop_info['segmentation'] / 100);
        $stExpenditure = ($Order->total_money - $Order->ping_fee - $Order->box_money) * ($cut_proportion / 100);
        $hbExpenditure = $Order->platform_coupon_money * ($assume_ratio / 100);

        $data = [
            'withdraw_sn' => $Order->orders_sn,
            'shop_id' => $Order->shop_id,
            'money' => round($ptExpenditure + $stExpenditure + $hbExpenditure,2),
            'type' => 4,
            'title' => '抽成支出',
            'add_time' => time()
        ];
        Db::name('withdraw')->insert($data);

        return true;
    }


    /**
     * 推广支出
     */
    public function tgExpenditure()
    {

    }


    /**
     * @param $order_sn
     * 退款支出
     */
    public function refund($order_sn)
    {
        $refundData = model('Refund')->where('orders_sn',$order_sn)->find();
        $data = [
            'withdraw_sn' => $order_sn,
            'shop_id' => $refundData->shop_id,
            'money' => $refundData->refund_fee,
            'type' => 6,
            'title' => '用户退款',
            'add_time' => time()
        ];

        Db::name('withdraw')->insert($data);
        return true;
    }

}