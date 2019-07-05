<?php

namespace app\common\model;

use think\Model;

class Refund extends Model
{
    /**
     * 获取退款申请记录
     * @param $orders_sn
     */
    public function getRefundInfo($orders_sn)
    {
        return $this->where('out_trade_no',$orders_sn)->find();
    }
}
