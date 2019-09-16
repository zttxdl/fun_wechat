<?php

namespace app\test\controller;

use think\Controller;
use think\Request;
use think\Db;

// 测试服务器自动 git pull
class User extends Controller
{
    /**
     * 添加用户【测试】
     *
     */
    public function addUser(Request $request)
    {
        for ($i=0; $i <= 1000; $i++) { 
            $data = [
                'openid'    =>  "test_openid_".sprintf('%04d',$i),
                'nickname'  =>  'test_nickname_'.sprintf('%04d',$i),
                'headimgurl'=>  'https://thumbs.dreamstime.com/t/%E4%BA%BA%E5%8A%A8-%E7%89%87%E6%99%BA%E8%83%BD%E6%89%8B%E6%9C%BA%E6%A0%B7%E5%93%81%E6%B5%8B%E8%AF%95-83155177.jpg',
                'sex'   =>  '1',
                'phone' =>  '1888888'.sprintf('%04d',$i),
                'type'  =>  1,
                'add_time'  =>  time(),
                'new_buy'   =>  1
            ];

            Db::name('user')->insert($data);
        }
    }



    /**
     * 测试骑手新订单提醒 
     * 
     */
    public function riderOrder($school_id)
    {
        $map1 = [
            ['school_id', '=', $school_id],
            ['open_status', '=', 1],
            ['status', '=', 3]
        ];
        // 暂未成为骑手的情况
        $map2 = [
            ['school_id', '=', $school_id],
            ['status', 'in', [0,1,2]]
        ];  

        $r_list = model('RiderInfo')->whereOr([$map1, $map2])->fetchSql()->select();
        dump($r_list);
    }
     

}
