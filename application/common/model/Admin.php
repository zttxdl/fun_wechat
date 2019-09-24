<?php

namespace app\common\model;

use think\Model;

class Admin extends Model
{
    /**
     * 获取时间字符串格式 
     * 
     */
    public function getCreateTimeAttr($value)
    {
        return date('Y-m-d', $value);
    }

    /**
     * 获取时间字符串格式 
     * 
     */
    public function getLastLoginTimeAttr($value)
    {
        return date('Y-m-d H:i', $value);
    }
}
