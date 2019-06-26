<?php

namespace app\common\model;

use think\Model;

class Feedback extends Model
{
    /**
     * 获取时间字符串格式 
     * 
     */
    public function getAddTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }
     
    /**
     * 获取处理状态【方法很好用，但是前段需要status的原始数值，所以需要另写一个字段来标注】
     * 
     */
    // public function getStatusAttr($value)
    // {
    //     $status = ['1' => '未处理','2' => '已处理','3' => '不处理',];
    //     return $status[$value];
    // }
}
