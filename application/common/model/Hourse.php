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
        $list = Db::name('Hourse')->where('school_id',$school_id)->field('id,fid,name')->select();
        $data = Db::name('Hourse')->where([['fid','=','0'],['school_id','=',$school_id]])->field('id,fid,name')->select();
        // dump($list);
        foreach($data as $k => &$v) {
            $v['son'] = [];
            foreach($list as $kk => $vv) {
                if($v['id'] == $vv['fid']) {
                    $v['son'][] = $vv;
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