<?php
namespace app\index\controller;

use think\Request;


class Index
{
    public function index()
    {
        return 'hello word';
    }


    /**
     * 方法名称
     * @params $name 注释
     * return string
     */
    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }


    /**
     * 方法名称
     * @params $id 注释
     * return json
     */
    public function userInfo(Request $request)
    {
        // 代码块的单行注释
        $user_name = $request->input('post.name');
        if(!empty($user_name)){
            return $user_name;
        } else {
            return '游客';
        }

        // 另一个注释
        $user_info = db('user')->get(1);
        
        
    }

}
