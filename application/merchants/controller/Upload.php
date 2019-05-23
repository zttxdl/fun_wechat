<?php

namespace app\merchants\controller;

use think\Controller;
use think\Request;

class Upload extends Controller
{
    /**
     * 文件上传提交
     *
     * @return \think\Response
     */
    // 文件上传提交
    public function up(Request $request)
    {
        // 获取表单上传文件
        $file = $request->file('file');
        // 上传文件验证
        $result = $this->validate(['file' => $file], ['file'=>'require|image'],['file.require' => '请选择上传文件', 'file.image' => '非法图像文件']);
        if(true !== $result){
            return json_error($result);
        }
        // 移动到框架应用根目录 目录下
        $info = $file->move('./uploads/');
        if ($info) {

            $data['images'] = config('app_host').'/uploads/'.$info->getSaveName();
            return json_success('文件上传成功',$data['images']);
        } else {
            // 上传失败获取错误信息
            return json_error($file->getError());
        }

    }


}
