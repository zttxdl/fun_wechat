<?php 
namespace app\Index\Controller;

use app\common\controller\Base;
use app\common\service\FeieYun;
use app\common\service\Getui;
use GuzzleHttp\Client;
use app\common\service\PushEvent;
use app\common\service\Xinge;
use think\facade\Cache;
use think\Request;
use think\Db;
use JPush\Client as JPush;

class Index extends Base
{

    /**
     * print 
     * 
     */
    public function print()
    {
        $printer_sn = '550514925';
        $orders_sn = 'D191028418045234185';
        $orderInfo = get_order_info_print($orders_sn,14,6,3,6);
        $times = 1;
        $result = $this->feieyunPrint($printer_sn,$orderInfo,$times);

        return $result;

    }
     


    /**
     * 飞鹅云打印 
     * 
     */
    public function feieyunPrint($printer_sn,$orderInfo,$times)
    {
        $user = config('feieyun')['user'];
        $ukey = config('feieyun')['ukey'];
        $ip = config('feieyun')['ip'];
        $port = config('feieyun')['port'];
        $path = config('feieyun')['path'];

        $time = time();			    //请求时间
		$content = array(			
			'user'=>$user,
			'stime'=>$time,
			'sig'=>sha1($user.$ukey.$time),
			'apiname'=>'Open_printMsg',
			'sn'=>$printer_sn,
			'content'=>$orderInfo,
		    'times'=>$times//打印次数
		);
        
        $client = new FeieYun($ip,$port);
        if(!$client->post($path,$content)){
            return false;
        }
        else{
            //服务器返回的JSON字符串，建议要当做日志记录起来
            return $client->getContent();
        }
    }




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
     *  极光推送
     * ---新订单提醒【既要通知消息，也要透传/静默消息】
     * 
     */
    public function jgNewOrder(Request $request)
    {
        $app_key = config('jiguang')['appkey'];
        $master_secret =  config('jiguang')['master_secret'];
        // 获取商家设备信息
        $shopid = $request->param('shop_id');
        $device_sn = Db::name('jg_device')->where('shop_id','=',$shopid)->value('device_sn');

        $order_sn = 'D201909091212120000';
        $client = new JPush($app_key, $master_secret);
        $res = $client->push()
            ->setPlatform('all')
            ->addRegistrationId($device_sn) 
            ->message('新订单',['title' => '新订单提醒','content_type' => '需刷新订单页面','extras' => ['order_sn' => $order_sn]])
            ->setNotificationAlert('饭点送来新订单了，请及时处理！')  // 推送内容
            // ->options(array(
            //     // sendno: 表示推送序号，纯粹用来作为 API 调用标识，
            //     // API 返回时被原样返回，以方便 API 调用方匹配请求与返回
            //     // 这里设置为 100 仅作为示例
    
            //     // 'sendno' => 100,
    
            //     // time_to_live: 表示离线消息保留时长(秒)，
            //     // 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
            //     // 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
            //     // 这里设置为 1 仅作为示例
    
            //     // 'time_to_live' => 1,
    
            //     // apns_production: 表示APNs是否生产环境，
            //     // True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送开发环境
    
            //     'apns_production' => false,
    
            //     // big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
            //     // 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
            //     // 这里设置为 1 仅作为示例
    
            //     // 'big_push_duration' => 1
            // ))
            ->send();

            dump($res);
    }


    /**
     *  极光推送
     * ---用户在商家未接单时，取消订单，该订单则自动从商家新订单列表中消息【透传消息】
     * 
     */
    public function jgCancelOrder(Request $request)
    {
        $app_key = config('jiguang')['appkey'];
        $master_secret =  config('jiguang')['master_secret'];
        // 获取商家设备信息
        $shopid = $request->param('shop_id');
        $device_sn = Db::name('jg_device')->where('shop_id','=',$shopid)->value('device_sn');

        $order_sn = 'D201909091212121111';
        $client = new JPush($app_key, $master_secret);
        $res = $client->push()
            ->setPlatform('all')
            ->addRegistrationId($device_sn) 
            ->message('用户订单取消提醒：'. $order_sn.'已取消')
            ->send();

        // dump($res);
    }



    /**
     * 信鸽推送 
     * ---新订单提醒【既要通知消息，也要透传/静默消息】
     * 
     */
    public function newOrder(Request $request)
    {
        $xinge = new Xinge();
        // 获取商家设备信息
        $shopid = $request->param('shop_id');
        $info = Db::name('jg_device')->where('shop_id','=',$shopid)->field('device_type,device_sn')->find();
        $title = '新订单通知';
        $content = '您有一笔新的订单，请及时处理';
        $order_sn = 'D201909091212120010';
        // 消息通知
        if ($info['device_type'] == 'ios') {
            $notify_res = $xinge->setUser($info['device_sn'],'ios')
                                ->setIosContent('notify',$title,$content)
                                ->singleIosPush(); // 通知消息
            $message_res = $xinge->setUser($info['device_sn'],'ios')
                                ->setIosContent('message','','',$order_sn)
                                ->singleIosPush(); // 静默消息
        } else {
            $notify_res = $xinge->setUser($info['device_sn'],'android')
                                ->setAndroidContent('notify',$title,$content)
                                ->singleAndroidPush(); // 通知消息
            $message_res = $xinge->setUser($info['device_sn'],'android')
                                ->setAndroidContent('message',$title,$content,$order_sn)
                                ->singleAndroidPush(); // 透传消息
        }

        dump($notify_res);die;
        dump($message_res);die;

    }


    /**
     * 信鸽推送
     * ---用户在商家未接单时，取消订单，该订单则自动从商家新订单列表中消息【透传/静默消息】
     * 
     */
    public function cancelOrder(Request $request)
    {
        $xinge = new Xinge();
        // 获取商家设备信息
        $shopid = $request->param('shop_id');
        $info = Db::name('jg_device')->where('shop_id','=',$shopid)->field('device_type,device_sn')->find();
        $title = '订单取消通知';
        $content = '用户取消订单';
        $order_sn = 'D201909091212120010';

        if ($info['device_type'] == 'ios') {
            $message_res = $xinge->setUser($info['device_sn'],'ios')
                                ->setIosContent('message')
                                ->singleIosPush();            
        } else {
            $message_res = $xinge->setUser($info['device_sn'],'android')
                                ->setAndroidContent('message',$title,$content)
                                ->singleAndroidPush();
        }
        dump($message_res);die;
    }

     

    

	 


}
