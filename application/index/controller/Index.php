<?php 
namespace app\Index\Controller;

use think\Controller;

use GuzzleHttp\Client;
use app\common\service\PushEvent;
use think\facade\Cache;
use think\Request;
use think\Db;



class Index extends Controller
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
        $key = 'user_list';
        $redis = Cache::store('redis');
    }



    //测试推送
	public function push(PushEvent $push)
	{
	    $push->setUser(1)->setContent('你好啊1')->push();

	}


	// 查看PHPinfo
	public function phpinfo()
	{
        $rider = Db::name('rider_income_expend')
                ->alias('a')
                ->leftJoin('rider_info b','a.rider_id = b.id')
                ->field('a.current_money,a.serial_number,b.openid')
                ->where('a.id',$id)
                ->find();
            //连接微信企业打款 start
            $wx_tx = new Transfer();
            $res = $wx_tx->sendMoney($rider['current_money'],$rider['openid'],$rider['serial_number']);
            if ($res == '企业付款成功') {
                $this->success($res);
            }
            $this->error($res);
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
	 


}
