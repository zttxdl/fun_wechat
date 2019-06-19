<?php

namespace app\rider\controller;

use think\Db;
use think\Request;
use app\common\controller\RiderBase;

/**
 * 骑手订单控制器
 * @author Billy
 * date 2019/6/17
 */
class Orders extends RiderBase
{
	protected $noNeedLogin = [];

	/**
	 * 获取订单列表
	 */
	public function index(Request $request)
	{
		$type = $request->param('type',0);

		if ($this->auth->status == 4) {
			$this->error('你账号已被禁用，无法接单');
		}

		if ($this->auth->open_status == 2) {
			$this->error('你还没开工，无法接单');
		}

        $where = [];
		if ($type == 1) {

            $where[] = ['school_id','=',$this->auth->school_id];
		    $where[] = ['type','=',1];

            $list = model('Takeout')
                ->field('order_id,ping_fee,meal_sn,shop_address,expected_time')
                ->where($where)->select();

            foreach ($list as $key => $item) {
                $order = model('Orders')->field('address,status')->where('id',$item->order_id)->find();
                $item->address = $order->address;
                $item->status = $order->status;
            }

		}else{
			//获取已接单
            $where[] = ['school_id','=',$this->auth->school_id];
            $where[] = ['type','=',2];
            $where[] = ['rider_id','=',$this->auth->id];

            $list = model('Takeout')
                ->field('order_id,ping_fee,meal_sn,shop_address,expected_time')
                ->where($where)->select();

            foreach ($list as $key => $item) {
                $order = model('Orders')->field('address,status')->where('id',$item->order_id)->find();
                $item->address = $order->address;
                $item->status = $order->status;
                $item->rest_time = round(($item->expected_time - time()) / 60);
            }
		}

		$this->success('success',$list);
	}

	/**
	 * 抢单
	 */
	
	public function grabSingle(Request $request)
	{
        $orderId = $request->param('order_id');

        $status = model('Orders')->where('id',$orderId)->value('status');

        if ($status !== 3){
            $this->error('手慢了，被人抢走了');
        }
        $data = [
            'rider_id'=>$this->auth->id,
            'type'=>2,
            'single_time'=>time(),
            'update_time'=>time(),
        ];
        model('Takeout')->where('order_id',$orderId)->update($data);

        model('Orders')->where('id',$orderId)->update(['status'=>5,'rider_receive_time'=>time()]);

        $this->success('success');

	}

    /**
     * 改变订单状态
     */
	public function statusUpdate(Request $request)
    {
        $type = $request->param('type');
        $orderId = $request->param('order_id');
        $Order = \app\common\model\Orders::get($orderId);
        $Takeout = \app\common\model\Takeout::get(['order_id'=>$orderId]);
        if ($type == 1){//我已到店
            $Order->status = 5;
            $Order->send_time = time();
        }elseif ($type == 2){
            $Order->status = 6;

        }elseif ($type ==3){
            $Order->arrive_time = time();
            $Order->status = 7;
            $Takeout->accomplish_time = time();
            $Takeout->update_time = time();
            $Takeout->type = 3;
            $Takeout->save();
        }

        $Order->save();

        $this->success('success');
    }

	/**
	 * 订单详情
	 */
	public function details(Request $request)
	{
        $orderId = $request->param('order_id');


        $data = Db::table('fun_takeout')
            ->alias('a')
            ->field('a.order_id,a.ping_fee,a.meal_sn,a.single_time,shop_address,a.accomplish_time,a.expected_time,b.address,b.status,b.trading_closed_time,b.send_time,b.cancel_desc')
            ->join('fun_orders b','a.order_id = b.id')
            ->where('a.order_id','=',$orderId)
            ->find();


        if ($data['status'] == 5 || $data['status'] == 6 || $data['status'] == 10  ){
            $data['rest_time'] = round(($data['expected_time'] - time()) / 60) ;
        }

        $data['single_time'] = $data['single_time'] ? date('H:i',$data['single_time'])  : '';
        $data['accomplish_time'] =  $data['accomplish_time'] ? date('H:i',$data['accomplish_time']) : '';
        $data['trading_closed_time'] = $data['trading_closed_time'] ? date('H:i',$data['trading_closed_time']) : '';
        $data['expected_time'] = $data['expected_time'] ? date('H:i',$data['expected_time']) : '';
        $data['send_time'] = $data['send_time'] ? date('H:i',$data['send_time']) : '';

        $this->success('success',$data);
    }

}






