<?php 
namespace app\Index\Controller;

use think\Request;
use think\Controller;
use app\common\Libs\Redis;
use Predis\Client;
use think\facade\Cache; 

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
		// return view('index');
		phpinfo();
		// $redis = Cache::store('redis');
		// // dump($redis);
		// $redis->set('name','zhangtaotao');
		// $redis->get('name');

	}

	public function prepay(Request $Request)
	{
		$config = app($config);
		$user = Auth::user();
		$user_m = User::fund($user->id);
		$wx_user = $user_m->wxuser;
		$order_id = $request->input("order_id");
		$order = OrderMaster::find($order_id);
		$app = EasyWechat::payment();

		$result = [

		];

		$jssdk = $app->jssdk;
		$config = $jssdk->sdkConfig($result['prepay_id']);
		return $config;
	}

	/**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function($message){
            return "欢迎关注 overtrue！";
        });

        Log::info('return response.');

        return $wechat->server->serve();
    }


    /**
     * swoole http server服务
     *
     * @return string
     */
    public function http_server()
    {
    	$http = new \swoole_http_server("127.0.0.1",8888);

    	$http->on('start',function($server){
    		echo "Swoole the http server started at http://127.0.0.1:8888\n";
    	});

    	$http->on("request", function ($request, $response) {
    		$response->header("Content-Type", "text/plain");
    		$response->end("Hello World\n");
    	});

    	$http->start();

    }

}

 ?>