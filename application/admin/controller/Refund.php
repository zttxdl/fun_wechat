<?php


namespace app\admin\controller;


use app\common\controller\Base;
use think\Request;
use think\Db;

class Refund extends Base
{
    private $status = [
        '1' => '申请退款',
        '2' => '退款成功',
        '3' => '退款失败',
    ];
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
            $map[] = ['a.out_trade_no|a.out_refund_no|b.shop_name|c.nickname','like','%'.$search.'%'];
        }

        if($status) {
            $map[] = ['a.status','=',$status];
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

        foreach ($refundList['data'] as &$value)
        {
            $value['add_time'] = date('Y-m-d H:i:s',$value['add_time']);
            $value['status'] = $this->status[$value['status']];
        }


        $this->success('获取成功',$refundList);

    }

    /**
     * 退款详情
     */
    public function getDetail(Request $request)
    {
        $refund_id = $request->param('id');

        if(empty($refund_id)) {
            $this->error('非法传参');
        }

        $result = [];
        $list = Db::name('refund')
                ->field('out_refund_no,num,refund_fee,status,add_time,content,orders_id')
                ->where('id',$refund_id)
                ->find();
        if(!$list) {
            $this->error('暂无订单明细');
        }
        $list['add_time'] = date('Y-m-d H:i:s',$list['add_time']);
        $result['base_info'] = $list;

        $details = Db::name('orders_info')->alias('a')
                    ->leftJoin('product b','a.product_id = b.id')
                    ->field('a.product_id,b.name,a.num,a.attr_ids,b.products_classify_id,b.price')
                    ->where('a.id','=',$list['orders_id'])
                    ->select();
        foreach ($details as &$val)
        {
            $val['attr_ids'] = model('ProductAttrClassify')->getNameByIds($val['attr_ids']);
            $val['products_classify_id'] = model('ProductsClassify')->getNameById($val['products_classify_id']);
        }
        $result['goods_info'] = $details;

        $orders = model('Orders')->getOrderById($list['orders_id']);

        $result['order_info'] = [
            'orders_sn' => $orders['orders_sn'],
            'add_time' => date('Y-m-d H:i:s',$orders['add_time']),
            'pay_time' => date('Y-m-d H:i:s',$orders['pay_time']),
        ];

        $this->success('success',$result);

    }

}
