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

use \Firebase\JWT\JWT;
use think\facade\Cache; //导入JWT
use app\common\Libs\Redis;
use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;

/**
 * 返回封装后的API成功方法
 * @access protected
 * @param  mixed     $data 要返回的数据
 * @param  integer   $code 返回的code
 * @param  mixed     $msg 提示信息
 * @return void
 */
if (!function_exists('json_success')) {
    function json_success($msg = '', $data = '', $code = 200)
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
    function json_error($msg = '', $code = 201)
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
    function curl_post($url, $post_data = array())
    {
        if (empty($url)) {
            return false;
        }
        $curl_data = json_encode($post_data);

        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($curl_data)));
        $data = curl_exec($ch); //运行curl
        curl_close($ch);

        return $data;
    }
}


/**
 * 模拟 get 请求
 * @param $url 
 */
if (!function_exists('curl_get')) {
    function curl_get($url)
    {
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' ); 
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 20 ); // 表示如果服务器20秒内没有响应，脚本就会断开连接
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 ); // 表示如果服务器60秒内没有请求完成，脚本将会断开连接
        $result = curl_exec ( $ch );
        curl_close ( $ch );

        return $result;
    }
}


/**
 * 生成唯一订单号
 * @param $letter 订单号标识（举例说明：创建订单号 D ，退款订单号 R ，某种活动的订单号，例如团购订单号 T ，秒杀订单号 S ，等等，依据项目实际场景来区分）
 */
