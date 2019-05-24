<?php

namespace app\merchants\controller;

use think\Controller;
use think\Request;
use app\common\model\ProductsClassify;

/**
 * 商品分类模块控制器
 */
class GoodsClassify extends Controller
{
    protected $noNeedLogin = [];

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $data = ProductsClassify::all(['shop_id'=>$this->shop_id]);
        return json_success('success',$data);

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
        $data['shop_id'] = $this->shop_id;
        $result = ProductsClassify::create($data);

        return json_success('success',$result);
    }


    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data   = $request->param();
        $result = ProductsClassify::update($data, ['id' => $id]);
        return json_success('success',$result);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $result = ProductsClassify::destroy($id);
        return json_success('success',$result);
    }
}