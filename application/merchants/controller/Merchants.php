<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/5/23
 * Time: 1:36 PM
 */
namespace  app\merchants\controller;

use app\common\controller\MerchantsBase;
use think\Request;

class Merchants extends MerchantsBase
{

    protected $noNeedLogin = [];

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
        // 学区列表
        $school_district_list = model('School')->field('id,name')->where('level',1)->select()->toArray();
        // 学校列表
        $school_list = model('School')->field('id,fid,name')->where('level',2)->select()->toArray();
        // 组装三维数组
        foreach ($school_district_list as $k => &$v) {
            foreach ($school_list as $ko => $vo) {
                if ($v['id'] == $vo['fid']) {
                    $v['children'][] = $vo;
                }
            }
        }

        return json_success('success',$school_district_list);
    }

    /**
     * 获取经营品类
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function getCategory()
    {
        $data = model('ManageCategory')->field('id,name,img')->select();

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