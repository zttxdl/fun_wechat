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
     * @param $shop_id 店铺ID
     * @param string $startTime 多少天以前开始计算的时间
     * @return string
     * 获取账户余额【可提现金额】
     */
    public function getAcountMoney($shop_id)
    {
        // 提现规则 7天前 [在测试阶段，设置10分钟之前]
        $withdraw_cycle = Db::name('shop_info')->where('id',$shop_id)->value('withdraw_cycle');
        $startTime = date('Y-m-d',strtotime("-".$withdraw_cycle. "days")).'23:59:59';
        // $startTime = time()-600;
        //收入
        $shouru_money = $this->getIncome($shop_id,$startTime);

        //支出
        $zc_money = $this->getExpenditure($shop_id,$startTime);

        //账户余额等于收入-支出
        $acount_money = $shouru_money - $zc_money;

        return sprintf("%.2f",$acount_money);
    }


    /**
     * 未结算金额[7天内]  
     *
     */
    public function getNotJsMoney($shop_id)
    {
        // 未结算金额 7天内 [在测试阶段，设置10分钟内]
        $withdraw_cycle = Db::name('shop_info')->where('id',$shop_id)->value('withdraw_cycle');
        $startTime = date('Y-m-d',strtotime("-".$withdraw_cycle. "days")).'23:59:59';
        // $startTime = time()-600;

        // 七天之内的总收入
        $sr_money = $this->where([['shop_id','=',$shop_id],['type','=',1]])->whereTime('add_time', '>=',$startTime)->sum('money');

        // 七天之内的总支出[目前仅退款 3:活动支出 5:推广支出 6:退款]
        $zc_money = $this->where([['shop_id','=',$shop_id],['type','in','3,5,6']])->whereTime('add_time', '>=',$startTime)->sum('money');

        $not_js_money = $sr_money - $zc_money;

        return sprintf("%.2f",$not_js_money);
    }



    /**
     * 支出
     * @param $shop_id
     * @param $startTime 开始时间
     * @param string $endTime 结束时间
     * @return string
     */
    public function getExpenditure($shop_id,$startTime = '',$endTime = '')
    {
        if(empty($endTime)) {
            //提现过程中的金额【包括 `已提现`，`申请提现`】支出计算时间
            $tx_money = $this->where([['shop_id','=',$shop_id],['type','=',2],['status','in','1,3']])
                ->sum('money');

            //总支出 过滤提现
            $zc_money = $this->where([['shop_id','=',$shop_id],['type','in','3,5,6']])
                ->whereTime('add_time', '<',$startTime)
                ->sum('money');

        }else{
            //提现过程中的金额【包括 `已提现`，`申请提现`】
            $tx_money = $this->where([['shop_id','=',$shop_id],['type','=',2],['status','in','1,3']])
                ->whereBetweenTime('add_time',$startTime,$endTime)
                ->sum('money');

            //总支出 过滤提现
            $zc_money = $this->where([['shop_id','=',$shop_id],['type','in','3,5,6']])
                ->whereBetweenTime('add_time',$startTime,$endTime)
                ->sum('money');
        }
        return sprintf("%.2f",$tx_money + $zc_money);
    }


    /**
     * 收入
     * @param $shop_id
     * @param $startTime 开始时间
     * @param string $endTime 结束时间
     * @return string
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
     * mike 已调整
     * 计算商家收入和支出 【后期此方法将会被删除】
     * @param $order_id
     * @return bool
     */
    public function income($order_id)
    {
        // 获取当前订单信息
        $Order = \app\common\model\Orders::get($order_id);
        // 食堂主键值 + 平台抽成 + 平台对商家的提价金额
        $shop_info = Db::name('shop_info')->where('id',$Order->shop_id)->field('canteen_id,segmentation,price_hike,hike_type')->find();
        // 食堂抽成比例（百分制）
        $cut_proportion = '';
        if ($shop_info['canteen_id']) {
            $cut_proportion = Db::name('canteen')->where('id',$shop_info['canteen_id'])->value('cut_proportion');
        }
        // 平台红包抽成比例（百分制）
        $assume_ratio = '';
        if ($Order->platform_coupon_id) {
            $assume_ratio = Db::name('platform_coupon')->where('id',$Order->platform_coupon_id)->value('assume_ratio');
        }
        
        // 商品提价金额
        if ($shop_info['hike_type'] == 1) {
            $total_hike_price = $Order->num * $shop_info['price_hike'];
        } else {
            $total_hike_price = (($Order['total_money'] - $Order['box_money'] - $Order['ping_fee']) / (1 + $shop_info['price_hike'] * 0.01)) * $shop_info['price_hike'] * 0.01;
        }

        // 启动事务
        Db::startTrans();
        try {
            /** 商家各种抽成支出*********************************/ 
            //平台抽成 = （商品原价-优惠金额）* 抽成比例 <==> 平台抽成 = （订单总价 - 配送费 - 餐盒费 - 每个商品的提价*下单的商品数量  - 平台优惠 - 商家活动优惠）* 抽成比例 
            $ptExpenditure = ($Order->total_money - $Order->ping_fee - $Order->box_money - $total_hike_price - $Order->platform_coupon_money - $Order->shop_discounts_money) * ($shop_info['segmentation'] / 100);

            //食堂抽成 = （商品原价 - 商家活动优惠） * 食堂抽成比例 《==》 （订单总价 - 配送费 - 餐盒费 - 提价 - 商家活动优惠）*食堂抽成比例
            $stExpenditure = 0;
            if ($cut_proportion) {
                $stExpenditure = ($Order->total_money - $Order->ping_fee - $Order->box_money - $total_hike_price - $Order->shop_discounts_money) * ($cut_proportion / 100);
            }
            
            //红包抽成 = 红包总金额 * 商家承担比列
            $hbExpenditure = 0;
            if ($assume_ratio) {
                $hbExpenditure = $Order->platform_coupon_money * ($assume_ratio / 100);
            }

            // 总抽成
            $totalExpenditure = $ptExpenditure + $stExpenditure + $hbExpenditure;

            // 更新订单表【写入写入平台抽成、食堂抽成、红包抽成】
            $update_data = [
                'platform_choucheng' => isset($ptExpenditure) ? $ptExpenditure : 0.00,
                'shitang_choucheng' => isset($stExpenditure) ? $stExpenditure : 0.00,
                'hongbao_choucheng' => isset($hbExpenditure) ? $hbExpenditure : 0.00
            ];
            Db::name('Orders')->where('id',$order_id)->update($update_data);
            // 商家订单实际收入 = 商品原价 - 商家满减支出 - 抽成支出 《==》 订单总价 - 配送费 - 餐盒费 - 加价 - 抽成支出 - 商家满减支出
            $shop_money = $Order->total_money - $Order->box_money - $Order->ping_fee - $total_hike_price - $totalExpenditure - $Order->shop_discounts_money;
            /** 商家用户下单收入*********************************/ 
            $data = [
                'withdraw_sn' => $Order->orders_sn,
                'shop_id' => $Order->shop_id,
                // 商家实际订单收入
                'money' => sprintf('%.2f',$shop_money),
                'type' => 1,
                'title' => '用户下单',
                'add_time' => time()
            ];
            Db::name('withdraw')->insert($data);

            /** 食堂收入 *************************************/
            if ($shop_info['canteen_id']) {
                // 获取最新的食堂账户余额信息
                $balance = Db::name('canteen_income_expend')->where('canteen_id','=',$shop_info['canteen_id'])->order('id','desc')->value('balance');
                if (!$balance) {
                    $balance = 0;
                }
                $canteen = [
                    'canteen_id' => $shop_info['canteen_id'],
                    'name' => '收入',
                    'balance' => sprintf('%.2f',$stExpenditure + $balance),
                    'money' => sprintf('%.2f',$stExpenditure),
                    'type' => 1,
                    'serial_number' => $Order->orders_sn,
                    // 食堂收入
                    'add_time' => time()
                ];
                Db::name('canteen_income_expend')->insert($canteen);
            }
            // 提交事务
            Db::commit();
            return true;
        } catch (\think\Exception\DbException $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }

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
        // 退款表数据结果
        $refundData = model('Refund')->where('out_refund_no',$order_sn)->find();

        // 查看当前订单的商家实际收入
        $money = Db::name('withdraw')->where([['withdraw_sn','=',$refundData->out_trade_no],['type','=',1]])->value('money');
        $data = [
            'withdraw_sn' => $refundData->out_trade_no,
            'shop_id' => $refundData->shop_id,
            'money' => $money,
            'type' => 6,
            'title' => '用户退款',
            'add_time' => time()
        ];
        // 更新商家收支明细表
        $ret = Db::name('withdraw')->insert($data);

        $canteen_id = Db::name('shop_info')->where('id','=',$refundData->shop_id)->value('canteen_id');
        if ($canteen_id) {
            // 获取最新的食堂账户余额信息
            $balance = Db::name('canteen_income_expend')->where('canteen_id','=',$canteen_id)->order('id','desc')->value('balance');
            $money = Db::name('canteen_income_expend')->where([['serial_number','=',$refundData->out_trade_no],['type','=',1]])->value('money');

            $canteen = [
                'canteen_id' => $canteen_id,
                'name' => '退款',
                'balance' => sprintf('%.2f',$balance - $money),
                'money' => $money,
                'type' => 3,
                'serial_number' => $refundData->out_trade_no,
                // 食堂退款
                'add_time' => time()
            ];
            // 更新食堂收支明细表
            $ret2 = Db::name('canteen_income_expend')->insert($canteen);

            if($ret && $ret2) {
                return true;
            }else{
                return false;
            }
        }
        //不结算食堂抽成
        if($ret) {
            return true;
        }else{
            return false;
        }
        



    }

    /**
     * 获取店铺销售总额
     */
    public function getCountSales($shop_id)
    {
        //收入
        $total_money = $this->where([['shop_id','=',$shop_id],['type','=',1]])->sum('money');

        //退款支出
        $total_refund_money = $this->where([['shop_id','=',$shop_id],['type','=',6]])->sum('money');

        $data = $total_money - $total_refund_money;
        return sprintf("%.2f",$data);
    }


     /**
      * 获取店铺月销售额
      */
    public function getMonthSales($shop_id)
    {
        //收入
        $total_money = $this->where([['shop_id','=',$shop_id],['type','=',1]])->whereTime('add_time','month')->sum('money');

        //退款支出
        $total_refund_money = $this->where([['shop_id','=',$shop_id],['type','=',6]])->whereTime('add_time','month')->sum('money');

        $data = $total_money - $total_refund_money;
        return sprintf("%.2f",$data);
    }

    /**
     * @param $shop_id
     * 今日交易额
     */
    public function getDaySales($shop_id)
    {
        //收入
        $total_money = $this->where([['shop_id','=',$shop_id],['type','=',1]])->whereTime('add_time','today')->sum('money');

        //退款支出
        $total_refund_money = $this->where([['shop_id','=',$shop_id],['type','=',6]])->whereTime('add_time','today')->sum('money');

        $data = $total_money - $total_refund_money;
        return sprintf("%.2f",$data);
    }

    /**
     * @param $order_sn
     * 获取订单商家所得收入
     */
    public function getMoneyByOrderSn($order_sn)
    {
        $shop_money = $this->where(['withdraw_sn'=>$order_sn,'type'=>1])->value('money');
        return !empty($shop_money) ? $shop_money : '0.00';
    }





}