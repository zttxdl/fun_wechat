<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/6
 * Time: 10:28 AM
 */

namespace app\api\controller;


use app\common\controller\ApiBase;
use think\App;


class Weixin extends ApiBase
{
    private $appid;
    private $appkey;

    public function __construct()
    {
        $wx_config = config('wx_pay');
        if($wx_config) {
            $this->appkey = $wx_config['key'];
            $this->appid = $wx_config['app_id'];
            $this->mchid = $wx_config['mch_id'];
        }

    }

    /**
     * 小程序支付
     */
    public function pay($data)
    {
        $return = $this->weixinapp($data);
        return $return;
    }

    /**
     * 支付查询
     */
    public function query()
    {

    }

    /**
     * 退款
     */
    public function refund()
    {

    }

    /**
     * 微信小程序接口
     * @param $data
     * @return array
     */
    private function  weixinapp($data){
        //统一下单接口
        $unifiedorder=$this->unifiedorder($data);
        $key = $this->appkey;
        $parameters=array(
            'appId'=>$this->appid,//小程序ID
            'timeStamp'=>''.time().'',//时间戳
            'nonceStr'=>$this->createNoncestr(),//随机串
            'package'=>'prepay_id='.$unifiedorder['prepay_id'],//数据包
            'signType'=>'MD5'//签名方式
        );
        //签名
        $parameters['paySign']=$this->getSign($parameters,$key);
        return $parameters;
    }

    /**
     * 统一下单接口
     * @param $data
     * @return mixed
     */
    private function unifiedorder($data){
        $key = $this->appkey;
        $url='https://api.mch.weixin.qq.com/pay/unifiedorder';
        $parameters=array(
            'appid'=>$this->appid,//小程序ID
            'mch_id'=>$this->mchid,//商户号
            'nonce_str'=>$this->createNoncestr(),//随机字符串
            'body'=>$data['body'],//商品描述
            'detail'=>$data['detail'],//商品详情
            'out_trade_no'=>$data['out_trade_no'],//商户订单号
            'total_fee'=>floatval($data['total_fee']*100),//总金额 单位 分
            'spbill_create_ip'=>$_SERVER['REMOTE_ADDR'],//终端IP
            'notify_url'=>'https' . "://" . $_SERVER['HTTP_HOST'].'/api/order/wxNotify',//通知地址
            'openid'=>$data['openid'],//用户id
            'trade_type'=>'JSAPI'//交易类型
        );
//        RpcLog::log("wx_pay_parameters:".$parameters['notify_url'], RpcLog::getMicroTime(), RpcLog::getMicroTime(), RpcLogEnvConfig::RPC_LOG_TYPE_MODULES);
        //统一下单签名
        $parameters['sign']=$this->getSign($parameters,$key);
        $xmlData=$this->arrayToXml($parameters);
        $return = $this->xmlToArray($this->postXmlCurl($xmlData, $url, 60));
        return $return;
    }

    /**
     * 作用：产生随机字符串，不长于32位
     * @param int $length
     * @return string
     */
    private function createNoncestr($length = 32 ){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ ) {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * 作用：生成签名
     * @param $Obj
     * @param $key
     * @return string
     */
    private function getSign($Obj,$key){
        foreach ($Obj as $k => $v){
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }

    /**
     * 作用：格式化参数，签名过程需要使用
     * @param $paraMap
     * @param $urlencode
     * @return bool|string
     */
    private function formatBizQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v){
            if($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0){
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     * 数组转成Xml
     * @param $parameters
     * @return string
     */
    private function arrayToXml($parameters){

        $xml = '<xml>
                   <appid>'.$parameters['appid'].'</appid>
                   <body>'.$parameters['body'].'</body>
                   <detail><![CDATA['.$parameters['detail'].']]></detail>
                   <mch_id>'.$parameters['mch_id'].'</mch_id>
                   <nonce_str>'.$parameters['nonce_str'].'</nonce_str>
                   <notify_url>'.$parameters['notify_url'].'</notify_url>
                   <openid>'.$parameters['openid'].'</openid>
                   <out_trade_no>'.$parameters['out_trade_no'].'</out_trade_no>
                   <spbill_create_ip>'.$parameters['spbill_create_ip'].'</spbill_create_ip>
                   <total_fee>'.$parameters['total_fee'].'</total_fee>
                   <trade_type>'.$parameters['trade_type'].'</trade_type>
                   <sign>'.$parameters['sign'].'</sign>
                </xml>';
        return $xml;
    }

    /**
     * xml转换成数组
     * @param $xml
     * @return mixed
     */
    private function xmlToArray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    /**
     * 请求Xml实体
     * @param $xml
     * @param $url
     * @param int $second
     * @return bool|string
     */
    private static function postXmlCurl($xml, $url, $second = 30) {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        set_time_limit(0);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }
}