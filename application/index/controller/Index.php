<?php 
namespace app\Index\Controller;

use think\Request;
use think\Controller;
use app\common\Libs\Redis;
use Predis\Client;

class Index extends Controller{
	public function index(Request $request)
	{
		$config = ['host'=>'127.0.0.1','port'=>'6379','db_id'=>0];
		
		$redis = new Client();

		$key = 'name';

		if($redis->exists($key)) {
			echo $redis->get('name');
			echo "<br>";
			echo $redis->ttl('name');
		}else{
			$redis->set('name',111);
			$redis->expire('name',60);

		}	
		
	}

	public function test()
	{
		return view('index');
	}


}

 ?>