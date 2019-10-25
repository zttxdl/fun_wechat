<?php
namespace app\common\service;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use think\facade\Cache;
use think\Model;


// 这里继承model的意义是，方便在控制器端，通过model('AliCall','service') 的方式进行调用， 其实完全可不继承model ，直接在控制器端通过 new Alisms() 的方式进行调用
class AliVms extends Model
{
    public function sendCall($phone)
    {
        $accessKeyId = config('aliyun_vms')['accessKeyId'];
        $accessSecret = config('aliyun_vms')['accessSecret'];
        $CalledShowNumber = config('aliyun_vms')['CalledShowNumber'];
        $TtsCode = config('aliyun_vms')['TtsCode'];
        AlibabaCloud::accessKeyClient($accessKeyId, $accessSecret)
                        ->regionId('cn-hangzhou')
                        ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                                ->product('Dyvmsapi')
                                // ->scheme('https') // https | http
                                ->version('2017-05-25')
                                ->action('SingleCallByTts')
                                ->method('POST')
                                ->host('dyvmsapi.aliyuncs.com')
                                ->options([
                                                'query' => [
                                                'RegionId' => "cn-hangzhou",
                                                'CalledShowNumber' => $CalledShowNumber,
                                                'CalledNumber' => $phone,
                                                'TtsCode' => $TtsCode,
                                                ],
                                            ])
                                ->request();
            print_r($result->toArray());
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }

}

