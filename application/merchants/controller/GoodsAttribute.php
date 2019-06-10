<?php

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use think\Request;
use app\common\model\ProductAttrClassify;

/**
 * 商品规格属性模块控制器
 */
class GoodsAttribute extends MerchantsBase
{
    protected $noNeedLogin = [];

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $data = ProductAttrClassify::all(['shop_id'=>$this->shop_id])->toArray();
        $data = $this->getSonCategory($data);
        $this->succes('success',$data);

    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data   = $request->param();
        $fid   = $request->param('pid');
        $data['shop_id'] = $this->shop_id;

        if ($fid){
            $count = model('ProductAttrClassify')->where('pid',$fid)->count();
            if ($count >= 3){
                $this->error('最多添加三个属性');
            }

        }
        $result = ProductAttrClassify::create($data);
        $this->succes('success');
    }


    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $result = ProductAttrClassify::get($id);
        if ($result->shop_id != $this->shop_id) {
            $this->error('没有权限删除');
        }

        $result = ProductAttrClassify::get(['pid'=>$id]);
        if ($result){
            $this->error('请先删除标签属性');
        }
        $result = ProductAttrClassify::destroy($id);
        $this->succes('success');
    }


}