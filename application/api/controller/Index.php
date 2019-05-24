<?php
namespace app\api\controller;


use think\Controller;
use think\Request;


class Index extends Base
{
    /**
     * 获取用户登录信息
     * @url /api/v1.index/login
     * @method POST
     * @param integer $page 页数
     * @param integer $limit 每页个数
     * @return integer $code 状态码
     * @return string $msg 返回消息
    */
    public function login(){
        
            //登录思路：客户端通过用户名密码登录以后，服务端返回给客户端两个token：access_token和refresh_token。
                //access_token：请求接口的token
                //refresh_token：刷新access_token
                //举个例子：比如access_token设置2个小时过期，refresh_token设置7天过期，2小时候后，access_token过期，但是refresh_token还在7天以内，那么客户端通过refresh_token来服务端刷新，服务端重新生成一个access_token；
                //如果refresh_token也超过了7天，那么客户端需要重新登录获取access_token和refresh_token。
                //为了区分两个token，我们在载荷（payload)加一个字段 scopes ：作用域。
                //access_token中设置：scopes:role_access
                //refresh_token中设置：scopes:role_refresh
    
                //自定义信息，不要定义敏感信息
                    $data['userid']=21;//用户ID
                    $data['username']="李小龙";//用户ID
                
                //请求接口的token
                    $exp_time1=7200; //token过期时间,这里设置2个小时
                    $scopes1='role_access'; //token标识，请求接口的token
                    $access_token = action('createToken',['data'=>$data,'exp_time'=>$exp_time1,'scopes'=>$scopes1]);
                
                //刷新refresh_token
                    $exp_time2=86400 * 30; //refresh_token过期时间,这里设置30天
                    $scopes2='role_refresh'; //token标识，刷新access_token
                    $refresh_token = action('createToken',['data'=>$data,'exp_time'=>$exp_time2,'scopes'=>$scopes2]);

                 //公用信息
                $time =time();
                 $token = [
                             'iss' => 'http://www.helloweba.net', //签发者 可选
                             'aud' => 'http://www.helloweba.net', //接收该JWT的一方，可选
                             'iat' => $time, //签发时间
                             'nbf' => $time, //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
                             'data' => $data
                 ];
             //请求接口的token 用户名登录验证通过时生成的
                         $access_token = $token; // access_token
                         $access_token['scopes'] = 'role_access'; //token标识，请求接口的token
                         $access_token['exp'] = $time+7200; //access_token过期时间,这里设置2个小时
             //刷新access_token
                         $refresh_token = $token; //refresh_token
                         $refresh_token['scopes'] = 'role_refresh'; //token标识，刷新access_token
                         $refresh_token['exp'] = $time+(86400 * 30); //refresh_token过期时间,这里设置30天
        
                $jsonList = [
                        'access_token'=>$access_token,
                        'refresh_token'=>$refresh_token,
                        'token_type'=>'bearer' //token_type：表示令牌类型，该值大小写不敏感，这里用bearer
                ];
            
    }


}
