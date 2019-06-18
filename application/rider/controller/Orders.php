<?php

namespace app\rider\controller;

use think\Controller;
use think\Request;
use app\common\model\RiderInfo;
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
		$where[] = ['school_id','=',$this->auth->school_id];
		if ($type == 0) {
			//获取待接单
			
		}else{
			//获取已接单
		}

	}

	/**
	 * 抢单
	 */
	
	public function grabSingle()
	{

	}

	/**
	 * 订单详情
	 */
	public function details()
	{

	}

}






