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
    protected $noNeedLogin = ['*'];

    /**
     * 文件上传提交
     * @param Request $request
     * @throws \Exception
     */
    public function up(Request $request)
    {
        // 获取表单上传文件
        $file = $request->file('file');
        // 上传文件验证
        $result = $this->validate(['file' => $file], ['file'=>'require|image'],['file.require' => '请选择上传文件', 'file.image' => '非法图像文件']);
        if(true !== $result){
            $this->error($result);
        }
        // 要上传图片的本地路径
        $path = $file->getRealPath();
        $ext = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);//后缀
        // 上传到七牛后保存的文件名
        $key = substr(md5($file->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;;
        $ym = config('qiniu')['domain'];
        $accessKey = config('qiniu')['accesskey'];
        $secretKey = config('qiniu')['secretkey'];
        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
        // 要上传的空间
        $bucket = config('qiniu')['bucket'];
        $token = $auth->uploadToken($bucket);
        // 初始化 UploadManager 对象并进行文件的上传
        $upload = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传
        list($ret, $err) = $upload->putFile($token, $key, $path);
        if (!$err) {
            $data['images'] = $ym . '/' . $ret['key'].'?'.config('qiniu')['style'];
            $this->success('文件上传成功',$data);
        }else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

    public function delete(Request $request)
    {
        $delFileName = $request->param('name');

        if( $delFileName == null){
            $this->error('参数不正确');
        }

        // 构建鉴权对象
        $accessKey = config('qiniu')['accesskey'];
        $secretKey = config('qiniu')['secretkey'];
        $bucket = config('qiniu')['bucket'];
        $auth = new Auth($accessKey, $secretKey);

        // 配置
        $config = new Config();

        // 管理资源
        $bucketManager = new BucketManager($auth, $config);
        $imgstr = reset(explode('?',$delFileName));
        $img_url = substr($imgstr,29);
        // 删除文件操作
        $res = $bucketManager->delete($bucket, $img_url);

        if (is_null($res)) {
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

}
