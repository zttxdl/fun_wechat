<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/6
 * Time: 7:55 PM
 */

namespace app\admin\controller;

use think\Request;
use think\Controller;


class Upload extends Controller
{
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
        $info = $file->move('./uploads/admin/'.$path);
        if ($info) {
            $data['images'] = '/uploads/admin/'.$path.'/'.$info->getSaveName();
            $this->success('文件上传成功',$data);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

    }
}