<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/6/3
 * Time: 5:57 PM
 */

namespace app\api\controller;

use app\common\controller\ApiBase;

class Invitation extends ApiBase
{
    protected $noNeedLogin = [];
    // 邀请有奖
    public function index()
    {
        $id = $this->auth->id;
        $info['num'] = model('Invitation')->getUserCount($id);
        $info['money'] = model('Invitation')->getLuckyMoney($id);

        $this->success('success',['info'=>$info]);
    }

}