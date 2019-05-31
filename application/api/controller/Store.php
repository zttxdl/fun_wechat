<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/5/30
 * Time: 4:27 PM
 */
namespace app\api\controller;

use app\common\controller\ApiBase;
use think\Request;

class Store extends ApiBase
{
    protected $noNeedLogin = ['*'];

    //获取商家详情-菜单
    public function index(Request $request)
    {
        $shop_id = $request->param('shop_id');

        $where = ['shop_id'=>$shop_id];
        //获取商品
        $list = model('Product')
            ->field('id,name,price,old_price,thumb,sales,products_classify_id,type')
            ->where($where)
            ->where('status',1)
            ->select()
            ->toArray();
        //获取分类
        $class = model('ProductsClassify')
            ->field('id as classId,name as className')
            ->where($where)
            ->select()
            ->toArray();

        foreach ($class as &$item) {
            $item['goods'] = [];
            foreach ($list as $value) {
                if ($item['id'] == $value['products_classify_id']){
                    $item['goods'][] = $value;
                }
            }
        }
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
        $data['class'] = $class;

        return json_success('success',$data);
    }
}