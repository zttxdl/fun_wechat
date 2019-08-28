<?php

namespace app\common\model;

use think\Model;

class Agreement extends Model
{
    /**
     * 获取协议内容 
     * 
     */
    public function getAgreementContent($id)
    {
        $info = $this->where('id','=',$id)->field('id,title,content')->find();
        return $info;
    }
     
}
