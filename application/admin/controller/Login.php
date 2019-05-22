<?php


namespace app\admin\controller;


use traits\controller\Jump;

class Login
{
    use Jump;
    public function index()
    {
        $data = ['name'=>'123'];
        return $this->result($data);
    }
}