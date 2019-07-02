<?php

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use think\facade\Env;
use think\Request;
use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class Upload extends MerchantsBase
{
    protected $noNeedLogin = ['upload'];

    /**
     * 文件上传提交
     *
     * @return \think\Response
     */
    // 文件上传提交
    // public function up(Request $request)
    // {
    //     // 获取表单上传文件
    //     $file = $request->file('file');

    //     error_log(print_r($file,1),3,Env::get('root_path')."./logs/file.log");
    //     // 上传文件验证
    //     $result = $this->validate(['file' => $file], ['file'=>'require|image'],['file.require' => '请选择上传文件', 'file.image' => '非法图像文件']);
    //     if(true !== $result){
    //         $this->error($result);
    //     }
    //     // 移动到框架应用根目录 目录下
    //     $info = $file->move('./uploads/merchant/'.$this->shop_id);
    //     if ($info) {

    //         $data['images'] = '/uploads/merchant/'.$this->shop_id.'/'.$info->getSaveName();
    //         $this->success('文件上传成功',$data);
    //     } else {
    //         // 上传失败获取错误信息
    //         $this->error($file->getError());
    //     }

    // }

    public function up(Request $request)
    {
        // 获取表单上传文件
        $file = $request->file('file');
        // 上传文件验证
        $result = $this->validate(['file' => $file], ['file'=>'require|image'],['file.require' => '请选择上传文件', 'file.image' => '非法图像文件']);
        if(true !== $result){
            $this->error($result);
        }

        $path = $file->getRealPath();
        $ext = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);
        $key = date('Ymd') . '/' . str_replace('.', '0', microtime(1)) . '.' . $ext;
        $ym = config('qiniu')['domain'];
        $accessKey = config('qiniu')['accesskey'];
        $secretKey = config('qiniu')['secretkey'];
        $auth = new Auth($accessKey, $secretKey);
        $bucket = config('qiniu')['bucket'];
        $token = $auth->uploadToken($bucket);
        $upload = new UploadManager();
        list($ret, $err) = $upload->putFile($token, $key, $path);
        if (!$err) {
            $data['path'] = $ym . '/' . $ret['key'].'?'.config('qiniu')['style'];
            $this->success('文件上传成功',$data);
        }else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

}
