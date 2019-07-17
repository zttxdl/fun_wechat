<?php 
namespace app\Index\Controller;

use think\Request;
use think\Controller;
use app\common\Libs\Redis;
use Predis\Client;

use think\facade\Cache; 

use app\common\service\PushEvent;


class Index extends Controller{


	/**
	 *  
	 * 
	 */
	public function index($id)
	{
		return view('index/index',['uid'=>$id]);

	}
	 

	public function test()
	{

		

	}


	public function testRedis()
	{

		// return view('index');
		phpinfo();
		// $redis = Cache::store('redis');
		// // dump($redis);
		// $redis->set('name','zhangtaotao');
		// $redis->get('name');

	}

}

 ?>