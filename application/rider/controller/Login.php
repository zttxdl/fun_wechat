<?php

namespace app\rider\controller;

use think\Controller;
use think\Request;
use app\common\model\RiderInfo;
use app\common\Auth\JwtAuth;


/**
 * 骑手登录注册
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
        $app_id = config('wx_rider')['app_id'];
        $app_secret = config('wx_rider')['secret'];
        
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$app_id.'&secret='.$app_secret.'&js_code='.$code.'&grant_type=authorization_code';

        // curl 请求
        $result = curl_post($url,'POST');
  
        $wxResult = json_decode($result, true);

        //判断返回的结果中是否有错误码
        if (isset($wxResult['errcode'])) {
            $this->error($wxResult['errmsg'],$wxResult['errcode']);
        }
        $this->success('获取 openid 成功',['auth_result'=>$wxResult]);
    }


    /**
     * 授权时，存储 openid 等用户相关信息 
     * 
     */
    public function saveRiderBaseInfo(Request $request)
    {
        $data = $request->post();
        $list['nickname'] = $data['nickName'];
        $list['headimgurl'] = $data['avatarUrl'];
        $list['openid'] = $data['openid'];
        $list['sex'] = $data['gender'];
        $list['invitation_id'] = $data['invitation_id'];
        $list['add_time'] = time();

        // 判断当前用户是否已授权
        $id = RiderInfo::where('openid','=',$data['openid'])->count('id');
        if ($id) {

            $this->error('该用户已授权');

        }

        // 存入数据
        $result = RiderInfo::create($list);
        if(!$result) {
            $this->error('授权入表失败');
        }
        $this->success('授权入表成功');
        
    }
     


    /**
     * 校验当前的手机号的验证码 
     * 
     */
    public function checkRiderPhone(Request $request)
    {
        $phone = $request->param('phone');
        $code  = $request->param('code');
        $type  = $request->param('type');

        $result = model('Alisms', 'service')->checkCode($phone, $type, $code);
        if (!$result) {
            $this->error(model('Alisms', 'service')->getError());
        }
        $this->success('验证通过');

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
            $this->error('短信发送失败');
        }
        $this->success('验证码已发送至 ' . $phone . ', 5分钟内有效！');

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
            $this->error(model('Alisms', 'service')->getError());
        }

        // 判断openid是否存在
        $rid = RiderInfo::where('openid',$openid)->value('id');
        if (!$rid) {
            $this->error('非法参数');
        }
        // 更新数据
        $res = RiderInfo::where('openid',$openid)->save([
            'link_tel' =>  $phone,
            'last_login_time'   =>  time()
        ]);
        
        if (!$res) {
            $this->error('登录或注册失败');
        }
        $rider_info = RiderInfo::where('id','=',$rid)->find();

        $jwtAuth = new JwtAuth();
        $token = $jwtAuth->createToken($rider_info,604800);
        $this->success('success',[
            'token' => $token
        ]);

        
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
            $this->error($data['msg'],$data['code']);
        }

        // 存表处理
        // 判断openid是否存在
        $rid = RiderInfo::where('openid',$data['openid'])->value('id');
        if (!$rid) {
            $this->error('非法参数');
        }
        // 更新数据
        $res = RiderInfo::where('openid',$data['openid'])->save([
            'link_tel' =>  $data['phone'],
            'last_login_time'   =>  time()
        ]);
        
        if (!$res) {
            $this->error('快捷登录失败');
        }
        $rider_info = RiderInfo::where('id','=',$rid)->find();

        $jwtAuth = new JwtAuth();
        $token = $jwtAuth->createToken($rider_info,604800);
        $this->success('success',[
            'token' => $token
        ]);

    }
     
    

    /**
     * 获取微信手机号 
     * 
     */
    public function getWechatPhone($encrypted_data,$code,$iv)
    {
        $app_id = config('wx_rider')['app_id'];
        $app_secret = config('wx_rider')['secret'];

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

        include_once './../extend/wx_auth_phone/wxBizDataCrypt.php';
        $wx = new \WXBizDataCrypt($app_id, $recod->session_key); //微信解密函数，微信提供了php代码dome
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