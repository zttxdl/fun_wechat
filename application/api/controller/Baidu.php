<?php


namespace app\api\controller;


use think\facade\Env;

class Baidu
{
    function request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        // 初始化curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $postUrl);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // post提交方式
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        // 运行curl
        $data = curl_exec($curl);
        curl_close($curl);

        return $data;
    }

    function getIdCard() {
        $token = '24.8f4cea9a3dd73ffda1413039e8791cab.2592000.1563623484.282335-16587563';
        $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/idcard?access_token=' . $token;
//        echo Env::get('think_path');exit;
        $img = file_get_contents(Env::get('think_path').'/1.jpg');
        $img = urlencode(base64_encode($img));
        $bodys = array(
            "image" => $img,
            "id_card_side" => 'front'
        );
        $res = $this->request_post($url, $bodys);

        return $res;
    }
}