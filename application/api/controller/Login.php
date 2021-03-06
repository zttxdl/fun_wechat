<?php

namespace app\api\controller;

use app\common\Auth\JwtAuth;
use app\common\controller\ApiBase;
use think\Request;
use app\common\model\User;
use EasyWeChat\Factory;


/**
 * 用户登录控制器  
 */
class Login extends ApiBase
{
    protected  $noNeedLogin = ['*'];

    /**
     * 授权获取openid、session_key信息
     * 
     */
    public function getAuthInfo(Request $request)
    {
        $config = config('wx_user');
        $app = Factory::miniProgram($config);
        $code = $request->param('code');
        $result = $app->auth->session($code);
        //判断返回的结果中是否有错误码
        if (isset($result['errcode'])) {
            $this->error($result['errmsg'],$result['errcode']);
        }
        $this->success('获取 openid 成功',['auth_result'=>$result]);
    }


    /**
     * 授权时，存储 openid 等用户相关信息 
     * 
     */
    public function saveUserBaseInfo(Request $request)
    {
        $data = $request->post();
        $list['nickname'] = $data['nickName'];
        $list['headimgurl'] = $data['avatarUrl'];
        $list['openid'] = $data['openid'];
        $list['sex'] = $data['gender'];
        $list['invitation_id'] = $data['invitation_id'];
        $list['add_time'] = time();

        // 判断当前用户是否已授权
        $id = User::where('openid','=',$data['openid'])->count('id');
        if ($id) {
            $this->success('该用户已授权');
        }
        // 存入数据
        $result = User::create($list);
        // 存入新用户
        $date = date('Y-m-d');
        $res = model('UserNew')->where('save_time','=',$date)->count();
        if ($res) {
            model('UserNew')->where('save_time','=',$date)->setInc('count');
        } else {
            model('UserNew')->insert(['save_time'=>$date,'count'=>1]);
        }
        if(!$result) {
            $this->error('授权入表失败');
        }
        $this->success('授权入表成功');
        
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
        $uid = User::where('openid',$openid)->value('id');
        if (!$uid) {
            $this->error('非法参数');
        }
        // 更新数据
        $res = User::where('openid',$openid)->update([
            'phone' =>  $phone,
            'last_login_time'   =>  time()
        ]);
        
        if (!$res) {
            $this->error('登录或注册失败');
        }
        $user_info = User::where('id','=',$uid)->field('id,phone,openid,new_buy,status')->find();

        $jwtAuth = new JwtAuth();
        $token = $jwtAuth->createToken($user_info,31104000); // 一年有效期
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
        $uid = User::where('openid',$data['openid'])->value('id');
        if (!$uid) {
            $this->error('非法参数');
        }
        // 更新数据
        $res = User::where('openid',$data['openid'])->update([
            'phone' =>  $data['phone'],
            'last_login_time'   =>  time()
        ]);
        
        if (!$res) {
            $this->error('快捷登录失败');
        }
        $user_info = User::where('id','=',$uid)->field('id,phone,openid,new_buy,status')->find();

        $jwtAuth = new JwtAuth();
        $token = $jwtAuth->createToken($user_info,31104000);  // 一年有效期
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
        $config = config('wx_user');
        $app = Factory::miniProgram($config);
        $code = request()->param('code');
        $result = $app->auth->session($code);

        include_once './../extend/wx_auth_phone/wxBizDataCrypt.php';
        $wx = new \WXBizDataCrypt($config['app_id'], $result['session_key']); //微信解密函数，微信提供了php代码dome
        $errCode = $wx->decryptData($encrypted_data, $iv, $data); //微信解密函数
        if ($errCode == 0) {
            $data = json_decode($data, true);
            $res = ['code'=>200,'phone'=>$data['phoneNumber'],'openid'=>$result['openid']];
        } else {
            $res = ['code'=>203,'msg'=>'请求失败'];
        }
        return $res;

    }

     
     
     
     
}
