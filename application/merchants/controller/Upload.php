<?php

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use think\Env;
use think\Request;

class Upload extends MerchantsBase
{
    protected $noNeedLogin = [];

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

        error_log(print_r($file,1),3,Env::get('root_path')."./logs/file.log");
        // 上传文件验证
        $result = $this->validate(['file' => $file], ['file'=>'require|image'],['file.require' => '请选择上传文件', 'file.image' => '非法图像文件']);
        if(true !== $result){
            $this->error($result);
        }
        // 移动到框架应用根目录 目录下
        $info = $file->move('./uploads/merchant/'.$this->shop_id);
        if ($info) {

            $data['images'] = '/uploads/merchant/'.$this->shop_id.'/'.$info->getSaveName();
            $this->success('文件上传成功',$data);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

    }


}
