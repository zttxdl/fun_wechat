<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\common\controller\ApiBase;

/**
 * 我的红包控制器
 * @autor  mike 
 * date 2019-5-31
 */
class MyCoupon extends ApiBase
{
    

    /**
     * 我的红包列表
     * @param $uid  用户表主键值
     * @param $type  $type = 1，可用红包列表 否则为历史红包 
     * 
     */
    public function index(Request $request)
    {
        // 条件
        $type = $request->get('type');
        $type == 1 ? $where[] = ['m.status','=',1] : $where[] = ['m.status','in','2,3'];
        $where[] = ['m.id','=',$uid];
        
        $list = Db::name('my_coupon m')->join('platform_coupon p','m.platform_coupon_id = p.id')->where($where)
                ->field('m.phone,m.indate,m.status,p.face_value,p.threshold,p.type,p.name')->paginate(8);

        $this->success('获取红包列表成功',['list'=>$list]);
    }

     
     
}