if (!function_exists('build_order_no')) {
    function build_order_no($letter = '')
    {
        return $letter . date('ymd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    }
}



/**
 * 计算两个经纬度之间距离的方法
 */
if (!function_exists('pc_sphere_distance')) {
    // 返回值的单位为米
    function pc_sphere_distance($lat1, $lon1, $lat2, $lon2, $radius = 6371000)
    {
        $rad = doubleval(M_PI / 180.0);
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
 * 物理地址解析经纬度 【因解析偏差过大作废，可适用于大概范围的经纬度获取场景】
 */
if (!function_exists('get_location')) {
    function get_location($address)
    {
        $key = config('lbs_map')['key'];
        // 仅学校地址信息，无法解析经纬度，目前需加上当前城市
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?address=" . $address . "&key=" . $key . "&region=南京";
        $jsondata = json_decode(file_get_contents($url), true);   // 尽量不要使用file_get_contents() ，建议使用curl模拟请求，curl请求更快
        $data = [];
        if ($jsondata['message'] == '查询无结果') {
            return $data;
        }
        $data['lat'] = $jsondata['result']['location']['lat'];
        $data['lng'] = $jsondata['result']['location']['lng'];

        return $data;
    }
}


/**
 * 百度地图物理地址解析经纬度 【因解析偏差过大作废，可适用于大概范围的经纬度获取场景】
 */

if (!function_exists('get_baidu_location')) {
    function get_baidu_location($address)
    {
        $result = array();
        $ak = '2DnX1SRN72z9rnLAGBSDvFEZvnhyVbZV'; //您的百度地图ak，可以去百度开发者中心去免费申请
        $url = "http://api.map.baidu.com/geocoding/v3/?address=" . $address . "&ret_coordtype=gcj02ll&output=json&ak=" . $ak;
        $data = file_get_contents($url);  // 尽量不要使用file_get_contents() ，建议使用curl模拟请求，curl请求更快
        $data = str_replace('renderOption&&renderOption(', '', $data);
        $data = str_replace(')', '', $data);
        $data = json_decode($data, true);
        if (!empty($data) && $data['status'] == 0) {
            $result['lat'] = $data['result']['location']['lat'];
            $result['lng'] = $data['result']['location']['lng'];
            return $result; //返回经纬度结果
        } else {
            return null;
        }
    }
}


/**
 * BD09 坐标转换GCJ02
 * 百度地图BD09坐标---->中国正常GCJ02坐标
 * 腾讯地图用的也是GCJ02坐标
 * @param double $lat 纬度
 * @param double $lng 经度
 * @return array();
 */
if (!function_exists('Convert_BD09_To_GCJ02')) {
    function Convert_BD09_To_GCJ02($lat, $lng)
    {
        $key = config('lbs_map')['key'];
        $url = "https://apis.map.qq.com/ws/coord/v1/translate?locations={$lat},{$lng}&type=3&key=" . $key;
        $jsondata = json_decode(curl_get($url), true);  // 尽量不要使用file_get_contents() ，建议使用curl模拟请求，curl请求更快
        if ($jsondata['status'] != 0) {
            return false;
        }
        $data['lat'] = $jsondata['locations'][0]['lat'];
        $data['lng'] = $jsondata['locations'][0]['lng'];

        return $data;
    }
}

/**
 * 距离计算【多点对多点】
 * @param double $from 起点坐标
 * @param double $lng 终点坐标
 * @return array();
 */
if (!function_exists('parameters')) {
    function parameters($from, $to)
    {
        $key = config('lbs_map')['key'];
        $url = "https://apis.map.qq.com/ws/distance/v1/matrix/?mode=bicycling&from={$from}&to={$to}&key=" . $key;
        $jsondata = json_decode(curl_get($url), true);  // 尽量不要使用file_get_contents() ，建议使用curl模拟请求，curl请求更快
        $data = $jsondata['result']['rows'];
        return $data;
    }
}


/**
 * 距离计算【一点对多点】
 * @param double $from 起点坐标
 * @param double $lng 终点坐标
 * @return string;
 */
if (!function_exists('one_to_more_distance')) {
    function one_to_more_distance($from, $to)
    {
        $key = config('lbs_map')['key'];
        $url = "https://apis.map.qq.com/ws/distance/v1/?mode=walking&from={$from}&to={$to}&key=" . $key;
        $jsondata = json_decode(curl_get($url), true);  // 尽量不要使用file_get_contents() ，建议使用curl模拟请求，curl请求更快
        $distance = $jsondata['result']['elements'][0]['distance'];
        return $distance;
    }
}


/**
 *  写日志
 * @param  $log  日志内容
 * @param string $type   日志后缀文件类型
 */
if (!function_exists('write_log')) {
    function write_log($log, $type = 'sql')
    {
        $request = Request::instance();
        $path = './log/';
        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            //无权创建文件忽略函数
            return false;
        }
        if (is_array($log)) {
            $log = json_encode($log);
        }
        $filename = $path . date("Ymd") . '_' . $type . ".log";
        @$handle = fopen($filename, "a+");
        @fwrite($handle, date('Y-m-d H:i:s') . " " . '[ip:' . $request->ip() . ']   ' . $log . "\r\n");
        @fclose($handle);
    }
}

/**
 * 清理缓存函数
 */
if (!function_exists('delete_dir_file')) {
    /**
     * 循环删除目录和文件
     * @param string $dir_name
     * @return bool
     */
    function delete_dir_file($dir_name)
    {
        $result = false;
        if (is_dir($dir_name)) {
            if ($handle = opendir($dir_name)) {
                while (false !== ($item = readdir($handle))) {
                    if ($item != '.' && $item != '..') {
                        if (is_dir($dir_name . DS . $item)) {
                            delete_dir_file($dir_name . DS . $item);
                        } else {
                            unlink($dir_name . DS . $item);
                        }
                    }
                }
                closedir($handle);
                if (rmdir($dir_name)) {
                    $result = true;
                }
            }
        }
        return $result;
    }
}

/**
 *  写日志
 * @param  $log  日志内容
 * @param string $type   日志后缀文件类型
 */
if (!function_exists('set_log')) {
    function set_log($param = '', $data, $type = '')
    {
        $path = Env::get('root_path') . "./logs/";
        if (!file_exists($path)) {

            mkdir($path, 0755);
        }
        error_log($param . print_r($data, 1), 3, $path . $type . date('Y-m-d') . ".log");
    }
}


if (!function_exists('createOrderSn')) {
    /**
     * 生成订单号
     *  -当天从1开始自增
     *  -订单号模样：20190604000001
     * @param Client $redis
     * @param $key
     * @param $back：序号回退，如果订单创建失败，事务回滚可用
     * @return string
     */
    function createOrderSn($key, $back = 0)
    {
        $sn = Cache::store('redis')->get($key); //redis读取，替换一下
        $snDate = substr($sn, 0, 8);
        $snNo = intval(substr($sn, 8));
        $curDate = date('Ymd');
        if ($back == 1) { //序号回退
            if ($curDate == $snDate) {
                $snNo = ($snNo > 1) ? ($snNo - 1) : 1;
                $sn = $curDate . sprintf("%06d", $snNo);
            }
        } else { //序号增加
            if (empty($sn)) {
                $sn = $curDate . '001';
            } else {
                $snNo = ($curDate == $snDate) ? ($snNo + 1) : 1;
                $sn = $curDate . sprintf("%06d", $snNo);
            }
        }
        Cache::store('redis')->set($key, $sn); //redis写入，替换一下
        return $snNo;
    }
}

if (!function_exists('getMealSn')) {
    /**
     * 获取商家取餐号
     * @param $shop_id
     * @param int $back
     */
    function getMealSn($shop_id)
    {
        $redis = Cache::store('redis');
        $key = 'shop_meal_sn';

        if ($redis->hExists($key, $shop_id)) {
            $redis->hIncrby($key, $shop_id, 1);
        } else {
            $redis->hSet($key, $shop_id, 1);
        }

        $sn = $redis->hGet($key, $shop_id);

        return $sn;
    }
}

/**
 * 获取Redis的静态实例
 */
if (!function_exists('redis')) {
    function redis()
    {
        $config = config('cache.redis');
        $redis = Redis::getInstance($config);
        return $redis;
    }
}


/**
 * 删除七牛云上的物理图片
 */
if (!function_exists('qiniu_img_del')) {
    function qiniu_img_del($imgurl)
    {
        // 构建鉴权对象
        $accessKey = config('qiniu')['accesskey'];
        $secretKey = config('qiniu')['secretkey'];
        $bucket = config('qiniu')['bucket'];
        $auth = new Auth($accessKey, $secretKey);

        // 配置
        $config = new Config();

        // 管理资源
        $bucketManager = new BucketManager($auth, $config);

        // 要删除的图片文件，与七牛云空间存在的文件名称相同， 即不能存在域名， 也不存在压缩的后缀
        // 数据库存储的图片路径为：http://picture.daigefan.com/6cfe8201907051641019024.png?imageView2/0/format/jpg/interlace/1/q/75|imageslim， 
        // 实际传到七牛云删除的路径为：6cfe8201907051641019024.png
        $imgstr = reset(explode('?', $imgurl));
        // 当图片域名换掉时，此处记得更改
        $img_url = substr($imgstr, 28);

        // 删除文件操作
        $res = $bucketManager->delete($bucket, $img_url);
        if (is_null($res)) {
            return true;
        } else {
            return false;
        }
    }
}


/**
 * 无限分类函数
 * @param   array  $data  传入的待处理数组
 * @param   int    $fid   分类初始级别
 * @return  array
 */
if (!function_exists('get_node')) {
    function get_node($data, $fid = 0)
    {
        static $result = [];
        foreach ($data as $key => $vo) {
            if ($vo['fid'] == $fid) {
                $result[] = $vo;
                get_node($data, $vo['id']);
            }
        }
        return $result;
    }
}


function conditions($time)
{
    $temp_time = json_decode($time, true); //转化成数组 ["2019-10-1","2019-10-2"] 
    // 计算两个日期之间的差值（多少天）
    $startdate = strtotime($temp_time[0]);
    $enddate = strtotime($temp_time[1]);
    $days = round(($enddate - $startdate) / 3600 / 24) + 1;

    // 封装数组
    $search_time[] = date('Y-m-d 00:00:00', strtotime($temp_time[0]));
    $search_time[] = date('Y-m-d 23:59:59', strtotime($temp_time[1]));
    for ($i = $days - 1; 0 <= $i; $i--) {
        $res[] = date('Y-m-d', strtotime('-' . $i . ' day', strtotime($temp_time[1])));
        $nums[] = 0;
    }

    $data = [
        'res' => $res,
        'nums' => $nums,
        'search_time' => $search_time,
        'temp_time' => $temp_time
    ];

    return $data;
}


function curl_post_json($url, $header, $content = '')
{
    $ch = curl_init();
    if (substr($url, 0, 5) == 'https') {
        // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 从证书中检查SSL加密算法是否存在
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    // 设置允许查看请求头信息
    // curl_setopt($ch,CURLINFO_HEADER_OUT,true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    $response = curl_exec($ch);
    // 查看请求头信息
    // dump(curl_getinfo($ch,CURLINFO_HEADER_OUT));
    if ($error = curl_error($ch)) {
        curl_close($ch);
        return $error;
    } else {
        curl_close($ch);
        return $response;
    }
}


/**
 * 打印机小票数据排版
 * @param string $orders_sn
 */
function get_order_info_print($orders_sn, $A, $B, $C, $D)
{
    // 获取订单数据信息
    $orderInfo = Model('Orders')->getOrder($orders_sn);
    $order['add_time'] = date('Y-m-d H:i:s', $orderInfo['add_time']);
    $order['message'] = $orderInfo['message'];
    $order['money'] = $orderInfo['money'];
    $order['orders_sn'] = $orderInfo['orders_sn'];
    $order['user_address'] = $orderInfo['address'];
    $order['shop_name'] = Db::name('shop_info')->where('id', '=', $orderInfo['shop_id'])->value('shop_name');
    $order['meal_sn'] = $orderInfo['meal_sn'];
    $order['ping_fee'] = $orderInfo['ping_fee'];
    $order['box_money'] = $orderInfo['box_money'];
    $order['dis_money'] = sprintf("%.2f", $orderInfo['platform_coupon_money'] + $orderInfo['shop_discounts_money']);
    $orderDetail = Model('Orders')->getOrderDetail($orderInfo['id']);

    foreach ($orderDetail as $row) {
        $data['name'] = Model('Product')->getNameById($row['product_id']);
        $data['attr_name'] = model('Shop')->getGoodsAttrName($row['attr_ids']);
        $data['num'] = $row['num'];
        $data['old_price'] = Model('Product')->getGoodsOldPrice('product_id');
        $data['price'] = $row['price'];
        $order['goods_detail'][] = $data;
    }

    // 组装小票数据排版
    $order_print_info = '<CB>饭点送 - 商家联 </CB><BR><BR>';
    $order_print_info .= '<B>取餐号：' . '# ' . $order['meal_sn'] . '</B><BR>';
    $order_print_info .= '===============================<BR>';
    $order_print_info .= '店铺名称：' . $order['shop_name'] . '<BR>';
    $order_print_info .= '订单编号：' . $orderInfo['orders_sn'] . '<BR>';
    $order_print_info .= '订餐时间：' . $order['add_time'] . '<BR><BR>';
    $order_print_info .= '----------- 商品信息 -----------<BR>';
    $order_print_info .= '名称           单价  数量 金额<BR>';
    $order_print_info .= '--------------------------------<BR>';
    foreach ($order['goods_detail'] as $k5 => $v5) {
        $name = $v5['name'];
        if (!empty($v5['attr_name'])) {
            $name = $v5['name'] . '【' . $v5['attr_name'] . '】';
        }
        $price = $v5['price'];
        $num = $v5['num'];
        $prices = $v5['price'] * $v5['num'];
        $kw3 = '';
        $kw1 = '';
        $kw2 = '';
        $kw4 = '';
        $str = $name;
        $blankNum = $A; //名称控制为14个字节
        $lan = mb_strlen($str, 'utf-8');
        $m = 0;
        $j = 1;
        $blankNum++;
        $result = array();
        if (strlen($price) < $B) {
            $k1 = $B - strlen($price);
            for ($q = 0; $q < $k1; $q++) {
                $kw1 .= ' ';
            }
            $price = $price . $kw1;
        }
        if (strlen($num) < $C) {
            $k2 = $C - strlen($num);
            for ($q = 0; $q < $k2; $q++) {
                $kw2 .= ' ';
            }
            $num = $num . $kw2;
        }
        if (strlen($prices) < $D) {
            $k3 = $D - strlen($prices);
            for ($q = 0; $q < $k3; $q++) {
                $kw4 .= ' ';
            }
            $prices = $prices . $kw4;
        }
        for ($i = 0; $i < $lan; $i++) {
            $new = mb_substr($str, $m, $j, 'utf-8');
            // echo $new.'<br>';
            $j++;
            
            if (mb_strwidth($new, 'utf-8') < $blankNum) {
                if ($m + $j > $lan) {
                    $m = $m + $j;
                    $tail = $new;
                    $lenght = iconv("UTF-8", "GBK//IGNORE", $new);
                    $k = $A - strlen($lenght);
                    for ($q = 0; $q < $k; $q++) {
                        $kw3 .= ' ';
                    }
                    if ($m == $j) {
                        $tail .= $kw3 . ' ' . $price . ' ' . $num . ' ' . $prices;
                    } else {
                        $tail .= $kw3 . '<BR>';
                    }
                    break;
                } else {
                    $next_new = mb_substr($str, $m, $j, 'utf-8');
                    if (mb_strwidth($next_new, 'utf-8') < $blankNum) continue;
                    else {
                        $m = $i + 1;
                        $result[] = $new;
                        $j = 1;
                    }
                }
            }
        }
        $head = '';
        foreach ($result as $key => $value) {
            if ($key < 1) {
                $v_lenght = iconv("UTF-8", "GBK//IGNORE", $value);
                $v_lenght = strlen($v_lenght);
                if ($v_lenght == 13) $value = $value . " ";
                $head .= '<L>'.$value . ' ' . $price . ' ' . $num . ' ' . $prices.'</L>';
            } else {
                $head .= '<L>'.$value .'</L>' . '<BR>';
            }
        }
        $order_print_info .= $head . '<L>'.$tail.'</L>';
    }
    

    // 费用明细
    $order_print_info .= '<BR><BR>';
    $order_print_info .= '----------- 费用明细 -----------<BR>';
    $order_print_info .= '餐盒费：                  ' . $order['box_money'] . '<BR>';
    $order_print_info .= '配送费：                  ' . $order['ping_fee'] . '<BR>';
    $order_print_info .= '优惠：                    ' . $order['dis_money'] . '<BR>';
    $order_print_info .= '--------------------------------<BR>';
    $order_print_info .= '实付：                    ' . $order['money'] . '<BR>';
    $order_print_info .= '--------------------------------<BR><BR>';
    $order_print_info .= '----------- 客户信息 -----------<BR>';
    $order_print_info .= '<L>姓名：' . $order['user_address']->name . '</L><BR>';
    $order_print_info .= '<L>电话：' . $order['user_address']->phone . '</L><BR>';
    $order_print_info .= '<L>地址：' . $order['user_address']->school_name . ' ' . $order['user_address']->area_detail . ' ' . $order['user_address']->house_number . '</L><BR>';
    $order_print_info .= '--------------------------------<BR>';
    if ($order['message']) {
        $order_print_info .= '<B>备注：' . $order['message'] . '</B><BR><BR><BR>';
    }
    // $order_print_info .= '<QR>http://www.feieyun.com</QR>';//把解析后的二维码生成的字符串用标签套上即可自动生成二维码


    return $order_print_info;
}
