<?php 
namespace app\Index\Controller;

use think\Controller;

use GuzzleHttp\Client;
use app\common\service\PushEvent;
use think\facade\Cache;
use think\Request;



class Index extends Controller
{



    //推送连接
	public function index()
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


    public function http(Request $request)
    {
        $params = [
            'key' => 'b6a629321ab344778a7f1b896113a57d',
            'userid' => 'yemwishu'
        ];

        $params['info'] = $request->param('info');

        $client = new client();

        $options = json_encode($params, JSON_UNESCAPED_UNICODE);
        $data = [
            'body' => $options,
            'headers' => ['content-type' => 'application/json'],
        ];
        
        
        //发送post数据
        $response = $client->post('http://www.tuling123.com/openapi/api', $data);

        $callback = json_decode($response->getBody()->getContents());

        return $this->success('200','测试返回结果',$callback);
    }
	 


}
