<?php

namespace app\rider\controller;

use think\Controller;
use think\Request;
use app\common\controller\RiderBase;
use think\Db;

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
        $already_tx_money = model('RiderIncomeExpend')->getAlreadyTxMoney($this->auth->id);

        // 可提现金额
        $can_tx_money = model('RiderIncomeExpend')->getCanTxMoney($this->auth->id);

        // 未结算收入
        $not_tx_money = model('RiderIncomeExpend')->getNotTxMoney($this->auth->id);

        $this->success('获取我的钱包成功',['info'=>['already_tx_money'=>$already_tx_money,'can_tx_money'=>$can_tx_money,'not_tx_money'=>$not_tx_money]]);

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
        }

        $this->success('您的提现申请已提交');
        
    }

    
}
