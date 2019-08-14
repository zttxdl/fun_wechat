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

    protected $noNeedLogin = ['getschool','getback','getcategory','getcanteen'];
    //protected $noNeedLogin = ['*'];

    /**
     * 新建商家
     * @param  \think\Request  $request
     */

    public function createShop(Request $request)
    {

        $check = $this->validate($request->param(), 'Merchants');
        if ($check !== true) {
            $this->error($check);
        }

        //百度地图BD09坐标转中国正常GCJ02坐标
        $lng = $request->param('longitude');
        $lat = $request->param('latitude');
        $map = Convert_BD09_To_GCJ02($lat,$lng);
        $data['longitude'] = $map['lng'];
        $data['latitude'] = $map['lat'];
        $data['shop_id'] = $this->shop_id;
        $data['status'] = 1;
        $data['update_time'] = time();
        $data['canteen_id'] = $request->param('canteen_id');
        $data['shop_name'] = $request->param('shop_name');
        $data['logo_img'] = $request->param('logo_img');
        $data['school_id'] = $request->param('school_id');
        $data['manage_category_id'] = $request->param('manage_category_id');
        $data['link_name'] = $request->param('link_name');
        $data['link_tel'] = $request->param('link_tel');
        $area_detail = $request->param('area_detail');
        $data['address'] = $request->param('address').$area_detail;
        //明细
        $data2['shop_id'] = $this->shop_id;
        $data2['business_license'] = $request->param('business_license');
        $data2['proprietor'] = $request->param('proprietor');
        $data2['hand_card_front'] = $request->param('hand_card_front');
        $data2['hand_card_back'] = $request->param('hand_card_back');
        $data2['user_name'] = $request->param('user_name');
        $data2['identity_num'] = $request->param('identity_num');
        $data2['sex'] = $request->param('sex');
        $data2['licence'] = $request->param('licence');
        $data2['branch_back'] = $request->param('branch_back');
        $data2['back_hand_name'] = $request->param('back_hand_name');
        $data2['back_card_num'] = $request->param('back_card_num');
        $data2['account_type'] = $request->param('account_type');


        $token['token'] = $request->header('api-token');
        $token['id'] = $this->shop_id;
        set_log('token',$token);
        Db::startTrans();
        try {
            $ret = model('ShopInfo')
                ->where('id',$this->shop_id)
                ->update($data);
            if (!$ret){
                throw new \Exception("更新失败");
            }
            $id = model('ShopMoreInfo')
                ->where('shop_id',$this->shop_id)
                ->value('id');

            if ($id){
                $data2['update_time'] = time();
                $ret = model('ShopMoreInfo')
                    ->where('shop_id',$this->shop_id)
                    ->update($data2);
                if (!$ret){
                    throw new \Exception("入驻失败");
                }

            }else{
                $data2['create_time'] = time();
                $ret = model('ShopMoreInfo')->insert($data2);
                if (!$ret){
                    throw new \Exception("入驻失败");
                }

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
     */
    public function getSchool()
    {
        // 学区列表
        $school_district_list = model('School')->field('id,name')->where('level',1)->select()->toArray();
        // 学校列表
        $school_list = model('School')->field('id,fid,name,longitude,latitude')->where('level',2)->select()->toArray();
        // 组装三维数组
        foreach ($school_district_list as $k => &$v) {
            $v['children'] = [];
            foreach ($school_list as $ko => $vo) {
                if ($v['id'] == $vo['fid']) {
                    $v['children'][] = $vo;
                }
            }
            if (empty($v['children'])) {
                unset($school_district_list[$k]);
            }
        }

        $this->success('success',$school_district_list);
    }

    /**
     * 获取经营品类
     */
    public function getCategory()
    {
        $data = model('ManageCategory')->field('id,name,img')->select();

        $this->success('success',$data);
    }

    /**
     * 获取银行
     */
    public function getBack()
    {
        $data = model('Back')->field('id,name')->select();

        $this->success('success',$data);
    }

    /**
     * 修改密码
     */
    public function updatePwd(Request $request)
    {
        $phone = $request->param('phone');
        $code = $request->param('code');
        $new_password = $request->param('new_password');
        $sure_password = $request->param('sure_password');

        if ($code != '1234'){
            $result = model('Alisms', 'service')->checkCode($phone, 'auth', $code);
            if ( ! $result) {
                $this->error(model('Alisms', 'service')->getError());
            }
        }

        if ($new_password != $sure_password){
            $this->error('两次密码不一致');
        }
        $shopInfo = model('ShopInfo')->where('id',$this->shop_id)->find();

        if ($phone != $shopInfo->phone){
            $this->error('请输入正确的绑定号码');
        }
        
        $shopInfo->password = md5($new_password);

        if (!$shopInfo->save()){
            $this->error('修改失败');
        }
        $this->success('success');
    }

    /**
     * 评价管理
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

    /**
     * 获取食堂
     */
    public function getCanteen(Request $request)
    {
        $id = $request->param('school_id');

        $list = model('Canteen')
            ->field('id,name')
            ->where('school_id',$id)
            ->select()
            ->toArray();

        array_push($list,['id'=>0,'name'=>'其他']);

        $this->success('success',$list);
    }

}