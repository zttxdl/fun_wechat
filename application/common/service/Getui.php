<?php
/**
 * Author: Postbird
 * Date  : 2017/4/29
 * time  : 11:11
 * Site  : www.ptbird.cn
 * There I am , in the world more exciting!
 */
namespace app\common\service;

use think\facade\Cache;
use think\Model;

class Getui extends Model{

    /**
     * 获取authtoken,从缓存中获取
     * 有效时间是1天，如果超时则重新获取
     * 为了保险起见，保存时间为23小时，超时刷新
     */
    public function getAuthToken(){
        // 从缓存中获取 [缓存中保存的字段标识是：getui_auth_token]
        $authToken=Cache::get('getui_auth_token');
        // 如果存在token参数,则说明没有过期
        if($authToken){
            // 返回auth_token
            return $authToken;
        }else{
            // 刷新token，会返回数组格式
            $res=$this->refreshAuthToken();
            // dump($res);die;
            // 返回的数组中 result=ok 代表获取成功
            if($res['result']=='ok'){
                // 向缓存中存储 token,有效时间是23小时
                Cache::set('getui_auth_token',$res['auth_token'],82800);
                // 将结果存储到redis中
                $this->cache_getui_res($res);
                return $res['auth_token'];
            }// 将结果存储到redis中
            $this->cache_getui_res($res);
            return false;
        }
    }
    /**
     * 刷新或者初次获取 authtoken
     * 通过 restAPI刷新
     * protected 方法
     */
    protected function refreshAuthToken(){
        // 从配置中获取相关的数据
        $appKey=config('getui')['appkey'];
        $appId=config('getui')['appid'];
        $masterSecret=config('getui')['mastersecret'];
        // 获取毫秒数 秒数*1000
        $timestamp=time()*1000;
        // 构建sign
        $sign=strtolower(hash('sha256',$appKey.$timestamp.$masterSecret,false));
        // 构建需要发送的数据
        $dataArr=[
            'sign'=>$sign,
            'timestamp'=>$timestamp,
            'appkey'=>$appKey,
        ];
        // 个推所有的api发送的数据都是json格式，因此不能发送函数，需要发送json
        $content=json_encode($dataArr);
        // 构建header
        $header=array(
            'Content-Type: application/json',
        );
        $url='https://restapi.getui.com/v1/'.$appId.'/auth_sign';
        // 发送http post请求
        $res=curl_post_json($url,$header,$content);
        $res=json_decode($res,true);
        // 返回数组格式,如果res.result是ok，说明没问题
        // 将结果存储到redis中
        $this->cache_getui_res($res);
        return $res;
    }
    /**
     * 关闭鉴权
     */
    public function closeAuthToken(){
        $appId=config('getui')['appid'];
        // 获取auth_token,调用函数获取，如果超时则会自动刷新
        $authToken=$this->getAuthToken();
        if(!$authToken){
            return false;
        }
        // 构建header
        $header=[
            'Content-Type: application/json',
            'authtoken:'.$authToken
        ];
        $url='https://restapi.getui.com/v1/'.$appId.'/auth_close';
        $res=curl_post_json($url,$header);
        $res=json_decode($res,true);
        // 返回数组格式,如果res.result是ok，说明没问题
        // 将结果存储到redis中
        $this->cache_getui_res($res);
        return $res;
    }
    /**
     *  向某个用户推送消息
     *  cid = fd98882bef6f1bade6bffc85574436db
     *  cid = 260cc489c1b6bb13b7cb933f89020ad0
     *  - $content 是一个报刊在notification->style下的数组内容
     *    其中包括了  title,text,logo,logourl,is_ring,is_vibrate,is_clearable
     * @param $clientID string
     * @param $content array
     * @param $transmission_content string
     */
    public function sendToClient($clientID,$content,$transmission_content=''){
        $appKey=config('getui')['appkey'];
        $authToken=$this->getAuthToken();
        $appId=config('getui')['appid'];
        $content=array(
            'message'=>[
                "appkey"=>$appKey,
                "is_offline"=>false,
                "msgtype"=>"notification"
            ],
            'notification'=>[
                'style'=>[
                    'type'=>1,
                    'title'=>$content['title'],
                    'text'=>$content['text'],
                    'logourl'=>$content['logourl'],
                    'is_ring'=>$content['is_ring'],
                    'is_vibrate'=>$content['is_vibrate'],
                    'is_clearable'=>$content['is_clearable'],
                ],
                "transmission_type"=> true,
                "transmission_content"=> $transmission_content
            ],
            "cid"=>$clientID,
            "requestid"=> "".time()
        );
        $content=json_encode($content);
        $header=array(
            'Content-Type: application/json',
            'authtoken:'.$authToken
        );
        $url='https://restapi.getui.com/v1/'.$appId.'/push_single';
        //
        $res=curl_post_json($url,$header,$content);
        $res=json_decode($res,true);
        // 将结果存储到redis中
        $this->cache_getui_res($res);
        return $res;
    }
    /**
     * 群发消息
     * - 向所有的app发送透传消息
     * @param $message
     * @return array
     */
    public function sendToAllTransmission($message){
        $appKey=config('getui')['appkey'];
        $authToken=$this->getAuthToken();
        $appId=config('getui')['appid'];
        $content=[
            'message'=>[
                "appkey"=>$appKey,
                "is_offline"=>false,
                "msgtype"=>"transmission"
            ],
            'transmission'=>[
                "transmission_type"=>false,
                "transmission_content"=>$message,
            ],
            'requestid'=>"".time(),
        ];
        $content=json_encode($content);
        $header=[
            'Content-Type: application/json',
            'authtoken:'.$authToken
        ];
        $url='https://restapi.getui.com/v1/'.$appId.'/push_app';
        //
        $res=curl_post_json($url,$header,$content);
        $res=json_decode($res,true);
        return $res;
    }


