<?php

namespace app\rider\controller;

use think\Request;
use app\common\controller\RiderBase;
use think\Db;
use think\facade\Cache;

class IncomeExpend extends RiderBase
{
    protected  $noNeedLogin = [];

    /**
     * 我的钱包
     *
     * @return \think\Response
     */
    public function myWallet()
    {
        // 已结算收入
        $already_money = (string)model('RiderIncomeExpend')->getAlreadyJsMoney($this->auth->id);

        // 提现金额【包括 已提现、申请提现】
        $tx_money = (string)model('RiderIncomeExpend')->getTxMoney($this->auth->id);
        
        // 可提现金额
        $can_tx_money = $already_money - $tx_money;

        // 将可提现金额写入缓存， 方便在提现过程中的判断可提现金额
        $key = 'rider_can_tx_money'.$this->auth->id;
        Cache::store('redis')->set($key,$can_tx_money,600);  

        // 未结算收入
        $not_tx_money = model('RiderIncomeExpend')->getNotJsMoney($this->auth->id);

        $this->success('获取我的钱包成功',['info'=>['already_tx_money'=>$already_money,'can_tx_money'=>$can_tx_money,'not_tx_money'=>$not_tx_money]]);

    }


    /**
     * 收支明细列表
     *
     * @return \think\Response
     */
    public function detail(Request $request)
    {
        //搜索条件
        $where[] = ['rider_id','=',$this->auth->id];
        !empty($request->get('type/d')) ? $where[] = ['type','=',$request->get('type/d')] : null;
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 10;

        $list = Db::name('rider_income_expend')->where($where)->order('id desc')->field('name,add_time,current_money money,type,status')
                ->paginate($pagesize)->each(function ($item, $key) {
                    if ($item['type'] == 1) {
                        $item['money'] = '+'.$item['money'];
                    }
                    if ($item['type'] == 2 && $item['status'] == 1) {
                        $item['money'] = '-'.$item['money'].'（审核中）';
                    }
                    if ($item['type'] == 2 && $item['status'] == 2) {
                        $item['money'] = '-'.$item['money'];
                    }
                    $item['add_time'] = date('Y-m-d H:i:s',$item['add_time']);
                    return $item;
                });

        $this->success('获取明细成功',['list'=>$list]);
    }


    /**
     * 保存提现操作
     *
     * @return \think\Response
     */
    public function withdraw(Request $request)
    {
        // 判断当前骑手的所在学校的提现周期
        $info = Db::name('rider_withdraw_period')->where('school_id','=',$this->auth->school_id)->field('type,days')->find();
        if ($info['type'] == 2) {   // 当设定为每周的某一天提现时
            if (date('w') != $info['days']) {
                $this->error('每周'.config('rider_withdraw_period')[$info['days']].'为提现日！今天不可提现', 205);
            }
        }

        // 判断提现金额【不能少于1元】
        if ($request->param('money') < 1) {
            $this->error('提现金额不能少于1元');            
        }

        // 计入缓存，每天只能提现一次  # 这块可写一脚本：每天凌晨清除当前缓存【在存缓冲的时候，设置缓存标签，即可指向性的清楚某一标签下的缓存Cache::clear('rider_tx');】
        $rider_id = $this->auth->id;
        $redis = Cache::store('redis');
        $key = "rider_tx_num";
        if ($redis->hExists($key,$rider_id)) {
            $this->error('每天只能提现 1 次！',202); 
        }

        // 优先读取缓存，当缓存过期时， 从数据库进行读取
        $can_money = Cache::store('redis')->get('rider_can_tx_money'.$rider_id);  
        if (!$can_money) {
            // 已结算收入
            $already_money = (string)model('RiderIncomeExpend')->getAlreadyJsMoney($rider_id);
            // 提现金额【包括 已提现、申请提现】
            $tx_money = (string)model('RiderIncomeExpend')->getTxMoney($rider_id);
            // 可提现金额
            $can_money = $already_money - $tx_money;
        }

        // 判断当前的提现金额是否大于实际可提现的金额
        if($can_money < $request->param('money')){  
            $this->error('您的提现金额大于可提现金额！');
        }

        // 组装数据
        $data['current_money'] = $request->param('money');
        $data['rider_id'] = $rider_id;
        $data['type'] = 2;
        $data['name'] = '提现';
        $data['serial_number'] = build_order_no('TX');
        $data['add_time'] = time();
        $data['status'] = 1;

        // 存表
        $res = model('RiderIncomeExpend')::create($data);

        if (!$res) {
            $this->error('您的提现申请失败');
        } else {
            $redis->hSet($key,$rider_id,1);
            $this->success('您的提现申请已提交');
        }
        
        
    }

    
}
