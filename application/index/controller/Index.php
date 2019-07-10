<?php 
namespace app\Index\Controller;

use think\Request;
use think\Controller;
use app\common\Libs\Redis;

class Index extends Controller{
	public function index(Request $request)
	{
		return "404";
		
	}

	public function test()
	{
		return view('index');
	}


}

 ?>