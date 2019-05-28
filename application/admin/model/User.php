<?php


namespace app\admin\model;


use think\Model;

class User extends Model
{
    private $table_name = 'user';

    public function getUserConsume($uid)
    {
        Db::name($this->table_name)
    }
}