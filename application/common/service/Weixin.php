<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/6
 * Time: 10:32 AM
 */

//php Pthread 实现多线程
class test extends Thread
{
	protected $isLogin = [];
	protected $instance;

	public static function __construct($instance = null)
	{
		$this->instance = $instance;
	}

	public static function getInstance($obj){
		if(!self::$instance) {
			self::$instance = $this->instance;
		}
		return self::$instance;
	}

	
}