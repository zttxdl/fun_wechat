<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/5/23
 * Time: 1:36 PM
 */
namespace  app\merchants\controller;

use think\Controller;
use app\common\Auth\JwtAuth;
use think\Request;

class Merchants extends Controller
{
    protected $shop_id;

    //前置操作，验证token
    protected $beforeActionList = [
        'valid_token',
    ];

    public function valid_token()
    {
        $token = $this->request->header('api-token','');
        $jwtAuth = new JwtAuth();
        $jwt = $jwtAuth->checkToken($token);
        $this->shop_id = substr($jwt['data'],9);
    }

    /**
     * 新建商家
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function createShop(Request $request)
    {
        $data = $request->param();
        $data['shop_id'] = $this->shop_id;
        $data['status'] = 3;
        $check = $this->validate($request->param(), 'Merchants');
        if ($check !== true) {
            return json_error($check);
        }

        model('ShopInfo')
        ->where('id',$data['shop_id'])
        ->update($data);

         $info = model('ShopMoreInfo')
             ->field('id')
             ->where('shop_id',$data['shop_id'])
             ->find();

         if ($info){
             model('ShopMoreInfo')
                 ->where('shop_id',$data['shop_id'])
                 ->update($data);

         }else{
             model('ShopMoreInfo')->insert($data);

         }

        return json_success('success');

    }

    /**
     * 获取学校
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function getSchool()
    {
        $data = model('School')->select();

        return json_success('success',$data);
    }

    /**
     * 获取经营品类
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function getCategory()
    {
        $data = model('ManageCategory')->select();

        return json_success('success',$data);
    }

    /**
     * 获取银行
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function getBack()
    {
        $data = model('Back')->select();

        return json_success('success',$data);
    }
}