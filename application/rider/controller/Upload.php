<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/6
 * Time: 7:55 PM
 */

namespace app\rider\controller;


use app\common\controller\RiderBase;
use think\Request;

class Upload extends RiderBase
{
    protected $noNeedLogin = ['*'];

    /**
     * 文件上传提交
     *
     * @return \think\Response
     */
    public function upload(Request $request)
    {
        // 获取表单上传文件
        $file = $request->file('file');
        $path = $request->param('path');

        // 上传文件验证
        $result = $this->validate(['file' => $file], ['file'=>'require|image'],['file.require' => '请选择上传文件', 'file.image' => '非法图像文件']);
        if(true !== $result){
            $this->error($result);
        }
        
        // 移动到框架应用根目录 目录下
        $info = $file->move('./uploads/rider/'.$path);
        
        if ($info) {
            $img_url = '/uploads/rider/'.$path.'/'.$info->getSaveName();
            $this->success('文件上传成功',['img_url'=>$img_url]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

    }
}