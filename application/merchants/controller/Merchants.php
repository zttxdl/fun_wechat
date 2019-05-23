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
        $data      = $request->param();
    }
}