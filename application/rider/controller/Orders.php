<?php

namespace app\rider\controller;

use think\Db;
use think\Request;
use app\common\controller\RiderBase;
use think\facade\Cache;

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
        $data = [];
        $type = $request->param('type');
        $rider_id = $this->auth->id;
        $school_id = $this->auth->school_id;
        if (!$type) {
            $this->error('非法参数');
        }
        $status_arr = model('RiderInfo')->where('id','=',$this->auth->id)->field('status,open_status')->find();

        
        if ($status_arr['status'] == 4) {
            $this->error('你账号已被禁用，无法接单',202);
        }
        
        $data['type'] = $status_arr['status'] == 0 ? 0 : 1;

		if ($status_arr['open_status'] == 2) {
			$this->error('你还没开工，无法接单',204);
		}

        $latitude = $request->param('latitude');
        $longitude = $request->param('longitude');
        if (!$latitude || !$longitude) {
            $this->error('坐标不能为空');
        }
        $location = $latitude.','.$longitude;

		if ($type == 1) {
            $hourse_ids = Db::name('RiderInfo')->where('id',$rider_id)->value('hourse_ids');
            $where[] = ['hourse_id','in',$hourse_ids];
            $where[] = ['school_id','=',$school_id];
            $where[] = ['status','=',1];
            $list = model('Takeout')
                ->field('order_id,ping_fee,meal_sn,shop_address,expected_time,status,user_address')
                ->where($where)
                ->order('create_time desc')
                ->select();
		}elseif($type == 2){
			//获取已接单
            $where[] = ['rider_id','=',$rider_id];
            $where[] = ['school_id','=',$school_id];
            $count = model('Takeout')->where($where)->where('status','in','3,4,5')->count();
            $data['count'] = $count;
            $where[] = ['status','in','3,4,5'];
            $list = model('Takeout')
                ->field('order_id,fetch_time,ping_fee,meal_sn,shop_address,expected_time,status,user_address')
                ->where($where)
                ->order('single_time desc')
                ->select();

            foreach ($list as $key => $item) {
                $item->rest_time = round(($item->expected_time - time()) / 60);
                if ($item->status == 3) {
                    $item->fetch_time = round(($item->fetch_time - time()) / 60);
                }
            }
		}else{
		    //获取已完成订单
            $where[] = ['rider_id','=',$rider_id];
            $where[] = ['school_id','=',$school_id];
            $count = model('Takeout')->where($where)->where('status','in','3,4,5')->count();
            $data['count'] = $count;
            $where[] = ['status','=','6'];
            $list = model('Takeout')
                ->field('order_id,accomplish_time,fetch_time,ping_fee,meal_sn,shop_address,expected_time,status,user_address')
                ->where($where)
                ->order('accomplish_time desc')
                ->select();
        }

        foreach ($list as $item) {
            if ($item->status != 6 && $item->status != 2) {
                $shop_address = $item->shop_address->latitude.','.$item->shop_address->longitude;
                $from = $location;
                $to = $shop_address;
                // 骑手到商家的距离
                $s_distance = one_to_more_distance($from,$to);
                if ($s_distance >= 100) {
                    $item->s_distance = round($s_distance / 1000,1).'km';
                }else{
                    $item->s_distance = $s_distance.'m';
                }
            }

            //已完成订单 送达时间超过两小时不展示用户联系方式
            if($item->status == 6 && (time() - $item->accomplish_time) > 7200) {
                $item->user_address->name = '';
                $item->user_address->phone = '';
            }

            $item->expected_time = date('H:i',$item->expected_time);

        }

        $hourse_ids = Db::name('RiderInfo')->where('id',$rider_id)->value('hourse_ids');

        $info = Db::name('Hourse')->field('id,fid,name')->where('id','in',$hourse_ids)->select();
        // $name_info = array_column($info,'name');

        foreach($info as $key => &$row){
            $fName = Db::name('Hourse')->where('id',$row['fid'])->value('name');
            $row['name'] = $fName.'-'.$row['name'];
        }
        unset($row);  // 加引用的for循环，循环执行完之后，建议手动清除引用

        $info = ltrim(implode(',',array_column($info,'name','fName')),'-');

        $data['list'] = $list;
        $data['info'] = $info;
		$this->success('success',$data);
    }
    

	/**
	 * 抢单
	 */
	public function grabSingle(Request $request)
	{
        $orderId = $request->param('order_id');

        $status = model('RiderInfo')->where('id','=',$this->auth->id)->value('status');

        if ($status == 0) {
            $this->error('您还未进行身份绑定，暂时不能抢单哦~',203);
        }
        if ($status == 1) {
            $this->error('身份绑定还在审核中，暂时不能抢单哦~',204);
        }
        if ($status == 2) {
            $this->error('身份绑定审核失败，暂时不能抢单哦~',205);
        }

        $result = model('Takeout')->where('order_id',$orderId)->field('school_id,status')->find();

        if ($result['status'] == 2){
            $this->error('该订单已取消');
        }

        if (in_array($result['status'],[3,4,5,6])){
            $this->error('手慢了，被人抢走了');
        }

        // 骑手两次超时15分钟未取餐，今日将不可接单
        $redis = Cache::store('redis');
        $key = "rider_overtime_number";
        if ($redis->hExists($key,$this->auth->id)) {
            $count = $redis->hGet($key,$this->auth->id);
            if ($count > 1) {
                $this->error('您今天存在多次超时未取餐状况，今天不可再抢单',206);
            }
        }

        // 当骑手目前存在五单以上的未完成订单，提示骑手暂时不可接单
        $count = Db::name('takeout')->where([['status','in',[3,4,5]],['rider_id','=',$this->auth->id]])->count('id');
        if ($count > 4) {
            $this->error('您目前的未完成订单量过多，目前不可再抢单，请优先配送未完成的订单',206);            
        }

        $time = model('School')->where('id',$result['school_id'])->value('fetch_time');
        $fetch_time = time() + 60 * $time;
        $data = [
            'rider_id'=>$this->auth->id,
            'status'=>3,
            'single_time'=>time(),
            'update_time'=>time(),
            'fetch_time'=> $fetch_time,
        ];
        Db::startTrans();
        try {
            $res = model('Takeout')->where('order_id',$orderId)->update($data);

            $res1 = model('Orders')->where('id',$orderId)->update(['status'=>5,'rider_id'=>$this->auth->id,'rider_receive_time'=>time()]);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
       
        if ($res && $res1) {
            // 抢单成功后，该学校的学生不应再看到该订单
            //实例化socket
            $socket = model('PushEvent','service');
            $where[] = ['school_id','=',$result['school_id']];
            $where[] = ['open_status','=',1];
            $where[] = ['status','=',3];
            $r_list = model('RiderInfo')->where($where)->select();

            foreach ($r_list as $item) {
                $rid = 'r'.$item->id;
                $socket->setUser($rid)->setContent('订单已被抢')->push();
            }

            $this->success('success');
        } else {
            $this->error('抢单失败');
        }

	}

    /**
     * 改变订单状态【此功能后续会删除，已将该方法拆分为多个方法】
     */
	// public function statusUpdate(Request $request)
    // {
    //     $type = $request->param('type');
    //     $orderId = $request->param('order_id');
    //     $latitude = $request->param('latitude');
    //     $longitude = $request->param('longitude');
    //     if (!$latitude || !$longitude) {
    //         $this->error('坐标不能为空');
    //     }

    //     $Order = \app\common\model\Orders::get($orderId);
    //     $Takeout = \app\common\model\Takeout::get(['order_id'=>$orderId]);
        
    //     $location = $latitude.','.$longitude;
    //     $shop_address = $Takeout->shop_address->latitude.','.$Takeout->shop_address->longitude;
    //     $user_address = $Takeout->user_address->latitude.','.$Takeout->user_address->longitude;
        
    //     if ($type == 1){//我已到店
    //         $result = parameters($location,$shop_address);
    //         if ($result[0]['elements'][0]['distance'] > 500) {
    //             $this->error('暂未到指定范围，还不可以点击哦');
    //         }
    //         $Order->status = 5;
    //         $Takeout->status = 4;
    //         $Takeout->toda_time = time();

    //     }elseif ($type == 2){//取餐离店
    //         $Order->status = 6;
    //         $Takeout->status = 5;
    //         $Order->send_time = time();
    //         // 判断当前订单是否已取餐离店
    //         $res = model('withdraw')->where([['withdraw_sn','=',$Order->orders_sn],['type','=',1]])->count();
    //         if ($res) {
    //             $this->error('您已取餐离店，请勿重新点击');
    //         }
    //         //取餐离店 计算商家收入、食堂收入
    //         $result = model('Withdraw')->income($orderId);
    //         if (!$result) {
    //             // 计算商家收入存表、食堂收入存表有误，造成写入回滚
    //             // 记录到异常订单中 【待更新。。。】
    //         }

    //     }elseif ($type ==3){//确认送达
    //         $result = parameters($location,$user_address);
    //         if ($result[0]['elements'][0]['distance'] > 300) {
    //             $this->error('暂未到指定范围，还不可以点击哦');
    //         }

    //         $Order->arrive_time = time();
    //         $Order->status = 7;
    //         $Takeout->status = 6;
    //         $Takeout->accomplish_time = time();
    //         $Takeout->update_time = time();

    //         //订单完成插入明细
    //         $data = [
    //             'rider_id' => $this->auth->id,
    //             'name' => $Takeout->shop_address->shop_name,
    //             'current_money' => $Takeout->ping_fee,
    //             'type' => 1,
    //             'serial_number' => $Order->orders_sn,
    //             'add_time' => time(),
    //         ];
    //         Db::name('rider_income_expend')->insert($data);
    //         $user = model('User')->field('phone,invitation_id')->where('id',$Order->user_id)->find();
    //         // 判断当前用户的订单数量【只要付款之后都算数量】
    //         $count = model('Orders')->where([['user_id','=',$Order->user_id],['status','notin',1]])->count('id');
    //         if ($count == 1 && $user->invitation_id){
    //             // 调用邀请红包
    //             $this->inviteGiving($user->invitation_id);
    //         }
            
    //         // 调用消费赠送红包
    //         $this->consumptionGiving($Order->user_id,$Takeout->school_id,$Order->money,$user->phone);
    //         // 调用添加商品销量
    //         $this->addProductSales($orderId,$Order->shop_id);
    //     }

    //     $Takeout->save();
    //     $Order->save();

    //     $this->success('success');
    // }

	/**
	 * 订单详情
	 */
	public function details(Request $request)
	{
        $orderId = $request->param('order_id');
        $latitude = $request->param('latitude');
        $longitude = $request->param('longitude');
        if (!$latitude || !$longitude) {
            $this->error('坐标不能为空');
        }

        $data = model('Takeout')
            ->field('order_id,ping_fee,meal_sn,single_time,shop_address,accomplish_time,expected_time,user_address,status,fetch_time,toda_time,cancel_desc,cancel_time')
            ->where('order_id',$orderId)->find();

        if ($data->status !== 6 && $data->status !== 2) {
                $location = $latitude.','.$longitude;
                $shop_address = $data->shop_address->latitude.','.$data->shop_address->longitude;
                $user_address = $data->user_address->latitude.','.$data->user_address->longitude;
                $from = $location.';'.$shop_address;
                $to = $shop_address.';'.$user_address;
                $result = parameters($from,$to);
                $s_distance = $result[0]['elements'][0]['distance'];
                
                if (in_array($data->status, [4,5])) {
                    $u_distance = $result[0]['elements'][1]['distance'];
                }else{
                    $u_distance = $result[1]['elements'][1]['distance'];
                }
                
                if ($s_distance >= 100) {
                    $data->s_distance = round($s_distance / 1000,1).'km';
                }else{
                    $data->s_distance = $s_distance.'m';
                }

                if ($u_distance >= 100) {
                    $data->u_distance = round($u_distance / 1000,1).'km';
                }else{
                    $data->u_distance = $u_distance.'m';
                }
        }

        //已完成订单 送达时间超过两小时不展示用户联系方式
        if($data->status == 6 && (time() - $data->accomplish_time) > 7200) {
            $data->user_address->name = '';
            $data->user_address->phone = '';
        }

        if (in_array($data->status,[3,4,5])){
            $data->rest_time = round(($data->expected_time - time()) / 60) ;
        }
        if ($data->status == 3) {
            $data->fetch_time = round(($data->fetch_time - time()) / 60);
        }
        $data->single_time = $data->single_time ? date('H:i',$data->single_time)  : '';
        $data->accomplish_time =  $data->accomplish_time ? date('H:i',$data->accomplish_time) : '';
        $data->cancel_time = $data->cancel_time ? date('H:i',$data->cancel_time) : '';
        $data->expected_time = $data->expected_time ? date('H:i',$data->expected_time) : '';
        $data->toda_time = $data->toda_time ? date('H:i',$data->toda_time) : '';

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
                $num = $item->other_time;
                $date = strtotime("+$num day");
                //执行逻辑
                $indate = date('Y.m.d',time()).'-'.date('Y.m.d',$date);
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
    protected function inviteGiving($invitation_id)
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
            $num = $data->other_time;
            $date = strtotime("+$num day");
            //执行逻辑
            $indate = date('Y.m.d',time()).'-'.date('Y.m.d',$date);
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
            model('Invitation')->insert(['referee_user_id'=>$invitation_id,'lucky_money'=>$data->face_value]);
        }

        return true;
    }

    /**
     * 商品售量
     */
    public function addProductSales($orderId,$shopId)
    {
        $list = model('OrdersInfo')->field('product_id,num')->where('orders_id',$orderId)->select();
        $data = [];
        foreach ($list as $key => $value) {
            $data[$key]['product_id'] = $value->product_id;
            $data[$key]['num'] = $value->num;
            $data[$key]['shop_id'] = $shopId;
            $data[$key]['create_time'] = time();
        }

        
        Db::name('product_sales')->insertAll($data);

        return true;
    }


    /**
     * 我已到店
     * 
     */
    public function arriveShop(Request $request)
    {
        $orderId = $request->param('order_id');
        $latitude = $request->param('latitude');
        $longitude = $request->param('longitude');
        if (!$latitude || !$longitude) {
            $this->error('坐标不能为空');
        }

        $Order = \app\common\model\Orders::get($orderId);
        $Takeout = \app\common\model\Takeout::get(['order_id'=>$orderId]);

        // 骑手超时未到商家取餐，订单将回滚到抢单状态
        if ($Takeout->status == 1) {
            $this->error('该订单已失效，请重新抢单');
        }
        
        $location = $latitude.','.$longitude;
        $shop_address = $Takeout->shop_address->latitude.','.$Takeout->shop_address->longitude;

        $result = parameters($location,$shop_address);
        if ($result[0]['elements'][0]['distance'] > 1000) {
            $this->error('暂未到指定范围，还不可以点击哦');
        }
        // 启动事务
        Db::startTrans();
        try {
            $Order->status = 5;
            $Takeout->status = 4;
            $Takeout->toda_time = time();

            $Takeout->save();
            $Order->save();
            // 提交事务
            Db::commit();
        } catch (\think\Exception\DbException $e) {
            // 回滚事务
            Db::rollback();
            $this->error('骑手已到店状态回写失败');            
        }
        $this->success('骑手已到店成功');
    }


    /**
     * 取餐离店
     * 
     */
    public function leaveShop(Request $request)
    {
        $orderId = $request->param('order_id');
        $Order = \app\common\model\Orders::get($orderId);
        $Takeout = \app\common\model\Takeout::get(['order_id'=>$orderId]);
        // 判断当前订单是否已取餐离店
        if ($Takeout->status == 5) {
            $this->error('您已取餐离店，请勿重新点击');
        }

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
            // 商品原价 = 订单总价 - 配送费 - 餐盒费 - 提价
            $original_money = $Order->total_money - $Order->ping_fee - $Order->box_money - $total_hike_price;
             /** 商家各种抽成支出*********************************/ 
            //平台抽成 = （商品原价 - 优惠金额）* 抽成比例 <==> 平台抽成 = （订单总价 - 配送费 - 餐盒费 - 提价  - 平台优惠 - 商家活动优惠）* 抽成比例 
            $ptExpenditure = ($original_money - $Order->platform_coupon_money - $Order->shop_discounts_money) * ($shop_info['segmentation'] / 100);

            //食堂抽成 = (商品原价 - 商家活动优惠) * 食堂抽成比例 《==》 （订单总价 - 配送费 - 餐盒费 - 提价 - 商家活动优惠）*食堂抽成比例
            $stExpenditure = 0;
            if ($cut_proportion) {
                $stExpenditure = ($original_money - $Order->shop_discounts_money) * ($cut_proportion / 100);
            }
            
            //红包抽成 = 红包总金额 * 商家承担比列
            $hbExpenditure = 0;
            if ($assume_ratio) {
                $hbExpenditure = $Order->platform_coupon_money * ($assume_ratio / 100);
            }

            // 总抽成
            $totalExpenditure = $ptExpenditure + $stExpenditure + $hbExpenditure;

            // 更新订单表【写入平台抽成、食堂抽成、红包抽成、订单状态】
            $Order->platform_choucheng = isset($ptExpenditure) ? $ptExpenditure : 0.00;
            $Order->shitang_choucheng = isset($stExpenditure) ? $stExpenditure : 0.00;
            $Order->hongbao_choucheng = isset($hbExpenditure) ? $hbExpenditure : 0.00;
            $Order->status = 6;
            $Order->send_time = time();
            $Order->save();

            // 更新外卖表【写入订单状态】
            $Takeout->status = 5;
            $Takeout->save();

            // 商家订单实际收入 = 商品原价 - 商家满减支出 - 抽成支出 《==》 订单总价 - 餐盒费 - 配送费 - 加价 - 抽成支出 - 商家满减支出
            $shop_money = $original_money - $totalExpenditure - $Order->shop_discounts_money;
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

            //统计店铺日订单量
            model('Shop')->setDayNum($Order->shop_id);
            // 提交事务
            Db::commit();
            
        } catch (\think\Exception\DbException $e) {
            // 回滚事务
            Db::rollback();
            $this->error('骑手取餐离店回写失败');            
        }
        $this->success('骑手取餐离店成功');
    }


    /**
     * 确认送达
     * 
     */
    public function confirmSend(Request $request)
    {
        $orderId = $request->param('order_id');
        // 去除经纬度
        // $latitude = $request->param('latitude');
        // $longitude = $request->param('longitude');
        // if (!$latitude || !$longitude) {
        //     $this->error('坐标不能为空');
        // }
        
        $Order = \app\common\model\Orders::get($orderId);
        $Takeout = \app\common\model\Takeout::get(['order_id'=>$orderId]);
        $user = model('User')->field('phone,invitation_id')->where('id',$Order->user_id)->find();
        // 判断当前订单是否已送达
        if ($Takeout->status == 6) {
            $this->error('您已送达，请勿重新点击');
        }
        // 去除经纬度
        // $location = $latitude.','.$longitude;
        // $user_address = $Takeout->user_address->latitude.','.$Takeout->user_address->longitude;
        // $result = parameters($location,$user_address);
        // if ($result[0]['elements'][0]['distance'] > 2000) {
        //     $this->error('暂未到指定范围，还不可以点击哦');
        // }
        // 启动事务
        Db::startTrans();
        try {
            // 更新订单表
            $Order->arrive_time = time();
            $Order->status = 7;
            $Order->save();
            
            // 更新外卖表
            $Takeout->status = 6;
            $Takeout->update_time = time();
            $Takeout->accomplish_time = time();
            $Takeout->save();

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
            // 判断当前用户的订单数量【只要付款之后都算数量】
            $count = model('Orders')->where([['user_id','=',$Order->user_id],['status','notin',1]])->count('id');
            if ($count == 1 && $user->invitation_id){
                // 调用邀请红包
                $this->inviteGiving($user->invitation_id);
            }
            
            // 调用消费赠送红包
            $this->consumptionGiving($Order->user_id,$Takeout->school_id,$Order->money,$user->phone);
            // 调用添加商品销量
            $this->addProductSales($orderId,$Order->shop_id);

            // 提交事务
            Db::commit();
        } catch (\think\Exception\DbException $e) {
            // 回滚事务
            Db::rollback();
            $this->error('确认送达回写失败');            
        }

        $this->success('确认送达');
    }

    /**
     * 选择楼栋列表
     */
    public function getHourseList()
    {
        $rider_id = $this->auth->id;
        $data = Db::name('RiderInfo')->field('hourse_ids,school_id')->where('id',$rider_id)->find();

        if(empty($data['hourse_ids'])) {
            $hourse_ids = [];
        }else{
            $hourse_ids = explode(',',$data['hourse_ids']);
        }
        $list = model('Hourse')->getHourseList($data['school_id']);

        foreach($list as &$row) {
            $row['isCheck'] = 0;
            if(in_array($row['id'],$hourse_ids)) {
                $row['isCheck'] = 1;
            }

            if(is_array($row['son'])) {
                foreach($row['son'] as &$v) {
                    $v['isCheck'] = 0;
                    if(in_array($v['id'],$hourse_ids)) {
                        $v['isCheck'] = 1;
                    }
                }
            }
        }
        $this->success('获取成功',$list);
    }

    /**
     * 保存楼栋设置(用于筛选订单))
     */
    public function save(Request $request)
    {
        $rider_id = $this->auth->id;
        $hourse_ids = $request->param('hourse_ids');

        $res = Db::name('RiderInfo')->where('id',$rider_id)->setField('hourse_ids','0,'.$hourse_ids);
        if($res !== false) {
            $this->success('保存成功');
        }else{
            $this->error('保存失败');
        }
        
    }






    
}






