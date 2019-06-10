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

    //邀请有奖
    public function index($id)
    {
        $data['mum'] = model('Invitation')->getUserCount($id);
        $data['money'] = model('Invitation')->getLuckyMoney($id);

        $this->success('success',$data);
    }

    //邀请
    public function share_wx()
    {

    }
}