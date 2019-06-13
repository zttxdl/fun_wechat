<?php


namespace app\admin\controller;


use think\Controller;
use think\Request;

class Orders extends Controller
{
    public function getList(Request $request)
    {
        $page = $request->param('page');
        $page_size = $request->param('pageSize',20);

        /*$result = Model('orders')->alias('a')
            ->leftJoin('user b','a.user_id=b.id')
            ->field('a.orders_sn,b.nickname')
            ->select();*/

        $orederList = Model('Orders')->getOrderList($page,$page_size);

        $result = [];

        foreach ($orederList as $row)
        {
            $result[] = [
                'orders_sn' => $row['orders_sn'],
                'user_name' => Model('user')->getUserNameById($row['user_id']),
                'phone' => $row['phone'],
            ];
        }

        $this->success('获取成功',$result);
    }

    public function getDetail(Request $request)
    {
        $order_id = $request->param('id','','int');
        if(empty($order_id)) {
            $this->error('非法传参');
        }
        $orederDetail = Model('Orders')->getOrderDetail($order_id);

        $this->success('获取成功',$orederDetail);
    }
}
