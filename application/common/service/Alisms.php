<?php
namespace app\common\service;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use think\facade\Cache;
use think\Model;


// 这里继承model的意义是，方便在控制器端，通过model('Alisms','service') 的方式进行调用， 其实完全可不继承model ，直接在控制器端通过 new Alisms() 的方式进行调用
class Alisms extends Model
{
    use  \app\common\controller\Jump;
    //定义短信模版
    protected function  getTemplateCode($type)
    {
        $arr = [
            'auth'=> 'SMS_168116283',
            'login'=> 'SMS_168116282',
            'register'=> 'SMS_168116280',
        ];

        return $arr[$type];
    }

    /**
     * 短信发送
     * @param $mobile 手机号
     * @param $type  类型
     * @return bool
     * @throws ClientException
     * @throws ServerException
     */
    public function sendCode($mobile,$type)
    {
        $cache_key      = 'alisms_' . $type . '_' . $mobile;
        $cache_send_key = 'Alisms/sendCode/' . $type . '/' . $mobile . '/send';

        if (Cache::store('redis')->get($cache_send_key)) {
            return true;
        } else {
            Cache::store('redis')->set($cache_send_key, true, 60);
            $code               = numRandCode(6);
            $cache_data['try']  = 6;
            $cache_data['code'] = $code;
            Cache::store('redis')
                ->set($cache_key, $cache_data, 300);
        }

        $accessKeyId = config('aliyun_sms')['accessKeyId'];
        $accessSecret = config('aliyun_sms')['accessSecret'];
        $SignName = config('aliyun_sms')['SignName'];
        $RegionId = config('aliyun_sms')['RegionId'];
        $TemplateCode = $this->getTemplateCode($type);

        AlibabaCloud::accessKeyClient($accessKeyId, $accessSecret)
            ->regionId('cn-hangzhou')
            ->asDefaultClient();

        try{
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'RegionId' => $RegionId,
                        'PhoneNumbers' => $mobile,
                        'SignName' => $SignName,
                        'TemplateCode' => $TemplateCode,
                        'TemplateParam' => "{\"code\":\"$code\"}",
                    ],
                ])
                ->request()->toArray();

            if ($result['Code'] == 'OK'){
                return true;
            }else{
                return false;
            }
        }catch (ClientException $e) {
             $this->error($e->getErrorMessage());
        } catch (ServerException $e) {
            $this->error($e->getErrorMessage());
        }


    }


    /**
     * 验证短信验证码
     * @param $mobile
     * @param $type
     * @param $code
     * @return bool
     */
    public function checkCode($mobile, $type, $code)
    {
        $cache_key  = 'alisms_' . $type . '_' . $mobile;
        $cache_data = Cache::store('redis')
            ->get($cache_key);
        if ($cache_data) {
            if ($cache_data['try'] > 0) {
                if ($cache_data['code'] == $code) {
                    Cache::store('redis')
                        ->rm($cache_key);

                    return true;
                } else {
                    $cache_data['try']--;
                }

                if ($cache_data['try'] == 0) {
                    $this->error = "请重新获取验证码！";
                    Cache::store('redis')
                        ->rm($cache_key);
                    return false;
                } else {
                    $this->error = "验证码有误，还有" . $cache_data['try'] . "机会！";
                    Cache::store('redis')
                        ->set($cache_key, $cache_data, 300);
                    return false;
                }
            }
        }

        $this->error = "请重新获取验证码！";

        return false;
    }

    public function showCode($mobile, $type)
    {
        $cache_key = 'alisms_' . $type . '_' . $mobile;

        return Cache::store('redis')
            ->get($cache_key);
    }

}

