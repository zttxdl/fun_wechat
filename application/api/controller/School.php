<?php

namespace app\api\controller;

use app\common\model\School as SchoolModel;
use app\common\controller\ApiBase;
use think\Request;



class School extends ApiBase
{
    protected  $noNeedLogin = ['*'];


    /**
     * 学校地区
     *
     */
    public function index()
    {
        $model = new SchoolModel();

        $school_list = $model->getSchoolList();

        $this->success('获取学校地区列表成功',['school_list'=>$school_list]);
    }



    /**
     * 学校地区
     * 
     */
    public function schoolLevel2()
    {
        $model = new SchoolModel();

        $school_list = $model->getSchoolLevel2();

        $this->success('获取学校列表成功',['school_list'=>$school_list]);
    }


    /**
     * 用户端首页部分【选择学校】
     */
    public function chooseSchool(Request $request)
    {
        $model = new SchoolModel();
        $current_school_id = $request->param('school_id');

        // 学区学校三级关联列表
        $school_list = $model->getSchoolList();
        
        // 获取当前学校信息
        if ($current_school_id) {
            $current_school = $model->getSchoolInfoById($current_school_id);
        } else {
            // 获取第一个学校
            $current_school = $school_list[0]['children'][0];
        }

        $this->success('获取学校地区列表成功',['school_list'=>$school_list,'current_school'=>$current_school]);
    }

    /**
     * 获取学校楼号地址列表
     */
    public function getHourseList(Request $request)
    {
        $school_id = $request->param('school_id');
        if(empty($school_id)) $this->error('非法传参');
        $list = model('Hourse')->getHourseList($school_id);
        $this->success('获取成功',$list);
    }
    

    
}
