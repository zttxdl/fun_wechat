<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

define('TokenKey', 'jgdgskhew!$^#&jgjsf128skdhf');
 /**
 * 返回封装后的API成功方法
 * @access protected
 * @param  mixed     $data 要返回的数据
 * @param  integer   $code 返回的code
 * @param  mixed     $msg 提示信息
 * @return void
 */
if (!function_exists('json_success')) {
    function json_success($data, $msg = '',$code = 200)
    {
        $result = [
            'code' => $code,
            'data' => $data,
            'msg'  => $msg,
            'time' => $_SERVER['REQUEST_TIME'],
        ];

        return json($result);
    }
}

 /**
 * 返回封装后的API失败方法
 * @access protected
 * @param  integer   $code 返回的code
 * @param  mixed     $msg 提示信息
 * @return void
 */
if (!function_exists('json_error')) {
    function json_error($msg = '',$code = 201)
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => $_SERVER['REQUEST_TIME'],
        ];

        return json($result);
    }
}
