<?php


namespace app\admin\validate;


class Hourse
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'fid'      => 'require|number',
        'name'      => 'require|max:30',
        'school_id'      => 'require',

    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'fid.require' => '上级楼栋名不能为空',
        'fid.number' => '上级楼栋ID必须为正整数',
        'name.require' => '楼栋名称不能为空',
        'name.max' => '楼栋名称不能超过30位',
        'school_id.require' => '学校ID必传',
    ];

    /**
     * 定义方法
     */
    protected $scene = [
        'addHourse'  =>  ['fid','name','school_id'],
        'updateHourse'  =>  ['fid','name'],
    ];
}