<?php

namespace app\rider\controller;

use think\Controller;

class ReceivingAddr extends Controller
{
    /**
     * 地址列表 
     * 
     */
    public function index($uid,$lat='',$lng='')
    {
        if ($lat == '' & $lng == ''){
            $list = model('ReceivingAddr')->getReceivingAddrList($uid);

        }else{
            $list = model('ReceivingAddr')->getReceivingAddrList($uid);
            foreach ($list as &$value) {
                $value['beyond'] = 0;
                $distance = pc_sphere_distance($lat,$lng,$value['latitude'],$value['longitude']);
                if ($$distance > 3000){
                    $value['beyond'] = 1;
                }
            }
        }


        return json_success('获取收货地址成功',['list'=>$list]);
    }

     
}
