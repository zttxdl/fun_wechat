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

use \Firebase\JWT\JWT; //导入JWT

 /**
 * 返回封装后的API成功方法
 * @access protected
 * @param  mixed     $data 要返回的数据
 * @param  integer   $code 返回的code
 * @param  mixed     $msg 提示信息
 * @return void
 */
if (!function_exists('json_success')) {
    function json_success($msg = '',$data='',$code = 200)
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

//核对手机号码
if (!function_exists('validate_mobile')) {
    function validate_mobile($mobile)
    {
        if (preg_match("/^1[3456789]{1}\d{9}$/", $mobile)) {
            return true;
        } else {
            return false;
        }
    }
}

//核对密码
if (!function_exists('validate_password')) {
    function validate_password($password)
    {
        if (preg_match("/^\w{8,20}\$/", $password)) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 随机数字
 */
if (!function_exists('numRandCode')) {
    function numRandCode($length = 6)
    {
        $str  = "0123456789";
        $code = "";
        for ($i = 0; $i < $length; $i++) {
            $start = rand(0, 9);
            $code .= substr($str, $start, 1);
        }
        return $code;
    }
}


/**
 * 模拟 post 请求
 * @param $url 
 */
if (!function_exists('curl_post')) {  
    function curl_post($url, $post_data = array()) {
        if (empty($url)) {
            return false;
        }
        $curl_data=json_encode($post_data);

        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' .strlen($curl_data)));
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        
        return $data;
    }
  }


/**
 * 生成唯一订单号
 * @param string $head  订单头部前缀（用于区分订单）
 */
if (!function_exists('build_order_no')) {  
    function build_order_no()
    {
        return date('YmdHis') . substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }
}


/**
 * 计算两个经纬度之间距离的方法
 */
if (!function_exists('pc_sphere_distance')) {
    // 返回值的单位为米
    function pc_sphere_distance($lat1, $lon1, $lat2, $lon2, $radius = 6371000) {
        $rad = doubleval(M_PI/180.0);
        $lat1 = doubleval($lat1) * $rad;
        $lon1 = doubleval($lon1) * $rad;
        $lat2 = doubleval($lat2) * $rad;
        $lon2 = doubleval($lon2) * $rad;
        $theta = $lon2 - $lon1;
        $dist = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($theta));
        return $dist * $radius * 1000;
    }
}


/**
 * 物理地址解析经纬度
 */
if (!function_exists('get_location')) {
    function get_location($address){
        $make_key = '5DNBZ-YEKC4-5HGUE-X7TP3-7W4F3-EWF3T';
        // 仅学校地址信息，无法解析经纬度，目前需加上当前城市
        $url="http://apis.map.qq.com/ws/geocoder/v1/?address=南京市".$address."&key=".$make_key;
        $jsondata=json_decode(file_get_contents($url),true);
        $data = [];
        if ($jsondata['message'] == '查询无结果') {
            return $data;
        }
        $data['lat'] = $jsondata['result']['location']['lat'];
        $data['lng'] = $jsondata['result']['location']['lng'];

        return $data;
    }
}


