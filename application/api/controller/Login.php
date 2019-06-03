<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\captcha\Captcha;
use app\common\model\User;

/**
 * 用户登录控制器
 */
class Login extends Controller
{
    /**
     * 授权获取openid 
     * 
     */
    public function getOpenid($code)
    {
        $app_id = config('wx_user.app_id');
        $app_secret = config('wx_user.secret');
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$app_id.'&secret='.$app_secret.'&js_code='.$code.'&grant_type=authorization_code';

        // curl 请求
        $result = http_curl($url,'post');
        //判断连接是否成功
        if ($result[0] != 200) {
            return json_error('连接微信服务器失败',201);
        }

        //将返回的json处理成数组
        $wxResult = json_decode($result[1], true);
        if (empty($wxResult)) {
            return json_error('获取session_key，openID时异常，微信内部错误',202);
        } 

        //判断返回的结果中是否有错误码
        if (isset($wxResult['errcode'])) {
            return json_error($wxResult['errmsg'], $wxResult['errcode']);
        }
        return json_success('获取 openid 成功',['openid'=>$wxResult['openid']]);
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
     * 发送手机号验证码 
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
     * 更换手机号【保存】
     * 
     */
    public function setUserPhone(Request $request)
    {
        $uid = $request->param('uid');
        $phone = $request->param('phone');
        $code  = $request->param('code');
        $type  = $request->param('type');

        // 校验验证码
        $result = model('Alisms', 'service')->checkCode($phone, $type, $code);
        if (!$result) {
            return json_error(model('Alisms', 'service')->getError());
        }

        // 更新数据
        $user = User::get($uid);
        $user->phone = $phone;
        $res = $user->save();
        if (!$res) {
            return json_error('失败');
        }
        $user_info = User::get($uid);
        return json_success('成功',['user_info'=>$user_info]);
        
    }


    /**
     * 登录|注册 
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
            'phone' =>  $phone
        ]);
        
        if (!$res) {
            return json_error('登录或注册失败');
        }
        $user_info = User::get($uid);

        return json_success('登录或注册成功',['user_info'=>$user_info]);
        
    }
     
     
     
}
