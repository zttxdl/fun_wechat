<?php 
namespace app\Index\Controller;

use think\Controller;

use Predis\Client;
use app\common\service\PushEvent;
use think\facade\Cache;


class Index extends Controller
{



    //推送连接
	public function index($id)
	{
		return view('index/index',['uid'=>$id]);

	}

	/****** TP 自带缓存单元测试 ******/
	public function test2()
	{
	    $user_id = 23;
	    $shop_id = 2;
        $redis = Cache::store('redis');
        $key = "shop_uv_conut";


        //店铺访问用户
        $user_vistor = $redis->hGet($key,$shop_id);

        if($user_vistor) {
            $user_vistor = json_decode($user_vistor);
//            dump($user_vistor);exit;
        }else{
            $user_vistor = json_encode([1,2,3]);
        }

        if(!in_array($user_id,$user_vistor)) {
            //统计店铺的访客

            array_push($user_vistor,$user_id);

            $user_vistor = json_encode($user_vistor);

            if($redis->hExists($key,$shop_id)) {

                $redis->hSet($key, $shop_id, $user_vistor);
            }
        }else{
            return false;
        }

	}

	public function test3()
    {
        $key = 'user_list';
        $redis = Cache::store('redis');
    }



    //测试推送
	public function test()
	{
        $sid = request()->param('sid');
		$socket = model('PushEvent','service');
		$socket->setUser($sid)->setContent('新订单来了')->push();


	}


	// 查看PHPinfo
	public function phpinfo()
	{
	    phpinfo();
	}
	 


}
