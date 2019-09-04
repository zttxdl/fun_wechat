<?php

namespace app\common\controller;

use think\App;
use app\common\Auth\JwtAuth;



/**
 *食堂基类控制器
 */
class CanteenBase extends Base
{
    protected $noNeedLogin = [];
    protected $auth;
    protected $canteen_id;

    function __construct(App $app = null)
    {
        parent::__construct($app);

        $this->canteen_id = session('canteen.id');
    }

    

}
