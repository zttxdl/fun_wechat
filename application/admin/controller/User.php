<?php


namespace app\admin\controller;


use think\Controller;
use think\Db;
use think\Request;
//use think\facade\Validate;

class User extends Controller
{
    /**
     * 会员列表
     * @param $status  会员状态 【1：正常会员 2：禁用的会员】
     */
    public function getList(Request $request,$status)
    {

        $where[] = ['status','=',$status];
        // 搜索条件
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 10;
        !empty($request->get('name/s')) ? $where[] = ['nickname','=',$request->get('name/d')] : null;
        !empty($request->get('type/d')) ? $where[] = ['type','=',$request->get('type/d')] : null;

        $user_list = model('User')
                    ->field('id,nickname,phone,add_time,last_login_time,type')
                    ->order('id','desc')->where($where)->paginate($pagesize)->each(function($item, $key){
                        $temp = Model('Orders')->getUserConsume($item['id']);
                        $item['count_num'] = $temp['count_num'];
                        $item['total_money'] = !empty($temp['total_money']) ? sprintf("%.2f", $temp['total_money']) : 0;
                        return $item;
                    });

        $this->success('获取成功',$user_list);
    }


    /**
     * 会员详情
     */
    public function getDetail(Request $request)
    {
        $uid = $request->param('uid');

        if(!$uid) {
            $this->error('Uid 不能为空');
        }

        $result = [];


        $result['user_detail'] = model('User')->where('id',$uid)->field('nickname,phone,last_login_time,add_time,headimgurl,type')->find();

        $data = Model('Orders')->getUserConsume($uid);
        //会员消费总金额
        $result['user_detail']['total_money'] = !empty($data['total_money']) ? sprintf("%.2f", $data['total_money']) : 0;
        //累计交易次数
        $result['user_detail']['count_num'] = $data['count_num'];

        //收货地址信息
        $result['user_address'] = model('ReceivingAddr')->alias('a')
            ->join('user b','a.user_id = b.id')
            ->leftJoin('school c','a.school_id = c.id')
            ->where('a.user_id','=',$uid)
            ->field('a.id,a.name,a.phone,b.sex,c.name as school_name ,a.area_detail')
            ->select();
        
        foreach ($result['user_address'] as &$row) {
            $row['sex'] = config('sex')[$row['sex']];
        }

        //红包信息
        $result['user_coupon'] = Db::name('my_coupon')->alias('a')
            ->leftJoin('platform_coupon b','a.platform_coupon_id = b.id')
            ->where('a.user_id','=',$uid)
            ->field('a.id,a.phone,b.name as coupon_name,b.face_value,a.indate,b.limit_use,b.threshold')
            ->select();

        foreach ($result['user_coupon'] as $k => &$v) {
            // 发放机构
            $v['organization'] = '平台';
            // 限品类
            $v['limit_use'] = !empty($v['limit_use']) ? implode(',',Db::name('manage_category')->where('id','in',$v['limit_use'])->column('name')) : '全部';
        } 
        
         $this->success('获取成功',$result);

    }


    /**
     * 设置会员状态 
     * 
     */
    public function setStatus(Request $request)
    {
        $id = $request->param('id');
        $status = $request->param('status');

        $result = Db::name('user')->where('id','=',$id)->setField('status',$status);
        if (!$result) {
            $this->error('设置失败');
        }
        $this->success('设置成功');
    }
     
}