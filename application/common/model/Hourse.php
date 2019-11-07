<?php 

namespace app\common\model;

use think\Model;
use think\Db;

class Hourse extends Model
{
    /**
     * 获取宿舍楼地址列表
     */
    public function getHourseList($school_id)
    {
        // 当前学校有楼栋二级数据的楼栋一级id值
        $ids = $this->where('fid','<>',0)->where('school_id','=',$school_id)->distinct(true)->column('fid');
        // 当前学校的所有楼栋一级列表
        $data = $this->where('id','in',$ids)->field('id,fid,name')->select()->toArray();

        // 当前学校的所有楼栋列表
        $list = $this->where('fid','<>',0)->where('school_id','=',$school_id)->field('id,fid,name')->select()->toArray();
        // 组装三维数组
        foreach ($data as $k => &$v) {
            $v['son'] = [];
            foreach ($list as $ko => $vo) {
                if ($v['id'] == $vo['fid']) {
                    $v['son'][] = $vo;
                }
            }
        }

        return $data;
    }

    /**
     * 根据HouseId获取宿舍楼名称
     */
    public function getNameById($id)
    {
        $name = Db::name('Hourse')->where('id',$id)->value('name');
        return isset($name) ? $name : '';
        
    }
}