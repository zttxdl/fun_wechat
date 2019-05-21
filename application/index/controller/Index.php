<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return 'hello word';
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }

    public function demo()
    {
        return '11';
    }
}
