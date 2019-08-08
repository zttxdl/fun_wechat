<?php


namespace app\admin\controller;


use app\common\controller\Base;
use think\Request;
use think\Db;

class Refund extends Base
{
    /**
     * 退单列表
     * @param Request $request
     */
    public function getList(Request $request)
    {
        $page = $request->param('page');
        $page_size = $request->param('pageSize',10);
        $search = $request->param('keyword');//搜索条件

        $status = $request->param('status');

        $map = [];
        if($search) {
            $map[] = ['a.out_trade_no|a.out_refund_no|b.shop_name|c.nikename','like','%'.$search.'%'];
        }

        if($status) {
            $map[] = ['status','=',$status];
        }

        $refundList = Db::name('refund')
                        ->alias('a')
                        ->leftJoin('shop_info b','a.shop_id = b.id')
                        ->leftJoin('user c', 'a.user_id = c.id')
                        ->where($map)
                        ->field('a.id,a.out_trade_no,a.out_refund_no,b.shop_name,c.nickname,a.add_time,a.refund_fee,a.status')
                        ->order('id DESC')
                        ->paginate($page_size)
                        ->toArray();

        dump($refundList);

    }

    /**
     * 退款详情
     */
    public function getDetail(Request $request)
    {
        $order_id = $request->param('id');

        if(empty($order_id)) {
            $this->error('非法传参');
        }

        $result = [];
        $list = Db::name('refund')
                ->field('out_refund_no,num,refund_fee,status,add_time,content')
                ->where('order_id',$order_id)
                ->find();

        $details = Db::name('orders_info')->alias('a')
                    ->leftJoin('product b','a.product_id = b.id')
                    ->field('a.product_id,b.name,a.num,a.attr_ids,b.products_classify_id,b.price')
                    ->where('orders_id','=',$order_id)
                    ->select();



    }

}
