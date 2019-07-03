<?php
/**
 * Created by PhpStorm.
 * User: billy
 * Date: 2019/5/23
 * Time: 1:36 PM
 */
namespace  app\merchants\controller;

use app\common\controller\MerchantsBase;
use think\Db;
use think\Request;

class Merchants extends MerchantsBase
{

    protected $noNeedLogin = ['getSchool','getBack','getCategory'];
    //protected $noNeedLogin = ['*'];

    /**
     * 新建商家
     * @param  \think\Request  $request
     * @return \think\Response
     */

    public function createShop(Request $request)
    {
        $data = $request->param();
        $data['shop_id'] = $this->shop_id;
        $data['status'] = 1;
        $check = $this->validate($request->param(), 'Merchants');
        if ($check !== true) {
            $this->error($check);
        }

        Db::startTrans();
        try {
            model('ShopInfo')
                ->where('id',$data['shop_id'])
                ->update($data);

            $info = model('ShopMoreInfo')
                ->field('id')
                ->where('shop_id',$data['shop_id'])
                ->find();

            if ($info){
                model('ShopMoreInfo')
                    ->where('shop_id',$data['shop_id'])
                    ->update($data);

            }else{
                model('ShopMoreInfo')->insert($data);

            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        $this->success('success');

    }


    /**
     * 商家审核状态 
     * 
     */
    public function checkStatus()
    {
        $check_info = model('ShopInfo')->where('id',$this->shop_id)->field('remark,status,check_status')->find();
        if ($check_info['status'] == 2) { // 审核未通过
            $check_info['mb_remark'] = Db::name('check_status')->where('type','=',1)->where('id','in',$check_info['remark'])->column('name');
        }
        unset($check_info['remark']);
        return json_success('获取审核状态成功',['check_info'=>$check_info]);
    }
     

    /**
     * 设置商家已审核通过状态【前端单独用】
     */
    public function setCheckStatus()
    {
        $res = Db::name('shop_info')->where('id',$this->shop_id)->setField('check_status',1);
        if (!$res) {
            $this->error('设置失败');
        }
        $this->success('设置成功');
    }


    /**
     * 获取学校
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function getSchool()
    {
        // 学区列表
        $school_district_list = model('School')->field('id,name')->where('level',1)->select()->toArray();
        // 学校列表
        $school_list = model('School')->field('id,fid,name,longitude,latitude')->where('level',2)->select()->toArray();
        // 组装三维数组
        foreach ($school_district_list as $k => &$v) {
            foreach ($school_list as $ko => $vo) {
                if ($v['id'] == $vo['fid']) {
                    $v['children'][] = $vo;
                }
            }
        }

        $this->success('success',$school_district_list);
    }

    /**
     * 获取经营品类
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function getCategory()
    {
        $data = model('ManageCategory')->field('id,name,img')->select();

        $this->success('success',$data);
    }

    /**
     * 获取银行
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function getBack()
    {
        $data = model('Back')->field('id,name')->select();

        $this->success('success',$data);
    }

    /**
     * 修改密码
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function updatePwd(Request $request)
    {
        $old_password = $request->param('old_password');
        $new_password = $request->param('new_password');
        $true_password = $request->param('true_password');


        $data = model('ShopInfo')->where('id',$this->shop_id)->find();

        if (md5($old_password) != $data->password){
            $this->error('输入的旧密码不正确');
        }

        if ($new_password != $true_password){
            $this->error('两次密码不一致');
        }

        model('ShopInfo')->where('id',$this->shop_id)->update(['password'=>md5($new_password)]);

        $this->success('success');
    }

    /**
     * 评价管理
     * @param Request $request
     * @return \think\response\Json
     */

    public function getEvaluation(Request $request)
    {
        $page = $request->param('page',1);
        $pagesize = $request->param('pagesize',20);
        $type = $request->param('type');

        $where[] = ['shop_id','=',$this->shop_id];

        $count = model('ShopComments')->where($where)->count();
        $sum = model('ShopComments')->where($where)->sum('star');
        if ($count != 0){
            $data['star'] = round($sum / $count,2);
        }else{
            $data['star'] = 0;
        }
        //好评总数
        $hp_count  = model('ShopComments')->where($where)->where('star','>=',3)->count();
        //差评总数
        $cp_count  = model('ShopComments')->where($where)->where('star','<',3)->count();
        $data['all_count']  =$count;
        $data['hp_count']  =$hp_count;
        $data['cp_count']  =$cp_count;

        if ($type == 1){
            $where[] = ['star','>=',3];
        }elseif($type == 2){
            $where[] = ['star','<',3];
        }

        $list = Db::table('fun_shop_comments a ')
            ->join('fun_user b','a.user_id = b.id ')
            ->field('a.id,a.star,a.add_time,a.content,b.headimgurl,b.nickname')
            ->where($where)
            ->order('add_time desc')
            ->page($page,$pagesize)
            ->select();

        foreach ($list as &$value){
            $value['add_time'] = date('Y-m-d',$value['add_time']);
            $value['topis'] = Db::table('fun_shop_comments_tips a')
                ->join('fun_tips b','a.tips_id = b.id')
                ->field('b.name')
                ->where('a.comments_id',$value['id'])
                ->select();
        }
        $data['list']  =$list;

        $this->success('success',$data);

    }



}