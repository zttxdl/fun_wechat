<?php
namespace app\common\service;

use think\Db;


/**
 * 信鸽消息推送
 * write by mike
 * data 2019/10/21
 */
class Xinge
{
    // push api url
    const URL = 'https://openapi.xg.qq.com/v3/push/app';

    // @var string 目标用户id
    protected $to_user;
    // @var string 消息类型
    protected $message_type;
    // @var string 安卓消息
    protected $android_msg;
    // @var string ios消息
    protected $ios_msg;
    protected $appid;
    protected $secretkey;

    /**
     * 单设备安卓消息推送【通知、透传】 
     */
    public function singleAndroidPush()
    {
        // 参数
        $data = [];
        $data['audience_type'] = 'token';
        $data['platform'] = $this->platform;
        // 消息内容
        $data['message'] = $this->android_msg;
        // 消息类型 【notify：通知，message：透传消息/静默消息】
        $data['message_type'] = $this->message_type;
        $data['token_list'] = $this->to_user;

        $jsonData = json_encode($data);
        
        $result = $this->http_curl(self::URL,$jsonData);
        return $result;

    }


    /**
     * 单设备ios消息推送【通知、静默】 
     */
    public function singleIosPush()
    {
        // 参数
        $data = [];
        $data['audience_type'] = 'token';
        $data['platform'] = $this->platform;
        // 消息类型 【notify：通知，message：透传消息/静默消息】
        $data['message_type'] = $this->message_type;
        $data['message']['ios'] = $this->ios_msg;
        $data['token_list'] = $this->to_user;
        
        $jsonData = json_encode($data);
        $result = $this->http_curl(self::URL,$jsonData);

        return $result;

    }
 

    /**
     * 设置推送用户，若参数留空则推送到所有在线用户
     *
     * @param string $user
     * @return $this
     */
    public function setUser($user, $platform)
    {
        $this->to_user = [$user];
        $this->platform = $platform;
        if ($platform == 'android') {
            $this->appid = config('xinge')['android']['appid'];
            $this->secretkey = config('xinge')['android']['secretkey'];
        } else {
            $this->appid = config('xinge')['ios']['appid'];
            $this->secretkey = config('xinge')['ios']['secretkey'];
        }
        return $this;
    }


    /**
     * 设置安卓推送内容【通知、透传】
     * 
     * @param string $message_type
     * @param string $title
     * @param string $content
     * @param string $order_sn
     */
    public function setAndroidContent($message_type, $title, $content, $order_sn = '')
    {
        $data = [];
        $data['title'] = $title;
        $data['content'] = $content;
        $data['android'] = $message_type == 'message' ? ['custom_content'=>['order_sn'=>$order_sn]] : null;

        $this->message_type = $message_type;
        $this->android_msg = $data;

        return $this;
    }


    /**
     * 设置ios推送内容【通知、静默】
     * 
     * @param string $message_type
     * @param string $title
     * @param string $content
     * @param string $order_sn
     */
    public function setIosContent($message_type, $title='', $content='',$order_sn='')
    {
        $data = [];
        if ($message_type == 'message') {
            $data['aps'] = ['content-available'=>1];
            $data['custom'] = ['order_sn'=>$order_sn];
        } else {
            $data['aps'] = ['alert'=>['title'=>$title,'content'=>$content]];
            $data['custom'] = null;
        }
        $this->message_type = $message_type;
        $this->ios_msg = $data;

        return $this;
    }
     
     


    /**
     * curl 模拟请求
     */
    public function http_curl($url, $data = null, $second=60)
    {
        $base64_auth_string = base64_encode($this->appid .':'.$this->secretkey);
        $header[] = "Content-type: application/json";
        $header[] = "Authorization: Basic ".$base64_auth_string;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, $second);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    
}
