<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use EasyWeChat\Factory;
use think\Db;

class Transfer extends Controller
{

    /**
     * 企业付款到用户零钱
     * 
     * @param  $amount       [发送的金额 目前发送金额不能少于1元]
	 * @param  $openid       [发送人的 openid]
	 * @param  $withdraw_sn  [商户订单号]
	 * @param  $desc         [企业付款描述信息 (必填)]
	 * @param  $check_name   [收款用户姓名 (选填)]
	 * @return [type]        [description]
     */
    // public function sendMoney($amount,$openid,$withdraw_sn,$check_name='',$desc='提现')
    // {
    //     $config = config('wx_pay');
    //     $payment = Factory::payment($config);
    //     $result = $payment->transfer->toBalance([
    //         'partner_trade_no' => $withdraw_sn, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
    //         'openid' => $openid,
    //         'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
    //         're_user_name' => $check_name, // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
    //         'amount' => $amount * 100, // 企业付款金额，单位为分
    //         'desc' => $desc, // 企业付款操作说明信息。必填
    //     ]);
        
    //     // TODO  退款成功时回调处理
    //     if ($result['return_code']=='SUCCESS' && $result['result_code']=='SUCCESS') {
    //         // 更新提现表
    //         $res = Db::name('rider_income_expend')->where('serial_number','=',$withdraw_sn)->setField('status',2);
    //         if ($res) {
    //             $str = '企业付款成功'; 
    //         } else {
    //             $str = '企业付款成功，数据库更新失败';
    //         }
    //     } else {
    //         $str = $result['err_code_des'];
    //     }
        
    //     return $str;
        
    // }


    	/**
	 * 企业付款到零钱
	 * @param  $amount       发送的金额（分）
	 * @param  $openid       发送人的 openid
	 * @param  $withdraw_sn  商户订单号]
	 * @param  $desc         企业付款描述信息 (必填)
	 * @param  $check_name   收款用户姓名 (选填)
	 */
    public function sendMoney($amount,$openid,$withdraw_sn,$check_name='ww',$desc='测试')
    {
        // 商户账号appid
        $data['mch_appid'] = config('wx_rider')['app_id'];
        // 商户号
        $data['mchid'] = config('wx_pay')['mch_id'];
        // 随机字符串
        $data['nonce_str'] = $this->getNonceStr();
        // 商户订单号
        $data['partner_trade_no'] = $withdraw_sn;
        // 用户openid
        $data['openid'] = $openid;
        // 校验用户姓名选项,
        $data['check_name'] = 'NO_CHECK';
        // 收款用户姓名
        $data['re_user_name'] = $check_name;
        // 金额
        $data['amount'] = $amount * 100;
        // 企业付款描述信息
        $data['desc'] = $desc;
        // Ip地址
        $data['spbill_create_ip'] = $_SERVER["REMOTE_ADDR"];
	    // 签名算法
        $data["sign"] = self::getSign($data);
        // 把数组转化成xml格式
	    $xml=$this->arrayToXml($data);
        // 调用接口
        $url='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; 
        
        // 返回来的结果        
        $result=$this->curlPost($url,$xml);
	    if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            // TODO 企业付款给用户成功后的业务逻辑处理
            // 更新提现表
            $res = Db::name('rider_income_expend')->where('serial_number','=',$withdraw_sn)->update(['status'=>2,'payment_time'=>time()]);
            if ($res) {
                $str = '企业付款成功'; 
            } else {
                $str = '企业付款成功，数据库更新失败';
            }
        } else {
            // 
            $str = $result['err_code_des'];
        }

        return $str;
	}


    /**
     *  curl模拟post请求
     * 
     * @param $url
     * @param $xmlData
     * @param $second
     * @return array
     */
    public  function curlPost($url,$xmlData,$second=60)
    {
        $header[] = "Content-type: text/xml";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, $second);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlData);

        // 添加证书校验
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($curl, CURLOPT_SSLCERT, config('wx_pay')['cert_path']);
        // 默认格式为PEM，可以注释
        curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($curl, CURLOPT_SSLKEY, config('wx_pay')['key_path']);

        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            print curl_error($curl);
        }
        curl_close($curl);

        return self::xmlToArray($data);

    }


    /**
     *  xml格式数据解析函数
     * 
     * @param $xml
     * @return array
     */
    public  function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        $msg = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        return $msg;
    }


    /**
     *  数组转为xml
     * 
     * @param $newPara
     * @return string
     */
    public  function arrayToXml($newPara)
    {
        $xmlData = "<xml>";
        foreach ($newPara as $key => $value) {
            $xmlData = $xmlData . "<" . $key . ">" . $value . "</" . $key . ">";
        }
        $xmlData = $xmlData . "</xml>";

        return $xmlData;
    }


    /**
     * 生成签名
     * 
     * @param $params
     * @return string
     */
    public  function getSign($params)
    {
        unset($params['sign']);
        ksort($params);
        $stringA = urldecode(http_build_query($params));
        $stringSignTemp = "$stringA&key=iew0a4ek8d2ap5nvn78bnsoq7m3wlfcs";
        if (isset($_POST['test'])) {
            echo $stringSignTemp . "\n";
        }
        return strtoupper(md5($stringSignTemp));
    }


    /**
     * 随机字符串
     * 
     * @return string
     */
    public  function getNonceStr()
    {
        return md5(uniqid() . microtime() . rand(0, 999999));
    }



    /**
     * 骑手提现申请列表 
     * 
     */
    public function riderTxList(Request $request)
    {
        // 搜索条件
        $where[] = ['rie.type','=',2];
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 10;
        // 获取集合
        $list = Db::name('rider_income_expend rie')->join('rider_info ri','rie.rider_id = ri.id')
                ->field('rie.id,rie.status,rie.current_money,rie.serial_number,rie.add_time,ri.name,ri.link_tel')
                ->append(['mb_status'])->order('rie.id desc')->where($where)->paginate($pagesize);

        $this->success('获取骑手提现申请列表成功',['list'=>$list]);

    }


    /**
     * 骑手提现申请审核通过 
     * 
     */
    public function riderTxCheckPass(Request $request)
    {
        $tx_id = $request->get('id');

        // 获取企业付款的相关参数信息
        $info = Db::name('rider_income_expend rie')->join('rider_info ri','rie.rider_id = ri.id')->where('rie.id','=',$tx_id)->field('rie.current_money,rie.serial_number,ri.openid,ri.name,rie.status')->find();
        
        // 判断是否已提现
        if ($info['status'] == 2) {
            $this->error('已提现，请勿重复提交');
        }

        // 调用企业付款接口
        $res = $this->sendMoney($info['current_money'],$info['openid'],$info['serial_number'],$info['name']);

        if ($res == '企业付款成功') {
            $this->success($res);
        }
        $this->error($res);

    }


    /**
     * 骑手提现申请审核不通过【失败】 
     * 
     */
    public function riderTxCheckNopass(Request $request)
    {
        $tx_id = $request->get('id');

        $res = Db::name('rider_income_expend')->where('id','=',$tx_id)->setField('status',3);

        if (!$res) {
            $this->error('提现审核失败设置失败');
        }
        $this->success('审核不通过');

    }







}
