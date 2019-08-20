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
     * 获取账户余额【可提现金额】
     */
    public function getAcountMoney($shop_id)
    {
        // 提现规则 7天前
        $startTime = date('Y-m-d',strtotime("-7 days")).'23:59:59';
        //收入
        $shouru_money = $this->getIncome($shop_id,$startTime);

        //支出
        $zc_money = $this->getExpenditure($shop_id,$startTime);

        $acount_money = $shouru_money - $zc_money;

        return sprintf("%.2f",$acount_money);
    }


    /**
     * 未结算金额 
     * 
     */
    public function getNotJsMoney($shop_id)
    {
        // 七天之内的总收入
        $sr_money = $this->where([['shop_id','=',$shop_id],['type','=',1]])->whereTime('add_time', '>=',strtotime("-7 days").'23:59:59')->sum('money');

        // 七天之内的总支出[抽成支出 + 退款 + 推广]
        $zc_money = $this->where([['shop_id','=',$shop_id],['type','in','4,5,6']])->whereTime('add_time', '>=',strtotime("-7 days").'23:59:59')->sum('money');

        $not_js_money = $sr_money - $zc_money;

        return sprintf("%.2f",$not_js_money);
    }
     


    /**
     * 获取支出
     */
    public function getExpenditure($shop_id,$startTime,$endTime = '')
    {
        if(empty($endTime)) {
            //提现过程中的金额【包括 `已提现`，`申请提现`】
            $tx_money = $this->where([['shop_id','=',$shop_id],['type','=',2],['status','in','1,3']])
                ->whereTime('add_time', '<',$startTime)
                ->sum('money');

            //总支出 过滤提现 和活动支出
            $zc_money = $this->where([['shop_id','=',$shop_id],['type','notin','1,2,3']])
                ->whereTime('add_time', '<',$startTime)
                ->sum('money');

        }else{
            //提现过程中的金额【包括 `已提现`，`申请提现`】
            $tx_money = $this->where([['shop_id','=',$shop_id],['type','=',2],['status','in','1,3']])
                ->whereBetweenTime('add_time',$startTime,$endTime)
                ->sum('money');;

            //总支出 过滤提现 和活动支出
            $zc_money = $this->where([['shop_id','=',$shop_id],['type','notin','1,2,3']])
                ->whereBetweenTime('add_time',$startTime,$endTime)
                ->sum('money');
        }



        return sprintf("%.2f",$tx_money + $zc_money);
    }



    /**
     * 获取收入
     */
    public function getIncome($shop_id,$startTime,$endTime='')
    {
        if(empty($endTime)) {
            $shouru_money = $this->where([['shop_id','=',$shop_id],['type','=',1]])
                ->whereTime('add_time', '<',$startTime)
                ->sum('money');
        }else{
            $shouru_money = $this->where([['shop_id','=',$shop_id],['type','=',1]])
                ->whereBetweenTime('add_time',$startTime,$endTime)
                ->sum('money');
        }



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
        //如果商品提价不计算抽成
        if($shop_info['price_hike']) {
            $ptExpenditure = $ptExpenditure - $shop_info['price_hike'];
        }
        $stExpenditure = ($Order->total_money - $Order->ping_fee - $Order->box_money) * ($cut_proportion / 100);
        $hbExpenditure = $Order->platform_coupon_money * ($assume_ratio / 100);

        $total_expenditure = round($ptExpenditure + $stExpenditure + $hbExpenditure,2);

        //抽成支出不为0的时候记录
        if($total_expenditure != 0) {
            $data = [
                'withdraw_sn' => $Order->orders_sn,
                'shop_id' => $Order->shop_id,
                'money' => $total_expenditure,
                'type' => 4,
                'title' => '抽成支出',
                'add_time' => time()
            ];
            Db::name('withdraw')->insert($data);
        }

        //抽成明细回写订单主表
        $update_data = [
            'platform_choucheng' => isset($ptExpenditure) ? $ptExpenditure : 0.00,
            'shitang_choucheng' => isset($stExpenditure) ? $stExpenditure : 0.00,
            'hongbao_choucheng' => isset($hbExpenditure) ? $hbExpenditure : 0.00
        ];
        Db::name('Orders')->where('id',$order_id)->update($update_data);



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
        $refundData = model('Refund')->where('out_refund_no',$order_sn)->find();
        $data = [
            'withdraw_sn' => $refundData->out_trade_no,
            'shop_id' => $refundData->shop_id,
            'money' => $refundData->refund_fee,
            'type' => 6,
            'title' => '用户退款',
            'add_time' => time()
        ];

        $res = Db::name('withdraw')->insert($data);

        return $res;
    }

}