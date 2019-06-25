<?php

namespace app\merchants\controller;

use app\common\controller\MerchantsBase;
use think\Request;
use app\common\model\Product;
use app\common\model\TodayDeals;

/**
 * 商品模块控制器
 */
class Goods extends MerchantsBase
{
    protected $noNeedLogin = [];

    /**
     * 获取商家列表
     */
    public function index()
    {

        $where = ['shop_id'=>$this->shop_id];
        //获取商品
        $list = model('Product')
            ->field('id,name,price,old_price,attr_ids,thumb,sales,products_classify_id as classId,type')
            ->where($where)
            ->where('status',1)
            ->select()
            ->toArray();
        $cakes = [];
        $preferential = [];
        //获取热销商品
        foreach ($list as $item) {
            if ($item['type'] == 1){
                $cakes[] = $item;
            }elseif($item['type'] == 2){
                $preferential[] = $item;
            }
        }
        $data['cakes'] = $cakes;
        $data['preferential'] = $preferential;

        //获取分类
        $class = model('ProductsClassify')
            ->field('id as classId,name as className')
            ->where($where)
            ->select()
            ->toArray();

        foreach ($class as &$item) {
            $item['goods'] = [];

            foreach ($list as $value) {
                if ($item['classId'] == $value['classId']){
                    $item['goods'][] = $value;
                }
            }
        }
        $data['class'] = $class;


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
        $result = Product::create($data);

        $this->success('success',$result);
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
        $data   = $request->param();
        $result = Product::update($data, ['id' => $request->param('id')]);
        $this->success('success',$result);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $result = Product::get($id);
        
        if ($result->shop_id != $this->shop_id) {
            $this->error('没有权限删除');
        }

        $result = Product::destroy($id);
        $this->success('success',$result);
    }

    /**
     * 获取商品详情
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function detail($id)
    {
        $result = Product::get($id);
        $data = TodayDeals::get(['product_id'=>$id]);
        if ($data){
            $result->old_price = $data->old_price;
            $result->price = $data->price;
        }

        $this->success('success',$result);
    }

    /**
     * 获取下架商品
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function getOffProduct()
    {
        $result = model('Product')
           ->where('status',2)
           ->where('shop_id',$this->shop_id)
            ->select();

        $this->success('success',$result);
    }
}