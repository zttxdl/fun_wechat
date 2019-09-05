<?php

namespace app\canteen\controller;

use app\common\controller\Base;
use app\common\model\CanteenIncomeExpend;
use think\Request;

class IncomeExpend extends Base
{
    /**
     * 提现记录
     */
    public function index(Request $request)
    {
        $pagesize = $request->get('pagesize',20);

        $list = CanteenIncomeExpend::scope(function ($query) {
            $query->where('type',2)
                ->order('id', 'desc');
        })->paginate($pagesize)->each(function ($item) {
            $item->add_time = date('Y-m-d H:i:s',$item->add_time);
            $item->status =  CanteenIncomeExpend::$statusMap[$item->status];
            $item->payment_time = $item->payment_time == 0 ? '-' : date('Y-m-d H:i:s',$item->payment_time);
        });
        
        $this->success('success',$list);
    }

    /**
     * 获取商家名称
     */
    public function selectShopName(Request $request)
    {
        $canteen_id = $request->get('canteen_id');
        $list = model('Shop')->field('id,shop_name')
                ->where('canteen_id',$canteen_id)
                ->order('id', 'desc')->select();
        
        $this->success('success',$list);
    }
}
