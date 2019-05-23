<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/5/23
 * Time: 1:36 PM
 */
namespace  app\merchants\controller;

use think\Controller;
use app\common\model\ShopInfo;
use app\common\Auth\JwtAuth;
use think\Request;

class Merchants extends Controller
{
    /**
     * 新建商家
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function createShop(Request $request)
    {

        $data = $request->param();
        $data['shop_id'] = 1;
        $data['status'] = 3;
         $check = $this->validate($request->param(), 'Merchants');
         if ($check !== true) {
             return json_error($check);
         }

        model('ShopInfo')
        ->where('id',$data['shop_id'])
        ->update($data);

         $data = model('ShopMoreInfo')
             ->field('id')
             ->where('shop_id',$data['shop_id'])
             ->find();
         if ($data){
             model('ShopMoreInfo')
                 ->where('id',$data['shop_id'])
                 ->update($data);

         }else{
             model('ShopMoreInfo')->insert($data);

         }

        return json_success('success');

    }

}