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
    protected  $noNeedLogin = [];


    /**
     * 我的红包列表
     * @param $type  $type = 1，可用红包列表 否则为历史红包 
     * 
     */
    public function index(Request $request)
    {

        $uid = $this->auth->id;
        $shop_id = $request->param('shop_id');//店铺ID
        $category_id = $request->param('category_id');//品类ID

        // 条件
        $type = $request->get('type');
        $type == 1 ? $where[] = ['m.status','=',1] : $where[] = ['m.status','in','2,3'];
        $where[] = ['m.user_id','=',$uid];

        //是否可用
        $is_use = 1;
        //不可用原因
        $remark = '';
        $list = Db::name('my_coupon m')->leftJoin('platform_coupon p','m.platform_coupon_id = p.id')->where($where)
                ->field('p.id,m.phone,m.indate,m.status,p.face_value,p.threshold,p.type,p.name,p.limit_use,p.school_id,p.shop_ids')->select();


        $userInfo = model('user')->where('id',$uid)->find();


        foreach ($list as &$row) {
            $row['is_use'] = $is_use;
            $row['limit_use'] = explode(',',$row['limit_use']);
            $row['shop_ids'] = explode(',',$row['shop_ids']);
            //手机使用条件判断
            if($row['phone'] != $userInfo['phone']) {
                $row['is_use'] = 0;
                $row['remark'] = '仅限手机号'.$row['phone'].'使用';
            }elseif (in_array($shop_id,$row['shop_ids'])) {//店铺使用条件判断
                $row['is_use'] = 0;
                $row['remark'] = '仅限部分商家使用';
            }elseif (in_array($category_id,$row['limit_use'])) {//品类使用条件判断
                $row['is_use'] = 0;
                $row['remark'] = '仅限部分品类使用';
            }
            unset($row['limit_use']);
            unset($row['shop_ids']);
        }



        $this->success('获取红包列表成功',['list'=>$list]);
    }

     
     
}
