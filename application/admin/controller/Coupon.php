<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use think\Db;
use app\common\model\ManageCategory;
use app\common\model\School;


/**
 * 平台红包控制器
 * @author Mike
 * date 2019/5/23
 */
class Coupon extends Base
{
    /**
     * 红包列表
     */
    public function index(Request $request)
    {
        // 经营品类列表
        $category_list = Db::name('manage_category')->field('id,name')->select();

        // 搜索条件
        $where = [];
        !empty($request->get('name/s')) ? $where[] = ['name','like',$request->get('name/s').'%'] : null;
        !empty($request->get('category/d')) ? $where[] = ['limit_use',['like',$request->get('category/d').',%'],['like','%,'.$request->get('category/d').',%'],['like','%,'.$request->get('category/d')],'or']: null;
        // 另一种写法 ，实现字段区间内查询，后续可根据数据量大的时候，判断两种写法哪一种写法效率更高【此写法适用于tp5.1.7以上版本】
        // !empty($request->get('category/d')) ? $where[] = ['','exp',Db::raw("FIND_IN_SET(1,limit_use)")]: null;
        !empty($request->get('status/d')) ? $where[] = ['status','=',$request->get('status/d')] : null;
        !empty($request->get('pagesize/d')) ? $pagesize = $request->get('pagesize/d') : $pagesize = 10;
    
        // 优惠券列表
        $coupon_list = Db::name('platform_coupon')->field('id,batch_id,name,user_type,face_value,threshold,start_time,end_time,other_time,limit_use,num,surplus_num,status,type')
                        ->where($where)->order('id desc')->paginate($pagesize)->each(function ($item, $key) {
                            // 设置优惠券中文状态
                            $item['mb_status'] = config('coupon_status')[$item['status']];
                            
                            // 优惠券发放类型
                            $item['mb_type'] = config('coupon_type')[$item['type']];
                            // 用户类型
                            $item['user_type'] = config('user_type')[$item['user_type']];
                            // 限品类
                            $item['limit_use'] = !empty($item['limit_use']) ? implode(',',Db::name('manage_category')->where('id','in',$item['limit_use'])->column('name')) : '全部';
                            // 有效期
                            $item['type'] == 2 ? $item['indate'] = date('Y-m-d',$item['start_time']).'-'.date('Y-m-d',$item['end_time']) : $item['indate'] = '领取日起'.$item['other_time'].'天';
                            return $item;
                        });

        $this->success('ok',['category_list'=>$category_list,'coupon_list'=>$coupon_list]);

    }


    /**
     * 展示新增优惠券页面 
     * 
     */
    public function add()
    {
        // 优惠券的覆盖范围 [学校]
        $sc_model = new School();
        $school_list = $sc_model->getSchoolList();

        // 经营品类列表
        $mg_model = new ManageCategory();
        $manage_category_list = $mg_model->getManageCategoryList();
        array_unshift($manage_category_list,['id'=>0,'name'=>'全部']);

        // 设置当前的红包批次ID
        $sum = Db::name('platform_coupon')->where('batch_id','like',date('ymd').'%')->count('id');
        $bacth_id = date('ymdH').'_No'.sprintf('%04d',$sum+1);

        $this->success('ok',['school_list'=>$school_list,'manage_category_list'=>$manage_category_list,'bacth_id'=>$bacth_id]);

    }
     

    /**
     * 保存新增优惠券
     * 
     */
    public function create(Request $request)
    {
        $data = $request->param();
        $data['add_time'] = time();
        if ($data['type'] == 2) {   // 平台发放
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
        } else {    // 自主领取、消费赠送、邀请赠送
            $data['start_time'] = 0;
            $data['end_time'] = 0;
        }
        $data['surplus_num'] = $data['num'];
        
        if (in_array('0',explode(',',$data['limmit_use']))) {   // 如果限制的品类存在 0 这个元素值，代表限制品类全选【因前端技术不好实现全选相关问题，即目前由后端处理】
            $data['limit_use'] = '0';
        }
        if (in_array('0',explode(',',$data['shop_ids']))) {   // 如果覆盖范围的商家存在 0 这个元素值，代表覆盖商家全选【因前端技术不好实现全选相关问题，即目前由后端处理】
            $data['shop_ids'] = '0';
        }

        // 验证表单数据
        $check = $this->validate($data, 'Coupon');
        if ($check !== true) {
            $this->error($check,201);
        }

        // 提交新增表单
        $result = Db::name('platform_coupon')->insert($data);
        if (!$result) {
            $this->error('添加失败',201);
        }

        $this->success('添加成功');

    }


