<?php 
namespace app\admin\controller;


use app\common\controller\Base;
use think\Request;
use think\Db;
use app\admin\controller\Transfer;

class FinanceManange extends Base
{
    protected $source = ['1'=>'骑手端','2'=>'商家端'];
    protected $toMention = ['1'=>'微信','2'=>'银行卡'];
    protected $status = ['1'=>'待审核','2'=>'审核失败','3'=>'提现成功'];


	public function getWithdraw(Request $request)
	{
        $page = $request->param('page');
        $page_size = $request->param('pageSize');
        $source = $request->param('source');//0:全部 1:骑手端 2:商家端
        $status = $request->param('status');//1:待审核 2:审核不成功 3:提现成功

        $rider_result = [];//骑手端提现数据
        $shop_result = [];//商家端端提现数据
        $map = [];

        if($status) {
            $map[] = ['a.status','=',$status];
        }

        $rider = Db::name('rider_income_expend')
        		->alias('a')
                ->leftJoin('rider_info b','a.rider_id = b.id')
                ->field('a.*,b.name')
        		->where($map)
        		->where('type','=','2')
        		->order('a.id DESC')
        		->paginate($page_size)
        		->toArray();
        foreach ($rider['data'] as $row)
        {
            $rider_result['info'][] = [
                'id' => $row['id'],
                'order_sn' => $row['serial_number'],
                'name' => Db::name('rider_info')->where('id',$row['rider_id'])->value('name'),
                'source' => isset($source) ? $this->source[$source] : '骑手端',
                'to_mention ' => isset($source) ? $this->toMention[$source] : '微信',
                'card' => '',
                'money' => $row['current_money'],
                'add_time' => $row['add_time'],
                'status' => $this->status[$row['status']],
            ];
        }
        $rider_result['count'] = $rider['total'];
        $rider_result['page'] = $rider['current_page'];
        $rider_result['pageSize'] = $rider['per_page'];

        $shop = Db::name('withdraw')
        			->alias('a')
                	->leftJoin('shop_info b','a.shop_id = b.id')
                	->field('a.*,b.shop_name')
        			->where($map)
        			->where('type','=','2')
        			->order('a.id DESC')
        			->paginate($page_size)
        			->toArray();
        // dump($shop);exit;
        foreach ($shop['data'] as $row)
        {
            $shop_result['info'][] = [
                'id' => $row['id'],
                'order_sn' => $row['withdraw_sn'],
                'name' => Db::name('shop_info')->where('id',$row['shop_id'])->value('shop_name'),
                'source' => isset($source) ? $this->source[$source] : '商家端',
                'to_mention ' => isset($source) ? $this->toMention[$source] : '银行卡',
                'card' => $row['card'],
                'money' => $row['money'],
                'add_time' => $row['add_time'],
                'status' => $this->status[$row['status']],
            ];
        }

        $shop_result['count'] = $shop['total'];
        $shop_result['page'] = $shop['current_page'];
        $shop_result['pageSize'] = $shop['per_page'];
        if($source == 1) {
            $this->success('获取成功',$rider_result);
        }
        if($source == 2){
            $this->success('获取成功',$shop_result);
        }

        // dump($rider_result[]);exit;

        $data['info'] = array_merge($rider_result['info'],$shop_result['info']);
        $data['count'] = $rider_result['count'] + $shop_result['count'];
        $data['page'] = $rider_result['page'];
        $data['pageSize'] = $rider_result['pageSize'];
        $this->success('获取成功',$data);
	}

    /**
     * 提现操作
     * @param Request $request
     */
	public function action(Request $request)
    {
    	$data = $request->param();
        $check = $this->validate($data,'FinanceManange');

        if($check !== true) {
        	$this->error($check);
        }

        $status = $request->param('status');////1:审核成功 2:审核不成功
        $source = $request->param('source');//1:骑手端 2:商家端
        $id = $request->param('id');//提现ID
        $remark = $request->param('remark','');//审核不通过理由

        if($source == 1){
        	$this->rider_tx($status,$id,$remark);
        }

        if($source == 2) {
        	$this->shop_tx($status,$id,$remark);
        }
        
    }


    /**
    *【商家提现 操作】
    * remark 如果
    */
    public function shop_tx($status,$id,$remark='')
    {
    	if($status == 1){
    		Db::name('withdraw')->where('id',$id)->setField('status',3);
        	$this->success('审核通过');
    	}else{
    		Db::name('withdraw')->where('id',$id)->update([
	                'status' => 2,
	                'remark' => $remark
		            ]);//更新审核失败状态
		    $this->success('审核不通过');
    	}
    	
    }

    /**
    *【骑手提现 操作】
    */
    public function rider_tx($status,$id,$remark='')
    {
    	if($status == 1) {//审核成功
    		$rider = Db::name('rider_income_expend')
        		->alias('a')
                ->leftJoin('rider_info b','a.rider_id = b.id')
                ->field('a.current_money,a.serial_number,b.openid')
                ->where('a.id',$id)
                ->find();
	    	//连接微信企业打款 start
	    	$wx_tx = new Transfer();
	    	$res = $wx_tx->sendMoney($rider['current_money'],$rider['openid'],$rider['serial_number']);

	    	if ($res == '企业付款成功') {
	    		$this->success($res);
			}
			$this->error($res);

	    	//连接微信企业打款 end
    	}else{
    		Db::name('rider_income_expend')->where('id',$id)->update([
	                'status' => 2,
	                'remark' => $remark
		            ]);;//更新审核失败状态
		    $this->success('审核不通过');
    	}
    	
    }

    /**
     * 查看原因
     */
    public function getCheck(Request $request)
    {
    	// dump(config('tx_check'));
        $id = $request->param('id');
        $source = $request->param('source');//1:骑手端 2:商家端

        if(empty($source)) {
        	$this->error('来源不能为空');
        }

        if(empty($id)) {
        	$this->error('ID不能为空');
        }

        $tx_check = config('tx_check');//不通过原因

        if($source == 1) {
        	$remark = Db::name('rider_income_expend')->where('id',$id)->value('remark');
        }

        if($source == 2) {
        	$remark = Db::name('withdraw')->where('id',$id)->value('remark');
        }


        $check_names = [];

    	$remark = explode(',',$remark);//1,2,3 [1,2,3]. in_array('1',[1,2,3]) echo $check[$key]

    	foreach ($remark as $row) {
    		if(in_array($row, array_keys($tx_check))) {
    			$check_names[] = $tx_check[$row];
    		}
    	}

    	$this->success('获取成功',$check_names);

    }

    /**
     * 填写不成功列表
     */
    public function getRemark()
    {
        $tx_check = config('tx_check');//不通过原因

        $this->success('获取成功',$tx_check);
        
    }
}
	
 ?>