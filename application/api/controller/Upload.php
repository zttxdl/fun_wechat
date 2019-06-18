<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/6
 * Time: 7:55 PM
 */

namespace app\api\controller;


use app\common\controller\ApiBase;
use think\Request;
use think\Image;

class Upload extends ApiBase
{
    protected $noNeedLogin = [];

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
        $info = $file->move('./uploads/api/'.$path);
        if ($info) {

            // 此处功能为图片压缩功能，暂时不用【需下载 composer require topthink/think-image，同时在使用过程中，可能报错（Class 'think\Image' not found），处理办法有两个（网友提供，第一种方法暂未测试，第二种方法测试正常）：① 保证你的thinkphp5.1 框架是完整版的；②参考文档：https://blog.csdn.net/zzh_meng520/article/details/86140465】
                // $img_path = './uploads/api/'.$path;
                // $image = Image::open($img_path.$info->getSaveName());
                // // 按照原图的比例生成一个最大为300*300的缩略图并保存为thumb.png
                // $image->thumb(300,300,Image::THUMB_SCALING)->save($img_path.date('Ymd').'/thumb_'.$info->getFilename());

            $data['images'] = '/uploads/api/'.$path.'/'.$info->getSaveName();
            $this->success('文件上传成功',$data);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

    }
}