<?php

namespace app\common\model;

use think\Model;

class CanteenIncomeExpend extends Model
{
	// 定义的 3 种状态
    const STATUS_FUNDING = 1;
    const STATUS_FAIL = 2;
    const STATUS_SUCCESS = 3;

    public static $statusMap = [
        self::STATUS_FUNDING   => '待审核',
        self::STATUS_FAIL => '提现失败',
        self::STATUS_SUCCESS => '提现成功',
    ];
}
