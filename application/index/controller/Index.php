<?php 
namespace app\Index\Controller;

use app\common\controller\Base;
use app\common\service\Getui;
use GuzzleHttp\Client;
use app\common\service\PushEvent;
use think\facade\Cache;
use think\Request;
use think\Db;
use JPush\Client as JPush;

class Index extends Base
{

    //提现时间规则当天可提现七天之前的结算金额
    protected $startTime;

    public function __construct()
    {
        parent::__construct();

        $this->startTime = date('Y-m-d',strtotime("-7 days")).'23:59:59';
    }

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
        $orders_sn = 'T190822453583016250';
        $res = model('Refund')->where('out_refund_no',$orders_sn)->setField('status',2);

        if(!$res) {
            echo '更新失败';
        }else{
            echo '更新成功';
        }


        var_dump($res);
    }



    //测试推送
	public function push($id,$content)
	{
        $push = new PushEvent();
        $ret = $push->setUser($id)->setContent($content)->push();
        $this->success('推送成功');
        

	}


	// 查看PHPinfo
	public function phpinfo()
	{
        phpinfo();
	}


    public function http(Request $request)
    {
        $params = [
            'UserName' => 'ztt2',
            'Password' => '123456'
        ];

        $params['info'] = $request->param('info');

        $client = new client();

        $options = json_encode($params, JSON_UNESCAPED_UNICODE);
        $data = [
            'body' => $options,
            'headers' => ['content-type' => 'application/json'],
        ];
        
        
        //发送post数据
        $response = $client->post('http://api.daigefan.io:8889/add', $data);

        $callback = json_decode($response->getBody()->getContents());

        return $this->success('200','测试返回结果',$callback);
    }

    public function swoole()
    {
        $http = new Swoole\Http\Server("127.0.0.1", 9501);
        $http->on('request', function ($request, $response) {
            $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
        });
        $http->start();

    }

    public function test()
    {
        // phpinfo();
        Cache::store('redis')->del('user_active_openid');
    }


    public function getui($type = 0, $cid = '833a1bdd7100799c5d3a31d30ac10856'){
        try{
            $Getui=new Getui();
            // 获取配置内容
            // $configInfo=Config::get('apiserver.notification_cid');
            // 拼接基本的content数组
            $content=[
                'title'=>'ttttt',
                'text'=>'ssssss',
                'logourl'=>'',
                'is_ring'=>true,
                'is_vibrate'=>true,
                'is_clearable'=>true,
            ];
            // type=0 说明只发送一个
            if($type==0){
                $res=$Getui->sendToClient($cid,$content,'');
            }else{
                // 多个cid都需要发送
                $res=$Getui->sendToListNotification($cid,$content,'');
            }
            return $res;
        }catch (\Exception $exception){
            $res=['res'=>$exception->getMessage(),'time'=>time()];
            return $res;
        }
    }


    /**
     *  
     * 
     */
    public function jiguang()
    {
        $app_key = 'fd8088ea1b361b77e0e83401';
        $master_secret = '3a892c08bbc625761968e0e2';
        $client = new JPush($app_key, $master_secret);
        // var_dump($client);
        $client->push()
            ->setPlatform('all')
            ->addAllAudience()
            // ->addRegistrationId('1507bfd3f7ac9b283b3') 
            // ->addRegistrationId('13065ffa4e6e0960368')   // 指定特定的用户推送
            // ->setNotificationAlert('Hello, 张涛涛')  // 推送内容
            ->addAndroidNotification('饭点送来新订单了','张涛涛提醒')  // 推送标题 + 内容
            ->send();
    }
     

	 


}