    /**
     * 群发消息
     * - 向所有的app发送notification消息
     * @param $content
     * @param $transmission_content string
     * @return array
     */
    public function sendToAllNotification($content,$transmission_content=''){
        $appKey=config('getui')['appkey'];
        $authToken=$this->getAuthToken();
        // var_dump($authToken);die;
        $appId=config('getui')['appid'];
        $content=[
            'message'=>[
                "appkey"=>$appKey,
                "is_offline"=>false,
                "msgtype"=>"notification"
            ],
            'notification'=>[
                'style'=>[
                    'type'=>1,
                    'text'=>$content['text'],
                    'title'=>$content['title'],
                    'logourl'=>$content['logourl'],
                    'is_ring'=>$content['is_ring'],
                    'is_vibrate'=>$content['is_vibrate'],
                    'is_clearable'=>$content['is_clearable'],
                ],
                "transmission_type"=>true,
                "transmission_content"=>$transmission_content
            ],
            'requestid'=>"".time(),
        ];
        $content=json_encode($content);
        $header=[
            'Content-Type: application/json',
            'authtoken:'.$authToken
        ];
        $url='https://restapi.getui.com/v1/'.$appId.'/push_app';
        //
        $res=curl_post_json($url,$header,$content);
        $res=json_decode($res,true);
        // 将结果存储到redis中
        $this->cache_getui_res($res);
        return $res;
    }
    /**
     *  群发消息
     *  - 向cidList中的cid发送消息
     *  - 需要调用两次接口，分别是save_list_body 和 push_list
     * @param $cidList array
     * @param $content
     * @param $transmission_content string
     * @return array
     */
    public function sendToListNotification($cidList=[],$content,$transmission_content=''){
        $appKey=config('getui')['appkey'];
        $authToken=$this->getAuthToken();
        $appId=config('getui')['appid'];
        $content=array(
            'message'=>[
                "appkey"=>$appKey,
                "is_offline"=>false,
                "msgtype"=>"notification"
            ],
            'notification'=>[
            'style'=>[
                'type'=>1,
                'text'=>$content['text'],
                'title'=>$content['title'],
                'logourl'=>$content['logourl'],
                'is_ring'=>$content['is_ring'],
                'is_vibrate'=>$content['is_vibrate'],
                'is_clearable'=>$content['is_clearable'],
                ],
                "transmission_type"=> true,
                "transmission_content"=> $transmission_content
            ],
        );
        $content=json_encode($content);
        $header=array(
            'Content-Type: application/json',
            'authtoken:'.$authToken
        );
        // 首先进行要发送的内容的保存
        $url='https://restapi.getui.com/v1/'.$appId.'/save_list_body ';
        // 成功之后获取taskid
        $res=curl_post_json($url,$header,$content);
        $res=json_decode($res,true);
        // 将结果存储到redis中
        $this->cache_getui_res($res);
        if($res['result']=='ok'){
            // 调用tolist接口
            $toListContent=[
                'cid'=>$cidList,
                'taskid'=>$res['taskid'],
                'need_detail'=>false,
            ];
            $toListContent=json_encode($toListContent);
            $url='https://restapi.getui.com/v1/'.$appId.'/push_list ';
            $res=curl_post_json($url,$header,$toListContent);
            $res=json_decode($res,true);
            // 将结果存储到redis中
            $this->cache_getui_res($res);
            return $res;
        }else{
            // 将结果存储到redis中
            $this->cache_getui_res($res);
            return $res;
        }
    }

    /**
     * 用于本地缓存个推API调用的结果
     * - 可以用于查看API调用的具体情况
     * - 本地缓存的key是 getui_res_list 
     * @param $res
     */
    protected function cache_getui_res($res){
        try{
            $nowList=Cache::get('getui_res_list');
            $arr=['res'=>$res,'time'=>date('Y-m-d H:i:s',time())];
            if(is_array($nowList)){
                array_push($nowList,$arr);
                // 如果已经数组了 则世界array_push
                Cache::set('getui_res_list',$nowList);
            }else{
                Cache::set('getui_res_list',[0=>$arr]);
            }
        }catch (\Exception $exception){

        }
    }
}