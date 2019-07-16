<?php 
namespace app\Index\Controller;

use think\Request;
use think\Controller;
use app\common\Libs\Redis;
use Predis\Client;
use app\common\service\PushEvent;

class Index extends Controller{

	/**
	 *  
	 * 
	 */
	public function index()
	{
		return view('index',['uid'=>6]);
	}
	 

	public function test()
	{
		// 向指定商家推送新订单消息
		$push = new PushEvent();
		$push->setUser(1)->setContent('您有新的校园外卖订单，请及时处理')->push();
	}


}

 ?>