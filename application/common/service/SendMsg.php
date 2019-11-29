<?php
namespace app\common\service;

use think\Db;


/**
 * 模板消息推送
 * write by mike
 * data 2019/01/15
 */
class SendMsg
{
    
    /**
     * 骑手申请入驻的审核结果通知
     * @param $id  骑手信息表主键值
     */
    public static function passCheckSend($id)
    {
        // 获取app_id,app_secret参数
        $wx_rider = config('wx_rider');
        // 获取信息
        $riderInfo = Db::name('rider_info r')->join('school s','r.school_id = s.id')->where('r.id','=',$id)
                    ->field('r.name,r.openid,r.formid,r.overtime,r.status,s.name as school_name')->find();

        if ($riderInfo['status'] == 2) {
            $check = '您的申请未通过';
        }
        if ($riderInfo['status'] == 3) {
            $check = '您的申请已通过';
        }
        write_log('进来了','txt');
        write_log($riderInfo,'txt');
        // 判断form_id是否失效,当不失效的时候,去获取并推送
        if ($riderInfo['overtime'] > time()) {
            // 这是获取的小程序accessToken方法
            $accessToken = self::getWxAccessToken($wx_rider['app_id'], $wx_rider['secret']); 
            // 组装模板消息数据
            $postData = array(
                "touser" => $riderInfo['openid'],                            // 用户openid
                "template_id" => '42ftJgSgo5fXw7B22_EmXaTScUKCIKk6EdwH3tauo0w',      // 模板消息ID
                "page" => 'pages/my/my',                  // 跳转的页面
                "form_id" => $riderInfo['formid'],
                "data" => array(
                    'keyword1' => array('value' => $check),
                    'keyword2' => array('value' => '申请成为骑手'),
                    'keyword3' => array('value' => $riderInfo['name']),
                    'keyword4' => array('value' => $riderInfo['school_name']),
                    'keyword5' => array('value' => '南京食聚荟信息科技有限公司'),
                ),
                'emphasis_keyword' => 'keyword1'                // 选择要放大字体的键值
            );

            $postData = json_encode($postData); //这里是把postData转json ，不然入坑
            $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $accessToken;
            $rtn = self::http_curl($url, $postData);
            return $rtn;
        }
    }


    /**
     * 小程序获取accesstoken方法
     */
    public static function getWxAccessToken($appid,$appsecret)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $appsecret;
        $AccessToken = self::http_curl($url);
        $AccessToken = json_decode($AccessToken, true);
        $AccessToken = $AccessToken['access_token'];
        return $AccessToken;
    }


    /**
     * curl 模拟请求
     */
    public static function http_curl($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
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
