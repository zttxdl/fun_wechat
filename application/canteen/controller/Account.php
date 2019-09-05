<?php

namespace app\canteen\controller;

use app\common\controller\Base;
use app\common\model\Canteen;
use app\common\model\CanteenAccount;
use app\common\model\CanteenIncomeExpend;
use think\Request;

class Account extends Base
{
    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $canteen_id = session('canteen.id');
        $data =  $request->post();
        // 验证表单数据
        $check = $this->validate($data, 'Account');
        if ($check !== true) {
            $this->error($check,201);
        }
        $find = CanteenAccount::get(['canteen_id'=>$canteen_id]);
        if ($find) {
            $this->error('添加失败',401);
        }
        $res = CanteenAccount::create($data);

        $this->success('success',$res);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read()
    {
        $canteen_id = session('canteen.id');
        $data = CanteenAccount::get(['canteen_id'=>$canteen_id]);
        $this->success('success',$data);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data =  $request->post();
        $res = CanteenAccount::update($data, ['id' => $id]);
        $this->success('success',$res);
    }

    /**
     * 获取账户资金
     */
    public function accountBalance()
    {
        $canteen_id = session('canteen.id');
        $canteen = Canteen::get($canteen_id);
        $income = $canteen->canteenIncomeExpend()->order('id','desc')->find();
        
        if (!$canteen) {
            $this->error('获取失败');
        }

        $data = [
            'account'=> $canteen->account,
            'can_balance'=> $canteen->can_balance,
            'balance'=> $income->balance,
        ];

        $this->success('success',$data);
    }

    /**
     * 提现
     */
    public function withdrawal(Request $request)
    {
        $canteen_id = session('canteen.id');
        $money = $request->post('money');
        $can_balance = model('Canteen')->where('id',$canteen_id)->value('can_balance');
        if ($money > $can_balance) {
            $this->error('提现金额不能大于可提现余额');
        }
        if ($money < 10) {
            $this->error('提现金额最低10元起');
        }
        $balance = model('CanteenIncomeExpend')->where('canteen_id',$canteen_id)->order('id','desc')->value('balance');
        $balance = $balance - $money;
        $data = [
            'canteen_id'=>$canteen_id,
            'name'=>'提现',
            'money'=>$money,
            'balance'=>$balance,
            'type'=>2,
            'serial_number'=> build_order_no('TX'),
            'add_time'=> time(),
            'status'=> 1,
        ];
        $ret = model('Canteen')->where('id',$canteen_id)->update(['can_balance'=>$can_balance - $money]);
        if (!$ret) {
            $this->error('提现失败');
        }
        $res = CanteenIncomeExpend::create($data);

        $this->success('success',$res);
    }
}
