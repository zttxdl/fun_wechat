<?php

namespace app\common\model;

use think\Model;

class School extends Model
{
    /**
     * 根据id 获取学校名称
     * @param $school_id
     * @return mixed
     */
    public function getSchoolNameById($school_id)
    {
        $res = $this->name('school')->field('name')->where('id',$school_id);
        return $res;
    }
}
