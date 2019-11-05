<?php

namespace app\rider\controller;

use app\common\controller\RiderBase;
use think\Request;
use app\common\model\RiderInfo;
use app\common\Auth\JwtAuth;
use EasyWeChat\Factory;
use think\Db;

/**
 * 骑手登录注册
 */
class Login extends RiderBase
{
    protected  $noNeedLogin = ['*'];
    /**
     * 授权获取openid、session_key信息
     * 
     */
    public function getAuthInfo(Request $request)
    {
        $config = config('wx_rider');
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
     * 授权时，存储 openid 等用户相关信息 ，并存表
     * 
     */
    public function saveRiderBaseInfo(Request $request)
    {
        $data = $request->post();
        $list['nickname'] = $data['nickName'];
        $list['headimgurl'] = $data['avatarUrl'];
        $list['openid'] = $data['openid'];
        $list['sex'] = $data['gender'];
        $list['add_time'] = time();
        $list['last_login_time'] = time();

        // 判断当前用户是否已授权
        $rid = Db::name('rider_info')->where('openid','=',$data['openid'])->value('id');
        if (!$rid) {
            $rid = Db::name('rider_info')->insertGetId($list);
            if(!$rid) {
                $this->error('授权入表失败');
            }
        } else {
            Db::name('rider_info')->where('id','=',$rid)->setField('last_login_time',time());
        }
        $info = Db::name('rider_info')->where('openid','=',$data['openid'])->field('id,school_id,status,open_status')->find();
        if ($info['school_id']) {
            // 记录日志
            write_log($info,'log');
            $hourse_ids_arr = Db::name('hourse')->where('school_id','=',$info['school_id'])->column('id');
            write_log($hourse_ids_arr,'log');
            if ($hourse_ids_arr) {
                $hourse_ids_str = implode(',',$hourse_ids_arr);
                // Db::name('rider_info')->where('id',$this->auth->id)->setField('hourse_ids','0,'.$hourse_ids_str);
                $res = Db::name('rider_info')->where('id',$this->auth->id)->fetchSql()->setField('hourse_ids','0,'.$hourse_ids_str);
                write_log($res,'log');
            }
        }
        $jwtAuth = new JwtAuth();
        $token = $jwtAuth->createToken($info,31104000);   // 一年有效期
        $this->success('已授权',[
            'token' => $token,
            'uuid' => 'r'.$rid
        ]);
        
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
     * 使用其他手机号登录|注册 【此功能已关闭】
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
        // 防止同一手机号，不同的微信openid 登录，必须唯一关系
        $result_openid = RiderInfo::where('phone',$phone)->value('openid');
        if (!empty($result_openid) && ($result_openid != $openid)) {
            $this->error('该手机号已绑定');
        }

        // 更新数据
        $res = RiderInfo::where('openid',$openid)->update([
            'phone' =>  $phone,
            'last_login_time'   =>  time()
        ]);
        
        if (!$res) {
            $this->error('登录或注册失败');
        }
        $rider_info = RiderInfo::where('id','=',$rid)->field('id,school_id,status,open_status,name')->find();

        $jwtAuth = new JwtAuth();
        $token = $jwtAuth->createToken($rider_info,31104000);   // 一年有效期
        $this->success('success',[
            'token' => $token,
            'uuid' => 'r'.$rid,
            'name'  => $rider_info['name'],
            'phone' =>  $phone
        ]);
    }


    /**
     * 微信用户快捷登录 【此功能已关闭】
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
        // 防止同一手机号，不同的微信openid 登录，必须唯一关系
        $result_openid = RiderInfo::where('phone',$data['phone'])->value('openid');
        if (!empty($result_openid) && ($result_openid != $data['openid'])) {
            $this->error('该手机号已绑定');
        }

        // 更新数据
        $res = RiderInfo::where('openid',$data['openid'])->update([
            'phone' =>  $data['phone'],
            'last_login_time'   =>  time()
        ]);
        
        if (!$res) {
            $this->error('快捷登录失败');
        }
        $rider_info = RiderInfo::where('id','=',$rid)->field('id,school_id,status,open_status,name')->find();

        $jwtAuth = new JwtAuth();
        $token = $jwtAuth->createToken($rider_info,31104000);   // 一年有效期
        $this->success('success',[
            'token' => $token,
            'uuid' => 'r'.$rid,
            'name'  => $rider_info['name'],
            'phone' =>  $data['phone']
        ]);
    }
     
    

    /**
     * 获取微信手机号 【此功能已关闭】
     * 
     */
    public function getWechatPhone($encrypted_data,$code,$iv)
    {
        $config = config('wx_rider');
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