<?php 
namespace app\Index\Controller;

use think\Controller;

class Index extends Controller
{


    //推送连接
	public function index($id)
	{
		return view('index/index',['uid'=>$id]);

	}

    //测试推送
	public function test($sid)
	{

		$socket = model('PushEvent','service');
		$socket->setUser($sid)->setContent('新订单来了')->push();

	}


}
