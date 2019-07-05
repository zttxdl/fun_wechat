<?php

namespace app\common\model;

use think\Model;

class ProductsClassify extends Model
{
    protected $autoWriteTimestamp = true;
    protected $insert             = [
        'status' => 1,
    ];

    
}
