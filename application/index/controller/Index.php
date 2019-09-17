<?php 
namespace app\Index\Controller;

use app\common\controller\Base;

use GuzzleHttp\Client;
use app\common\service\PushEvent;
use think\facade\Cache;
use think\Request;
use think\Db;



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
        $seve = new Swoole\Server("127.0.0.1",9501);

        //监听
        $seve->on('Content',function ($serv, $fd){
            echo 'Client:Content.\n';
        });

        //监听数据接收事件

    }
	 


}
