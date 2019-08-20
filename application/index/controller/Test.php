<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/8/3
 * Time: 9:23 AM
 */

namespace app\index\Controller;


use think\Request;
use think\Controller;
use think\facade\Env;

class Test extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->path = Env::get('ROOT_PATH')."AccInfo.ini";
    }


    /**
     * @param Request $request
     * 批量添加用户
     */
    public function  addAll(Request $request)
    {
        $num = $request->param('num');

        for($i = 1;$i <= $num; $i++){
            $data['UserName'] = $request->param('UserName').$i;
            $data['Password'] = $request->param('Password');
            $res = $this->addData($data);
            if(is_array($res)) {
                return json($res);
            }
        }

        return $res;

    }

    /**
     * @param Request $request
     * 新增用户
     */
    public function add(Request $request)
    {
        $data = $request->param();
        $res = $this->addData($data);
        if(is_array($res)) {
            return json($res);
        }
        return $res;
    }

    /**
     * @param Request $request
     * 新增用户
     */
    public function addData($data)
    {
        $userInfo = [
            'UserName' => isset($data['UserName']) ? trim($data['UserName']) : '',
            'Password' => isset($data['Password']) ? trim($data['Password']) : '',
            'MACAddress' => isset($data['MACAddress']) ? $data['MACAddress'] : '',
            'IPAddressLow' => isset($data['IPAddressLow']) ? $data['IPAddressLow'] : '',
            'IPAddressHigh' => isset($data['IPAddressHigh']) ? $data['IPAddressHigh'] : '',
            'ServiceMask' => isset($data['ServiceMask']) ? $data['ServiceMask'] : '254',
            'MaxConn' => isset($data['MaxConn']) ? $data['MaxConn'] : '',
            'BandWidth' => isset($data['BandWidth']) ? $data['BandWidth'] : '',
            'BandWidth2' => isset($data['BandWidth2']) ? $data['BandWidth2'] : '',
            'WebFilter' => isset($data['WebFilter']) ? $data['WebFilter'] : '',
            'TimeSchedule' => isset($data['TimeSchedule']) ? $data['TimeSchedule'] : '',
            'EnableUserPassword' => isset($data['EnableUserPassword']) ? $data['EnableUserPassword'] : '1',
            'EnableIPAddress' => isset($data['EnableIPAddress']) ? $data['EnableIPAddress'] : '',
            'EnableMACAddress' => isset($data['EnableMACAddress']) ? $data['EnableMACAddress'] : '',
            'Enable' => isset($data['Enable']) ? $data['Enable'] : '',
            'BelongsGroup' => isset($data['BelongsGroup']) ? $data['BelongsGroup'] : '',
            'BelongsGroupName' => isset($data['BelongsGroupName']) ? $data['BelongsGroupName'] : '',
            'IsGroup' => isset($data['IsGroup']) ? $data['IsGroup'] : '',
            'AutoDisable' => isset($data['AutoDisable']) ? $data['AutoDisable'] : '',
            'DisableDateTime' => isset($data['DisableDateTime']) ? $data['DisableDateTime'] : '',
            'EnableLeftTime' => isset($data['EnableLeftTime']) ? $data['EnableLeftTime'] : '',
            'EnableBandwidthQuota' => isset($data['EnableBandwidthQuota']) ? $data['EnableBandwidthQuota'] : '',
            'BandwidthQuota' => isset($data['BandwidthQuota']) ? $data['BandwidthQuota'] : '',
            'BandwidthQuotaPeriod' => isset($data['BandwidthQuotaPeriod']) ? $data['BandwidthQuotaPeriod'] : '',
        ];

        $userInfo2 = explode("\r\n",file_get_contents($this->path));

        //先获取健名
        $keys = array_search('UserName='.$userInfo['UserName'],$userInfo2);

        if($keys) {
            return ['msg'=>'用户已经存在!','code'=>201,'data'=>[]];
        }

        $check = $this->validate($userInfo,'Test');

        if($check !== true) {
            return json(['msg'=>$check,'code'=>201,'data'=>[]]);
        }

        foreach ($userInfo as $key => $value)
        {
            trim($value);
        }

        $currCount = $this->getUserCount($this->path);

//        dump($currCount);exit;

        $userInfo['UserCode'] = sprintf('%03d',$currCount+1);


        $str = $this->toString($userInfo);

//        dump($str);exit;

        //追加写入
        $res = file_put_contents($this->path, $str, FILE_APPEND);


        //统计总人数
        $userInfo = explode("\r\n",file_get_contents($this->path));
        $UserCount = 'UserCount='.($currCount+1);
        $userInfo['1'] = $UserCount;

        //重新写入
        $str = implode("\r\n", $userInfo);

        file_put_contents($this->path, $str);

        return json(['msg'=>'写入成功','code'=>200,'data'=>[]]);

    }

    /**
     * @param $path
     * @return int
     * 获取总人数
     */
    public function getUserCount($path)
    {
        $userInfo = file($path);

        $sn = (int)substr($userInfo['1'], 10);
        $currCount = $sn;
        return $currCount;
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * 获取所有用户信息
     */
    public function get(Request $request)
    {
        $userInfo = explode("\r\n",file_get_contents($this->path));

        $page = 1;//当前第几页
        $pageSize = 25;//每页显示多少条
        $countPage = ceil(count($userInfo) / 25);//总页数
        $pageData = [];//返回数据



        for($i=$page;$i<=$countPage;$i++){
            $start = (($i-1)*$pageSize)+6;//计算每次分页的开始位置
            $pageData[] = array_slice($userInfo,$start,$pageSize);
        }
        dump($userInfo);exit;
        $userData = [];
        foreach ($pageData as $key => &$val){
            foreach ($val as $k => &$v){
                $a = [];
                $b = [];
                $vv = explode('=',$v);

                if(!isset($vv[1]) && empty($vv[1])) {
                    $vv[0] = 'UserCode';
                    $vv[1] = 'User'.sprintf('%03d',$key + 1);
                }
//                dump($v) ;
                if(isset($v) && !empty($v)){
                    list($a[],$b[]) = $vv;
                    $new_data = array_combine($a,$b);
                    foreach ($new_data as $k2 => $v2){
                        $k2 = ltrim($k2,'["');
                        $k2 = rtrim($k2,']"');
                        $userData[$key][$k2] = $v2;
                    }
                }

            }
        }

        return json(['msg'=>'获取成功','code'=>200,'data'=>$userData]);

    }

    /**
     * @param Request $request
     * 删除
     */
    public function del(Request $request)
    {
        $UserName = $request->param('UserName','ztt');

        $userInfo = explode("\r\n",file_get_contents($this->path));


        //先获取健名
        $key = array_search('UserName='.$UserName,$userInfo);

        //删除对应的数据
        array_splice($userInfo,$key-1,25);

//        dump($userInfo);exit;

        //重新写入文件
        $str = implode("\r\n", $userInfo);
        file_put_contents($this->path,$str);


        //统计总人数
        $currCount = $this->getUserCount($this->path);
        $UserCount = 'UserCount='.($currCount-1);
        $userInfo['1'] = $UserCount;

        //重新写入
        $str = implode("\r\n", $userInfo);

        file_put_contents($this->path, $str);
        return json(['msg'=>'删除成功','code'=>200,'data'=>[]]);

    }

    /**
     * @param Request $request
     * 编辑
     */
    public function edit(Request $request)
    {
        $Password = $request->param('Password','ztt');
        $UserName = $request->param('UserName','ztt');
        $MaxConn = $request->param('MaxConn','ztt');
        $BandWidth = $request->param('BandWidth','ztt');
        $BandWidth2 = $request->param('BandWidth2','ztt');
        $DisableDateTime = $request->param('DisableDateTime','ztt');

        $userInfo = explode("\r\n",file_get_contents($this->path));


        //更新数据
        $updateData = [
            'UserName'=>'UserName='.$UserName,
            'Password'=>'Password='.$Password,
            'MaxConn'=>'MaxConn='.$MaxConn,
            'BandWidth'=>'BandWidth='.$BandWidth,
            'BandWidth2'=>'BandWidth2='.$BandWidth2,
            'DisableDateTime'=>'DisableDateTime='.$DisableDateTime,

        ];
//        dump($updateData);

        //先获取健名
        $keys = array_search('UserName='.$UserName,$userInfo);


        //取出对用数组 并修改
        $data = array_slice($userInfo,$keys-1,25);

        foreach ($data as $key => $val)
        {
            $valData = explode('=',$val);

            if(in_array($valData[0],array_keys($updateData))) {
                $data[$key] = $updateData[$valData[0]];
            }
        }

//        dump($keys);
        //删除数组 用新数组代替
        $a = array_splice($userInfo,$keys-1,25,$data);
//        dump($userInfo);exit;

        //重新写入文件
        $str = implode("\r\n", $userInfo);
        file_put_contents($this->path,$str);
        return json(['msg'=>'更新成功','code'=>200,'data'=>[]]);


    }

    /**
     * @param array $data
     * @return string
     * 数组转字符串
     */
    public function toString($data = []){
        $temp = "[User".$data['UserCode']."]"."\r\n" .
            "UserName=".$data['UserName']. "\r\n" .
            "Password=".$data['Password']. "\r\n" .
            "MACAddress=".$data['MACAddress']. "\r\n" .
            "IPAddressLow=".$data['IPAddressLow']. "\r\n" .
            "IPAddressHigh=".$data['IPAddressHigh']. "\r\n" .
            "ServiceMask=".$data['ServiceMask']. "\r\n" .
            "MaxConn=".$data['MaxConn']. "\r\n" .
            "BandWidth=".$data['BandWidth']. "\r\n" .
            "BandWidth2=".$data['BandWidth2']. "\r\n" .
            "WebFilter=".$data['WebFilter']. "\r\n" .
            "TimeSchedule=".$data['TimeSchedule']. "\r\n" .
            "EnableUserPassword=".$data['EnableUserPassword']. "\r\n" .
            "EnableIPAddress=".$data['EnableIPAddress']. "\r\n" .
            "EnableMACAddress=".$data['EnableMACAddress']. "\r\n" .
            "Enable=".$data['Enable']. "\r\n" .
            "BelongsGroup=".$data['BelongsGroup']. "\r\n" .
            "BelongsGroupName=".$data['BelongsGroupName']. "\r\n" .
            "IsGroup=".$data['IsGroup']. "\r\n" .
            "AutoDisable=".$data['AutoDisable']. "\r\n" .
            "DisableDateTime=".$data['DisableDateTime']. "\r\n" .
            "EnableLeftTime=".$data['EnableLeftTime']. "\r\n" .
            "EnableBandwidthQuota=".$data['EnableBandwidthQuota']. "\r\n" .
            "BandwidthQuota=".$data['BandwidthQuota']. "\r\n" .
            "BandwidthQuotaPeriod=".$data['BandwidthQuotaPeriod']."\r\n";
        return $temp;
    }

    public function count()
    {

    }
}