<?php


namespace app\admin\controller;


use think\Controller;
use think\Request;

class Orders extends Controller
{
    public function getList(Request $request)
    {
        $page = $request->param('page');
        $page_size = $request->param('pageSize');
        $orederList = Model('Orders')->getOrderList($page,$page_size);

        $this->success('获取成功',$orederList);
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