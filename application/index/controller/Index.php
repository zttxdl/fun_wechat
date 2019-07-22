<?php 
namespace app\Index\Controller;

use think\Request;
use think\Controller;
use Predis\Client;
use app\common\service\PushEvent;
use think\facade\Cache;


class Index extends Controller{


	/**
	 *  
	 * 
	 */
	public function index($id)
	{
		return view('index/index',['uid'=>$id]);

	}
	 
	/****** Redis 实例单元测试 ******/
	public function test(){
		$shop_id = 1;

		//ttl测试
		redis()->set('zhangtaotao',$hop_id);
		// redis()->expire('zhangtaotao',600);
		dump(redis()->ttl('zhangtaotao'));
		dump(redis()->ttl('name'));
		
		redis()->hSet('shop_uv_conut',$shop_id,'1');
		dump(redis()->hGet('shop_uv_conut',$shop_id));
		echo redis()->hLen('shop_uv_conut',$shop_id);

		

		

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

}

 ?>