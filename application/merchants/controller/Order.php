<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/3
 * Time: 2:37 AM
 */

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use think\Request;
use think\Db;

class Order extends MerchantsBase
{

    protected $noNeedLogin = [];

    /**
     * 店铺订单查询
     */
    public function show(Request $request)
    {
        $status = $request->param('status');//订单状态 2:新订单 3：处理中 9:已完成
        $page_no = $request->param('page');
        $page_size = config('page_size');

        if(!$status || !$page_no) {
            $this->error('非法传参');
        }

        $map = '';

        if($status) {
            $map = ['status' => $status];
        }

        $result = Db::name('orders')->where($map)->page($page_no,$page_size)->select();

        if(!$result) {
            return json_error('暂无订单');
        }

        $orders = [];
        foreach ($result as $row)
        {
            $orders = [
                'orders_sn' => $row['orders_sn'],
                'add_time' => date('Y-m-d H:i',$row['add_time']),
                'address' => $row['address'],
                'remark' => $row['message'],
                'ping_fee' => $row['ping_fee'],
                'money' => $row['money'],
                'detail' => $this->detail($row['id'])
            ];
        }


        $this->success('获取成功',$orders);

    }


    /**
     * 获取店铺订单详情
     */
    public function detail($id)
    {
        $detail = Db::name('Orders_info')->where('orders_id','=',$id)->select();
        return $detail;
    }

    /**
     * 店铺订单详情
     */
    public function orderDetail(Request $request)
    {

    }

    /**
     * 拒单 接单处理
     */
    public function receipt(Request $request)
    {
        $status = $request->param('status');//3:接单 4:拒单 7:确认送出
        $orders_sn = $request->param('orders_sn');



        $result = Db::name('Orders')->where('orders_sn',$orders_sn)->update(['status'=>$status]);

        if($result) {
            return json_success('处理成功');
        } else {
            return json_error('处理失败');
        }

        $this->success('获取成功',$result);
    }

    /**
     * 退款处理
     */
    public function refund()
    {

    }
}