    /**
     * 展示修改优惠券页面 
     * @param $id  优惠券表主键值
     * 
     */
    public function edit($id)
    {
        if (empty((int)$id) ) {
            $this->error('非法参数',201);
        }

        // 当前优惠券信息
        $coupon_info = Db::name('platform_coupon')->where('id',$id)->find();
        if ($coupon_info['type'] == 2) {
            $coupon_info['start_time'] = date('Y-m-d',$coupon_info['start_time']);
            $coupon_info['end_time'] = date('Y-m-d',$coupon_info['end_time']);
        }

        // 经营品类列表
        $mg_model = new ManageCategory();
        $manage_category_list = $mg_model->getManageCategoryList();
        array_unshift($manage_category_list,['id'=>0,'name'=>'全部']);

        // 限品类拼接中文数据
        $coupon_info['mb_limit_uses'] = !empty($coupon_info['limit_use']) ? implode(',',Db::name('manage_category')->where('id','in',$coupon_info['limit_use'])->column('name')) : '全部';
        
        // 覆盖学校拼接中文数据 && 商家拼接中文数据
        $coupon_info['mb_school_name'] = '';
        $coupon_info['mb_shop_names'] = '';
        if ($coupon_info['school_id']) {
            $school_arr = Db::name('school')->where('id','=',$coupon_info['school_id'])->field('fid,name')->find();
            $area_name = Db::name('school')->where('id','=',$school_arr['fid'])->value('name');
            $coupon_info['mb_school_name'] = $area_name.'/'.$school_arr['name'];

            $coupon_info['mb_shop_names'] = !empty($coupon_info['shop_ids']) ? implode(',',Db::name('shop_info')->where('id','in',$coupon_info['shop_ids'])->column('shop_name')) : '全部';
        }

        // 优惠券的覆盖范围 [学校]
        $sc_model = new School();
        $school_list = $sc_model->getSchoolList();

        $this->success('ok',['coupon_info'=>$coupon_info,'school_list'=>$school_list,'manage_category_list'=>$manage_category_list]);

    }


    /**
     * 保存修改优惠券
     * 
     */
    public function update(Request $request)
    {
        $data = $request->param();

        if (!isset($data['id']) || empty((int)$data['id'])) {
            $this->error('非法参数',201);
        }
        
        if ($data['type'] == 2) {   // 平台发放
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $data['other_time'] = 0;
        } else {    // 自主领取、消费赠送、邀请赠送
            $data['start_time'] = 0;
            $data['end_time'] = 0;
        }

        // 验证表单数据
        $check = $this->validate($data, 'Coupon');
        if ($check !== true) {
            $this->error($check,201);
        }

        $info = Db::name('platform_coupon')->where('id',$data['id'])->field('num,surplus_num,status')->find();
        /*********** $info['status']【此处可由前端传过来 `status` 的状态值进行判断，目前由后端查表获取】************************/
        // 当优惠券未发放时
        if ($info['status'] == 1) {
            if (in_array('0',explode(',',$data['limmit_use']))) {   // 如果限制的品类存在 0 这个元素值，代表限制品类全选【因前端技术不好实现全选相关问题，即目前由后端处理】
                $data['limit_use'] = '0';
            }
            if (in_array('0',explode(',',$data['shop_ids']))) {   // 如果覆盖范围的商家存在 0 这个元素值，代表覆盖商家全选【因前端技术不好实现全选相关问题，即目前由后端处理】
                $data['shop_ids'] = '0';
            }
            $data['surplus_num'] = $data['num'];
        }
        // 当优惠券已发放时
        if ($info['status'] == 2) {
            // 已领取数量
            $temp = $info['num'] - $info['surplus_num'];
            if ($data['num'] >= $temp) {
                $data['surplus_num'] = $data['num'] - $temp;
            } else {
                $this->error('发行量不能小于已领取的优惠券数量');
            }
        }
        // 提交表单
        $result = Db::name('platform_coupon')->update($data);
        if (!$result) {
            $this->error('修改失败',201);
        }
        
        $this->success('修改成功');

    }


