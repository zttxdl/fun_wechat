<?php

namespace app\common\model;

use think\Model;

class School extends Model
{
    /**
     * 学区学校关联列表 
     * 
     */
    public function getSchoolList()
    {
        // 学区列表
        $school_district_list = $this->field('id,name as label,name as value')->where('level',1)->select()->toArray();
        // 学校列表
        $school_list = $this->field('id,fid,name as label,name as value')->where('level',2)->select()->toArray();
        // 组装三维数组
        foreach ($school_district_list as $k => &$v) {
            foreach ($school_list as $ko => $vo) {
                if ($v['id'] == $vo['fid']) {
                    $v['children'][] = $vo;
                }
            }
        }

        return $school_district_list;

    }


    /**
     * 根据id 获取学校名称
     * @param $school_id
     * @return mixed
     */
    public function getNameById($school_id)
    {
       return  $this->where('id',$school_id)->value('name');

    }


    /**
     * 根据id 获取学校信息
     * @param $school_id
     * @return mixed
     */
    public function getSchoolInfoById($school_id)
    {
       return  $this->where('id',$school_id)->field('id，name')->find();

    }


    /**
     * 学校列表 
     * 
     */
    public function getSchoolLevel2()
    {
        // 学校列表
        $school_list = $this->field('id,fid,name as label,name as value')->where('level',2)->select()->toArray();

        return $school_list;

    }

    
}
