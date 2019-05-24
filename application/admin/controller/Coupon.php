<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

/**
 * 平台红包控制器
 * @author Mike
 * date 2019/5/23
 */
class Coupon extends Controller
{
    /**
     * 红包列表
     */
    public function index(Request $request)
    {3
        // 经营品类列表
        $category_list = Db::name('manage_category')->where('level',2)->field('id,name')->select();

        // 搜索条件
        !empty($request->input('get.name/s')) ? $where['name'] = ['like',$request->input('get.name/s').'%'] : null;
        !empty($request->input('get.category/d')) ? $where['limit_use'] = ['between',array_column($category_list,'id')]: null;
        !empty($request->input('get.status/d')) ? $where['status'] = $request->input('get.status/d') : null;
        
        // 优惠券列表
        $coupon_list = Db::name('platform_coupon')->field('id,batch_id,name,user_type,face_value,threshold,start_time,end_time,other_time,limit_use,num,status')->paginate(10)->each(function ($item, $key) {
            // 优惠券状态
            $item['status'] = config('coupon_status')[$item['status']];
            // 用户类型
            $item['user_type'] = config('coupon_status')[$item['user_type']];
            // 限品类
            $item['limit_use'] = Db::name('manage_category')->where('id','in',$item['limit_use'])->value('name');
            // 有效期
            $item['type'] == 2 ? $item['indate'] = date('Y-m-d',$item['start_time']).'-'.date('Y-m-d',$item['end_time']) : $item['indate'] = '领取日起'.$item['other_time'].'天';
            
            return $item;
        });

        return json_success('ok',['category_list'=>$category_list,'coupon_list'=>$coupon_list]);

    }


    /**
     * 展示新增优惠券页面 
     * 
     */
    public function add()
    {
        $school_list = '';
    }
     

    /**
     * 保存新增优惠券
     * 
     */
    public function create(Request $request)
    {
        $data = $request->input('post.');
        if ($data['type'] == 2) {
            $data['start_time'] = date('Y-m-d',$data['start_time']);
            $data['end_time'] = date('Y-m-d',$data['end_time']);
        }

        // 验证表单数据
        $check = $this->validate($data, 'Coupon');
        if ($check !== true) {
            return json_error($check,201);
        }

        // 提交新增表单
        $result = Db::name('platform_coupon')->insert($data);
        if (!$result) {
            return json_error('添加失败',201);
        }

        return json_success('添加成功');

    }
     


    
     
     

    
     
     
     
     





}
