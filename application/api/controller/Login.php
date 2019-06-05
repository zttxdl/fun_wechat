<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\captcha\Captcha;
use app\common\model\User;
use wx_auth_phone\WXBizDataCrypt;

/**
 * 用户登录控制器
 */
class Login extends Controller
{
    /**
     * 授权获取openid、session_key信息
     * 
     */
    public function getAuthInfo(Request $request)
    {
        $code = $request->param('code');
        $app_id = config('wx_user')['app_id'];
        $app_secret = config('wx_user')['secret'];
        
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$app_id.'&secret='.$app_secret.'&js_code='.$code.'&grant_type=authorization_code';

        // curl 请求
        $result = curl_post($url,'POST');
  
        $wxResult = json_decode($result, true);

        //判断返回的结果中是否有错误码
        if (isset($wxResult['errcode'])) {
            return json_error($wxResult['errmsg'], $wxResult['errcode']);
        }
        return json_success('获取 openid 成功',['auth_result'=>$wxResult]);
    }


    /**
     * 授权时，存储 openid 等用户相关信息 
     * 
     */
    public function saveUserBaseInfo(Request $request)
    {
        $data = $request->post();
        $list['nickname'] = $data['nickName'];
        $list['img'] = $data['avatarUrl'];
        $list['openid'] = $data['openid'];
        $list['sex'] = $data['gender'];
        $list['invitation_id'] = $data['invitation_id'];
        $list['add_time'] = time();

        // 存入数据
        $result = User::create($list);
        if(!$result) {
            return json_error('授权入表失败');
        }
        return json_success('授权入表成功');
        
    }
     


    /**
     * 校验当前的手机号的验证码 
     * 
     */
    public function checkUserPhone(Request $request)
    {
        $phone = $request->param('phone');
        $code  = $request->param('code');
        $type  = $request->param('type');

        $result = model('Alisms', 'service')->checkCode($phone, $type, $code);
        if (!$result) {
            return json_error(model('Alisms', 'service')->getError());
        }
        return json_success('验证通过');

    }


    /**
     * 获取手机号验证码 
     * 
     */
    public function getVerify(Request $request)
    {
        $phone = $request->param('phone');
        $type = $request->param('type');

        // 发送短信
        $back = model('Alisms', 'service')->sendCode($phone,$type);

        if (!$back) {
            return json_error('短信发送失败');
        }
        return json_success('验证码已发送至 ' . $phone . ', 5分钟内有效！');

    }


    /**
     * 使用其他手机号登录|注册 
     * 
     */
    public function login(Request $request)
    {
        $openid = $request->param('openid');
        $phone = $request->param('phone');
        $code  = $request->param('code');
        $type  = $request->param('type');

        // 校验验证码
        $result = model('Alisms', 'service')->checkCode($phone, $type, $code);
        if (!$result) {
            return json_error(model('Alisms', 'service')->getError());
        }

        // 判断openid是否存在
        $uid = User::where('openid',$openid)->value('id');
        if (!$uid) {
            return json_error('非法参数');
        }
        // 更新数据
        $res = User::where('openid',$openid)->save([
            'phone' =>  $phone,
            'last_login_time'   =>  time()
        ]);
        
        if (!$res) {
            return json_error('登录或注册失败');
        }
        $user_info = User::where('id','=',$uid)->field('id,headimgurl,nickname,phone')->find();

        return json_success('登录或注册成功',['user_info'=>$user_info]);
        
    }


    /**
     * 微信用户快捷登录 
     * 
     */
    public function celerityLogin(Request $request)
    {
        $encrypted_data = $request->param('encryptedData');
        $code = $request->param('code');
        $iv = $request->param('iv');

        // 解密手机号
        $data = $this->getWechatPhone($encrypted_data,$code,$iv);
        if ($data['code'] != 200) {
            return json_error($data['msg'],$data['code']);
        }

        // 存表处理
        // 判断openid是否存在
        $uid = User::where('openid',$data['openid'])->value('id');
        if (!$uid) {
            return json_error('非法参数');
        }
        // 更新数据
        $res = User::where('openid',$data['openid'])->save([
            'phone' =>  $data['phone'],
            'last_login_time'   =>  time()
        ]);
        
        if (!$res) {
            return json_error('快捷登录失败');
        }
        $user_info = User::where('id','=',$uid)->field('id,headimgurl,nickname,phone')->find();

        return json_success('快捷登录成功',['user_info'=>$user_info]);

    }
     
    

    /**
     * 获取微信手机号 
     * 
     */
    public function getWechatPhone($encrypted_data,$code,$iv)
    {
        $app_id = config('wx_user.app_id');
        $app_secret = config('wx_user.secret');

        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$app_id.'&secret='.$app_secret.'&js_code='.$code.'&grant_type=authorization_code';

        // curl 请求
        $result = curl_post($url);
        $wxResult = json_decode($result, true);

        //判断返回的结果中是否有错误码
        if (isset($wxResult['errcode'])) {
            $res = ['code'=>$wxResult['errcode'],'msg'=>$wxResult['errmsg']];
            return $res;
        }

        // 解密
        $recod = json_decode($wxResult);
        $wx = new WXBizDataCrypt($app_id, $recod->session_key); //微信解密函数，微信提供了php代码dome
            $errCode = $wx->decryptData($encrypted_data, $iv, $data); //微信解密函数
        if ($errCode == 0) {
            $data = json_decode($data, true);
            $res = ['code'=>200,'phone'=>$data['phoneNumber'],'openid'=>$recod->openid];
        } else {
            $res = ['code'=>203,'msg'=>'请求失败'];
        }
        return $res;

    }

     
     
     
     
}
