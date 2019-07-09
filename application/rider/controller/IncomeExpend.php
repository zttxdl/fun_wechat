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

        // 提现金额【包括 申请提现、申请提现】
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
        // 判断提现金额【不能少于1元】
        if ($request->param('money') < 1) {
            $this->error('提现金额不能少于1元');            
        }

        // 计入缓存，每天只能提现一次  # 这块可写一脚本：每天凌晨清除当前缓存【在存缓冲的时候，设置缓存标签，即可指向性的清楚某一标签下的缓存Cache::clear('rider_tx');】
        $key = 'rider_tx_'.$this->auth->id;
        $check = Cache::store('redis')->tag('rider_tx')->get($key);  

        if($check){  
            $this->error('每天只能提现 1 次！',202);
        }

        // 优先读取缓存，当缓存过期时， 从数据库进行读取
        $can_money = Cache::store('redis')->get('rider_can_tx_money'.$this->auth->id);  
        if (!$can_money) {
            // 已结算收入
            $already_money = (string)model('RiderIncomeExpend')->getAlreadyJsMoney($this->auth->id);
            // 提现金额【包括 申请提现、申请提现】
            $tx_money = (string)model('RiderIncomeExpend')->getTxMoney($this->auth->id);
            // 可提现金额
            $can_money = $already_money - $tx_money;
        }

        // 判断当前的提现金额是否大于实际可提现的金额
        if($can_money < $request->param('money')){  
            $this->error('您的提现金额大于可提现金额！');
        }

        $data['current_money'] = $request->param('money');
        $data['rider_id'] = $this->auth->id;
        $data['type'] = 2;
        $data['name'] = '提现';
        $data['serial_number'] = build_order_no('TX');
        $data['add_time'] = time();
        $data['status'] = 1;

        $res = model('RiderIncomeExpend')::create($data);

        if (!$res) {
            $this->error('您的提现申请失败');
        } else {
            Cache::store('redis')->tag('rider_tx')->set($key,1,3600*24);
            $this->success('您的提现申请已提交');
        }
        
        
    }

    
}
