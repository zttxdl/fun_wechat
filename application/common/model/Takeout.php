<?php

namespace app\common\model;

use think\Model;

class Takeout extends Model
{
     // 设置json类型字段
    protected $json = ['user_address','shop_address'];
}