<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/28
 * Time: 2:56 PM
 */

namespace app\common\service;


use think\Model;

class Email extends Model
{
    public function __construct($data = [])
    {

    }

    public function sendEmail($mail)
    {
        echo $mail;
    }
}