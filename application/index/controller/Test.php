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
use think\facade\Cache;
use think\Db;

class Test extends Controller
{

    public function __construct()
    {

        parent::__construct();
        $this->path = Env::get('ROOT_PATH')."AccInfo.ini";
        $this->pageSize = 25;//每页显示字段数
    }


    /**
     * @param Request $request
     * 批量添加用户
     */
    public function  addAll(Request $request)
    {
        $num = $request->param('num');

        if(empty($num)) {
            return ['msg'=>'开号数量不能为空!','code'=>201,'data'=>[]];
        }

        if((int)$num <1) {
            return ['msg'=>'开号数量要大于1!','code'=>201,'data'=>[]];
        }


        for($i = 1;$i <= $num; $i++){
            $data['UserName'] = $request->param('UserName').$i;
            $data['Password'] = $request->param('Password');
            $data['AccountType'] = $request->param('AccountType');
            $res = $this->addData($data);
            if(is_array($res)) {
                return $res;
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
        $check = $this->validate($data,'Test');

        if($check !== true) {
            return json(['code'=>201,'data'=>$data,'msg'=>$check]);
        }

        //过期时间
        $DisableDateTime = date('Y-m-d H:i:s');

        //测试账号
        if($data['AccountType'] == '1') {
            $DisableDateTime = date('Y-m-d H:i:s',strtotime('+5 hours'));
        }
        //正式账号
        if($data['AccountType'] == '2') {
            $DisableDateTime = date('Y-m-d H:i:s',strtotime('+30 days'));
        }


        $userInfo = [
            'UserName' => isset($data['UserName']) ? trim($data['UserName']) : '',
            'Password' => isset($data['Password']) ? trim($data['Password']) : '',
            'MACAddress' => isset($data['AccountType']) ? $data['AccountType'] : '',//新增账号类型 add by ztt 20190821
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
            'BelongsGroupName' => date('Y-m-d'),//新增账号添加时间 add by ztt 20190823
            'IsGroup' => isset($data['IsGroup']) ? $data['IsGroup'] : '',
            'AutoDisable' => isset($data['AutoDisable']) ? $data['AutoDisable'] : '1',//添加默认值 add by ztt 20100821
            'DisableDateTime' => isset($DisableDateTime) ? $DisableDateTime : '',//添加账号过期时间 add by ztt 20100821
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

        //判断测试账号是否当天的测试账号是否超过200个
        $res = $this->TestUserCount();
        if($res) {
            return  ['msg'=>'测试账号一天不能超过200个!','code'=>201,'data'=>[]];
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

        /*foreach ($userInfo as $key => $value)
        {
            if($value == '') {
                unset($userInfo[$key]);
            }
        }*/

//        dump($userInfo);exit;
        $UserCount = 'UserCount='.($currCount+1);
        $userInfo['1'] = $UserCount;

        //重新写入
        $str = implode("\r\n", $userInfo);
//                dump($str);exit;
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
    public function get()
    {
        $userInfo = explode("\r\n",file_get_contents($this->path));

        $page = 1;//当前第几页
        $pageSize = $this->pageSize;//每页显示多少条
        $countPage = ceil(count($userInfo) / $this->pageSize);//总页数
        $pageData = [];//返回数据



        for($i=$page;$i<=$countPage;$i++){
            $start = (($i-1)*$pageSize)+6;//计算每次分页的开始位置
            $pageData[] = array_slice($userInfo,$start,$pageSize);
        }

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

        return ['msg'=>'获取成功','code'=>200,'data'=>$userData];

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

        if(empty($key)) {
            return ['msg'=>'用户不存在!','code'=>201,'data'=>[]];
        }

        //删除对应的数据段
        array_splice($userInfo,$key-1,$this->pageSize);

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
        $data = array_slice($userInfo,$keys-1,$this->pageSize);

        foreach ($data as $key => $val)
        {
            $valData = explode('=',$val);

            if(in_array($valData[0],array_keys($updateData))) {
                $data[$key] = $updateData[$valData[0]];
            }
        }

//        dump($keys);
        //删除数组 用新数组代替
        $a = array_splice($userInfo,$keys-1,$this->pageSize,$data);
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

    /**
     * 测试账号每天只能添加200个
     * 返回true
     */
    public function TestUserCount()
    {
        $data = $this->get();
        $time = date('Y-m-d');

        $test_user = [];

        foreach ($data['data'] as $row)
        {
            if($row['BelongsGroupName'] == $time) {
                if($row['MACAddress'] == '1') {
                    $test_user[] = $row['UserName'];
                }
            }
        }

        $test_user_count = count($test_user);
        if($test_user_count > 200) {
            return true;
        }
        return false;

    }

    /**
     * 统计账号个数
     */
    public function count(Request $request)
    {
        $data = explode("\r\n",file_get_contents($this->path));

//        dump($data);

        $test_code = 'MACAddress=1';//测试会员
        $prod_code = 'MACAddress=2';//正式会员

        $test_data = [];
        $prod_data = [];

        foreach($data as $row) {
//            dump($row);
            if($row == $test_code) {
                $test_data[] = $test_code;
            }

            if($row == $prod_code) {
                $prod_data[] = $prod_code;
            }
        }

        $test_user_count =  count($test_data);
        $prod_user_count =  count($prod_data);
        $user_count = $prod_user_count + $test_user_count;
        $total_money = $prod_user_count * 6;

        $data = [
            'user_count' => $user_count,
            'test_user_count' => $test_user_count,
            'prod_user_count' => $prod_user_count,
            'total_money' => $total_money,
        ];

        return json(['code'=>'200','data'=>$data,'msg'=>'获取成功']);

    }

    /**
     * 测试接口
     */
    public function test(Request $request)
    {
//        phpinfo();exit;
//        $ret = Cache::store('redis')->get('list');
//        dump($ret);
        // $data = [
        //     [
        //         'pid' => 320,
        //         'price' => 1.00,
        //     ],
        //     [
        //         'pid' => 320,
        //         'price' => 2.00,
        //     ],
        // ];
        // $data = array_column($data,'pid');

        // $_data = array_count_values($data);
        // dump($_data['320']);
        if(bccomp(15.7 - 10, 5.7, 4) != 0) {
            $this->error('订单结算金额不正确');
        }else{
            $data = [
                ['id'=>1,'fid'=>0,'name'=>'aa'],
                ['id'=>2,'fid'=>0,'name'=>'bb'],
                ['id'=>3,'fid'=>0,'name'=>'cc'],
                ['id'=>4,'fid'=>1,'name'=>'dd'],
                ['id'=>5,'fid'=>1,'name'=>'ee'],
                ['id'=>6,'fid'=>2,'name'=>'ff'],
                ['id'=>7,'fid'=>2,'name'=>'gg'],
                ['id'=>8,'fid'=>4,'name'=>'hh'],
            ];

            $res = get_node($data);
            $this->success($res);
        }
    }

    /**
     * 添加新的字段
     */
    public function addColumns(Request $request)
    {
        $key = $request->param('key');
        $value = $request->param('value');

        $data = explode("\r\n",file_get_contents($this->path));

        //先获取健名
        $keys = array_search($key."=".$value,$data);

        if($keys) {
            return ['msg'=>'字段已添加,请勿重复操作!','code'=>201,'data'=>[]];
        }

        $page = 1;//当前第几页
        $pageSize = $this->pageSize - 1;//每页显示多少条
        $countPage = ceil(count($data) / $pageSize);//总页数
        $pageData = [];//返回数据

        //头部数据
        $headerData = array_slice($data,0,6);

        for($i=$page;$i<=$countPage;$i++){
            $start = (($i-1)*$pageSize)+6;//计算每次分页的开始位置
            $pageData[] = array_slice($data,$start,$pageSize);
        }

        $new_data = [];
        foreach ($pageData as &$row)
        {
            $addColumns = $key.'='.$value;
            if(!empty($row[0])) {
                array_push($row,$addColumns);
            }

        }
        //向数组开头插入头部数组
        array_unshift($pageData,$headerData);

        //二维数组转一维数组
        foreach ($pageData as $v){
            foreach ($v as $vv){
                $new_data[] = $vv;
            }
        }

//        dump($new_data);exit;
        //重新写入文件
        $str = implode("\r\n", $new_data);
//                dump($str);exit;
        $res = file_put_contents($this->path,$str);

        if($res) {
            return json(['msg'=>'添加成功','code'=>200,'data'=>[]]);
        }

    }

    /**
     * 删除新的字段
     * @param Request $request
     */
    public function delColumns(Request $request)
    {
        $key = $request->param('key');
    }

}