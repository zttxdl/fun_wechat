<?php

namespace app\common\Auth;

use \Firebase\JWT\JWT; //导入JWT


class JwtAuth
{

    /**
     * 头部 公共参数
     * @param array $header 头部参数数组
     * @param string $alg 声明签名算法为SHA256
     * @return string $typ 声明类型为jwt
     */
    private static $header=array(
        'alg'=>'HS256', //生成signature的算法 //声明签名算法为SHA256
        'typ'=>'JWT'  //声明类型为jwt
    );

    /**
     * 创建 token
     * @param array $data 必填 自定义参数数组
     * @param integer $exp_time 必填 token过期时间 单位:秒 例子：7200=2小时
     * @param string $scopes 选填 token标识，请求接口的token
     * @return string
     */
    public function createToken($data="",$exp_time=0,$scopes=""){

        //JWT标准规定的声明，但不是必须填写的；
        //iss: jwt签发者
        //sub: jwt所面向的用户
        //aud: 接收jwt的一方
        //exp: jwt的过期时间，过期时间必须要大于签发时间
        //nbf: 定义在什么时间之前，某个时间点后才能访问
        //iat: jwt的签发时间
        //jti: jwt的唯一身份标识，主要用来作为一次性token。
        //公用信息
            $key=config('token_key');
            $time = time(); //当前时间
            $token['iat']=$time; //签发时间
            $token['nbf']=$time; //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
            if($scopes){
                $token['scopes']=$scopes; //token标识，请求接口的token
            }
            if(!$exp_time){
                $exp_time=7200;//默认=2小时过期
            }
            $token['exp']=$time+$exp_time; //token过期时间,这里设置2个小时
            if($data){
                $token['data']=$data; //自定义参数
            }

            $json = JWT::encode($token,$key);

            return $json; //返回给客户端token信息

    }

    /**
     * 验证token是否有效,默认验证exp,nbf,iat时间
     * @param string $jwt 需要验证的token
     * @return string $msg 返回消息
     */
    public function checkToken($jwt){
        $key=config('token_key');;
        $decoded = JWT::decode($jwt, $key, ['HS256']); //HS256方式，这里要和签发的时候对应
        $arr = (array)$decoded;
        return $arr; //返回信息
    }



}
