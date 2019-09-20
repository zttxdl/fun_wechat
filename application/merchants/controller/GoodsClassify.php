<?php

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use app\common\model\Product;
use think\Request;
use app\common\model\ProductsClassify;
use think\Db;

/**
 * 商品分类模块控制器
 */
class GoodsClassify extends MerchantsBase
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
        $this->success('success',$data);

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
        ProductsClassify::create($data);

        $this->success('success');
    }


    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request)
    {
        $id = $request->param('id');
        if (!$id){
            $this->error('非法参数');
        }

        $data   = $request->param();
        ProductsClassify::update($data, ['id' => $id]);
        $this->success('success');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id){
            $this->error('非法参数');
        }
        $result = Product::get(['products_classify_id'=>$id,'status'=>1]);

        if ($result) {
            $this->error('该分类下有商品，请先删除商品');
        }

         ProductsClassify::destroy($id);

        $this->success('success');
    }

    
    /**
     * 更新分类排序 
     * 
     */
    public function classifySort(Request $request)
    {
        $sorts = $request->param('sorts');
        $data = json_decode($sorts,true);

        // 启动事务
        Db::startTrans();
        try {
            foreach ($data as $k => $v) {
                Db::name('products_classify')->update($v);
            }

             // 提交事务
             Db::commit();
             $this->success("分类排序更新成功");
         } catch (\think\Exception\DbException $e) {
             // 回滚事务
             Db::rollback();
             $this->error("分类排序更新失败");
         }
        $this->error('分类排序更新失败');

    }
}