    /**
     *  获取当前学校的店铺列表
     * @param $id 学校表主键值
     */
    public function getSchoolShop($id)
    {
        if (empty((int)$id)) {
            $this->error('非法参数',201);
        }
        //获取店铺列表
        $shop_list = Db::name('shop_info')->where('school_id',$id)->where('status','=',3)->field('id,shop_name')->select();
        array_unshift($shop_list,['id'=>0,'shop_name'=>'全部']);
        
        $this->success('获取当前学校的店铺列表成功',['shop_list'=>$shop_list]);
    }


    /**
     * 优惠券详情 
     * @param $id  优惠券表主键值
     */
    public function show($id)
    {
        if (empty((int)$id)) {
            $this->error('非法参数',201);
        }

        // 优惠券详情信息
        $coupon_info = Db::name('platform_coupon')->where('id',$id)->find();

        if ($coupon_info['type'] != 4) { // 非邀请赠送
            $coupon_info['school_name'] = model('School')->getNameById($coupon_info['school_id']);
        } else {
            $coupon_info['school_name'] = '全平台适用';
        }

        // 优惠券状态
        $coupon_info['mb_status'] = config('coupon_status')[$coupon_info['status']];
        // 用户类型
        $coupon_info['user_type'] = config('user_type')[$coupon_info['user_type']];
        // 限品类
        if ($coupon_info['limit_use']) {
            $coupon_info['limit_use'] = implode(',',Db::name('manage_category')->where('id','in',$coupon_info['limit_use'])->column('name'));
        } else {
            $coupon_info['limit_use'] = '全部';
        }
        // 有效期
        $coupon_info['type'] == 2 ? $coupon_info['indate'] = date('Y-m-d',$coupon_info['start_time']).'-'.date('Y-m-d',$coupon_info['end_time']) : $coupon_info['indate'] = '领取日起'.$coupon_info['other_time'].'天';
        // 发放方式
        $coupon_info['type'] = config('coupon_type')[$coupon_info['type']];

        // 参与优惠券的店铺信息
        if ($coupon_info['shop_ids']) {
            $shop_info = Db::name('shop_info')->where('id','in',$coupon_info['shop_ids'])->field('id,logo_img,shop_name,link_tel')->select();
        } else {
            $shop_info = Db::name('shop_info')->where('school_id','=',$coupon_info['school_id'])->field('id,logo_img,shop_name,link_tel')->select();
        }

        // 当前优惠券使用列表
        $coupon_used_list = model('my_coupon')->alias('mc')
                            ->join('user u','mc.user_id = u.id')   
                            ->join('platform_coupon pc','mc.platform_coupon_id = pc.id')
                            ->where('pc.id','=',$id)
                            ->field('pc.id,pc.name,pc.face_value,u.nickname,u.phone,mc.indate,mc.order_sn,mc.status')
                            ->select();

        $this->success('ok',['coupon_info'=>$coupon_info,'shop_info'=>$shop_info,'coupon_used_list'=>$coupon_used_list]);

    }
     
     
     /**
      * 设置优惠券状态 【发放/暂停发放/设为作废】
      * @param $id 优惠券主键值
      * @param $status 状态值
      */
     public function status($id,$status)
     {
        $result = Db::name('platform_coupon')->where('id',$id)->setField('status',$status);

        if (!$result) {
            $this->error('设置失败');
        }

        $this->success('设置成功');
     }
      
     


    
     
     

    
     
     
     
     





}
