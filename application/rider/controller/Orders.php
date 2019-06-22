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
		    $where[] = ['status','=',1];

            $list = model('Takeout')
                ->field('order_id,ping_fee,meal_sn,shop_address,expected_time,status,user_address')
                ->where($where)->select();

            $data['list'] = $list;
		}else{
			//获取已接单
            $where[] = ['school_id','=',$this->auth->school_id];
            $where[] = ['status','in','2,3,4,5'];
            $where[] = ['rider_id','=',$this->auth->id];

            $list = model('Takeout')
                ->field('order_id,ping_fee,meal_sn,shop_address,expected_time,status,user_address')
                ->where($where)->select();
            $count =  model('Takeout')->where($where)->count();
            foreach ($list as $key => $item) {
                $item->rest_time = round(($item->expected_time - time()) / 60);
            }

            $data['count'] = $count;
            $data['list'] = $list;
		}

		$this->success('success',$data);
	}

	/**
	 * 抢单
	 */
	
	public function grabSingle(Request $request)
	{
        $orderId = $request->param('order_id');

        $status = model('Takeout')->where('order_id',$orderId)->value('status');

        if ($status == 2){
            $this->error('该订单已取消');
        }

        if ($status !== 1){
            $this->error('手慢了，被人抢走了');
        }
        $data = [
            'rider_id'=>$this->auth->id,
            'status'=>3,
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
            $Takeout->status = 4;
            $Takeout->toda_time = time();

        }elseif ($type == 2){//取餐离店
            $Order->status = 6;
            $Takeout->status = 5;
            $Order->send_time = time();
        }elseif ($type ==3){//确认送达
            $Order->arrive_time = time();
            $Order->status = 7;
            $Takeout->status = 6;
            $Takeout->accomplish_time = time();
            $Takeout->update_time = time();

            //订单完成插入明细
            $data = [
                'rider_id' => $this->auth->id,
                'name' => $Takeout->shop_address->shop_name,
                'current_money' => $Takeout->ping_fee,
                'type' => 1,
                'serial_number' => $Order->orders_sn,
                'add_time' => time(),
            ];
            Db::name('rider_income_expend')->insert($data);
            $user = model('User')->field('phone,new_buy,invitation_id')->where('id',$Order->user_id)->find();
            if ($user->new_buy == 1 && $user->invitation_id){
                $this->inviteGiving($Order->user_id,$user->invitation_id);
            }

            $this->consumptionGiving($Order->user_id,$Takeout->school_id,$Order->money,$user->phone);

            $user->new_buy =2;
            $user->save();
        }

        $Takeout->save();
        $Order->save();

        $this->success('success');
    }

	/**
	 * 订单详情
	 */
	public function details(Request $request)
	{
        $orderId = $request->param('order_id');


        $data = model('Takeout')
            ->field('order_id,ping_fee,meal_sn,single_time,shop_address,accomplish_time,expected_time,user_address,status,toda_time,cancel_desc,cancel_time')
            ->where('order_id',$orderId)->find();

        if ($data['status'] == 3 || $data['status'] == 4 || $data['status'] == 5  ){
            $data['rest_time'] = round(($data['expected_time'] - time()) / 60) ;
        }

        $data['single_time'] = $data['single_time'] ? date('H:i',$data['single_time'])  : '';
        $data['accomplish_time'] =  $data['accomplish_time'] ? date('H:i',$data['accomplish_time']) : '';
        $data['cancel_time'] = $data['cancel_time'] ? date('H:i',$data['cancel_time']) : '';
        $data['expected_time'] = $data['expected_time'] ? date('H:i',$data['expected_time']) : '';
        $data['toda_time'] = $data['toda_time'] ? date('H:i',$data['toda_time']) : '';

        $this->success('success',$data);
    }

    /**
     * 消费赠送红包
     */
    protected function consumptionGiving($user_id,$school_id,$fee,$phone)
    {

        $where[] = [
            'type','=',3
        ];
        $where[] = [
            'school_id','=',$school_id
        ];
        $where[] = [
            'status','=',2
        ];

       $list =  model('PlatformCoupon')
           ->where($where)
           ->order('threshold','desc')
           ->select();

       foreach ($list as $item) {
            if ($fee > $item->threshold && $item->surplus_num > 0){
                $num = $item->indate;
                //执行逻辑
                $indate = date('Y-m-d',time()).'-'.date('Y-m-d',strtotime("$num + day"));
                $data = [
                    'user_id'=>$user_id,
                    'phone'=>$phone,
                    'platform_coupon_id'=>$item->id,
                    'indate'=>$indate,
                    'add_time'=>time(),
                ];
                model('MyCoupon')->insert($data);

                //处理红包减法
                model('PlatformCoupon')->where('id',$item->id)->setDec('surplus_num');
                break;
            }
       }

       return true;
    }

    /**
     * 邀请赠送红包
     */
    protected function inviteGiving($user_id,$invitation_id)
    {
        $where[] = [
            'type','=',4
        ];
        $where[] = [
            'status','=',2
        ];

        $data =  model('PlatformCoupon')
            ->where($where)
            ->find();
        if ($data->surplus_num >0 ){
            $num = $data->indate;
            //执行逻辑
            $indate = date('Y-m-d',time()).'-'.date('Y-m-d',strtotime("$num + day"));
            $phone = model('User')->where('id',$invitation_id)->value('phone');

            $datas = [
                'user_id'=>$invitation_id,
                'phone'=>$phone,
                'platform_coupon_id'=>$data->id,
                'indate'=>$indate,
                'add_time'=>time(),
            ];
            model('MyCoupon')->insert($datas);

            //处理红包减法
            model('PlatformCoupon')->where('id',$data->id)->setDec('surplus_num');

            //添加邀请人
            model('Invitation')->insert(['referee_user_id'=>$user_id,'lucky_money'=>$data->face_value]);
        }

        return true;
    }
}






