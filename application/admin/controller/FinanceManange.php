<?php 
namespace app\admin\controller;


use app\common\controller\Base;
use think\Request;
use think\Db;
use app\admin\controller\Transfer;

class FinanceManange extends Base
{
    protected $source = ['1'=>'骑手端','2'=>'商家端','3'=>'食堂端'];
    protected $toMention = ['1'=>'微信','2'=>'银行卡','3'=>'银行卡'];
    protected $status = ['1'=>'待审核','2'=>'审核失败','3'=>'提现成功'];


	public function getWithdraw(Request $request)
	{
        $page = $request->param('page');
        $page_size = $request->param('pageSize');
        $source = $request->param('source');//0:全部 1:骑手端 2:商家端 3:食堂端
        $status = $request->param('status');//0:全部 1:待审核 2:审核不成功 3:提现成功

        $rider_result = [];//骑手端提现数据
        $shop_result = [];//商家端端提现数据
        $canteen_result = [];//食堂端提现数据
        $map = [];

        if($status) {
            $map[] = ['a.status','=',$status];
        }

        switch ($source) {
            case 1 :
                $rider = Db::name('rider_income_expend')
                    ->alias('a')
                    ->leftJoin('rider_info b','a.rider_id = b.id')
                    ->field('a.*,b.name')
                    ->where($map)
                    ->where('type','=','2')
                    ->order('a.id DESC')
                    ->paginate($page_size)
                    ->toArray();

                if($rider['data']) {
                    foreach ($rider['data'] as $row)
                    {
                        $rider_result['info'][] = [
                            'id' => $row['id'],
                            'order_sn' => $row['serial_number'],
//                    'name' => Db::name('rider_info')->where('id',$row['rider_id'])->value('name'),
                            'name' => $row['name'],
                            'source' => !empty($source) ? $this->source[$source] : '骑手端',
                            'to_mention ' => !empty($source) ? $this->toMention[$source] : '微信',
                            'card' => '',
                            'money' => $row['current_money'],
                            'add_time' => date('Y-m-d H:i:s',$row['add_time']),
                            'status' => $this->status[$row['status']],
                        ];
                    }
                }else{
                    $rider_result['info'] = [];
                }

                $rider_result['count'] = $rider['total'];
                $rider_result['page'] = $rider['current_page'];
                $rider_result['pageSize'] = $rider['per_page'];
                if($source == 1) {
                    $this->success('获取成功',$rider_result);
                };
                break;

            case 2 :
                $shop = Db::name('withdraw')
                    ->alias('a')
                    ->leftJoin('shop_info b','a.shop_id = b.id')
                    ->field('a.*,b.shop_name')
                    ->where($map)
                    ->where('type','=','2')
                    ->order('a.id DESC')
                    ->paginate($page_size)
                    ->toArray();
                if($shop['data']) {
                    foreach ($shop['data'] as $row)
                    {
                        $shop_result['info'][] = [
                            'id' => $row['id'],
                            'order_sn' => $row['withdraw_sn'],
//                    'name' => Db::name('shop_info')->where('id',$row['shop_id'])->value('shop_name'),
                            'name' => $row['shop_name'],
                            'source' => !empty($source) ? $this->source[$source] : '商家端',
                            'to_mention ' => !empty($source) ? $this->toMention[$source] : '银行卡',
                            'card' => $row['card'],
                            'money' => $row['money'],
                            'add_time' => date('Y-m-d H:i:s',$row['add_time']),
                            'status' => $this->status[$row['status']],
                        ];
                    }
                }else{
                    $shop_result['info'] = [];
                }


                $shop_result['count'] = $shop['total'];
                $shop_result['page'] = $shop['current_page'];
                $shop_result['pageSize'] = $shop['per_page'];
                if($source == 2){
                    $this->success('获取成功',$shop_result);
                };
                break;
            case 3:
                //食堂端
                $canteen = Db::name('canteen_income_expend')
                    ->alias('a')
                    ->leftJoin('canteen b','a.canteen_id = b.id')
                    ->leftJoin('canteen_account c','a.canteen_id = c.canteen_id')
                    ->field('a.*,b.name,c.back_num')
                    ->where($map)
                    ->where('type','=','2')
                    ->order('a.id DESC')
                    ->paginate($page_size)
                    ->toArray();

                if($canteen['data']) {
                    foreach ($canteen['data'] as $row)
                    {
                        $canteen_result['info'][] = [
                            'id' => $row['id'],
                            'order_sn' => $row['serial_number'],
//                    'name' => Db::name('rider_info')->where('id',$row['rider_id'])->value('name'),
                            'name' => $row['name'],
                            'source' => !empty($source) ? $this->source[$source] : '食堂端',
                            'to_mention ' => !empty($source) ? $this->toMention[$source] : '银行卡',
                            'card' => $row['back_num'],
                            'money' => $row['money'],
                            'add_time' => date('Y-m-d H:i:s',$row['add_time']),
                            'status' => $this->status[$row['status']],
                        ];
                    }
                }else{
                    $canteen_result['info'] = [];
                }

                $canteen_result['count'] = $canteen['total'];
                $canteen_result['page'] = $canteen['current_page'];
                $canteen_result['pageSize'] = $canteen['per_page'];

                if($source == 3){
                    $this->success('获取成功',$canteen_result);
                };
                break;
        }









        /*del by ztt 20180822 全部的数据暂时不展示
         * $data['info'] = array_merge($rider_result['info'],$shop_result['info']);
        $data['count'] = $rider_result['count'] + $shop_result['count'];
        $data['page'] = $rider_result['page'];
        $data['pageSize'] = $rider_result['pageSize'];
        $this->success('获取成功',$data);*/
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
        $source = $request->param('source');//1:骑手端 2:商家端 3:食堂端
        $id = $request->param('id');//提现ID
        $remark = $request->param('remark','');//审核不通过理由

        if($source == 1){
        	$this->rider_tx($status,$id,$remark);
        }

        if($source == 2) {
        	$this->shop_tx($status,$id,$remark);
        }

        if($source == 3) {
            $this->canteen_tx($status,$id,$remark);
        }
        
    }


    /**
     * 【商家提现 操作】
     * @param $status 审核状态  1:审核成功 2:审核不成功
     * @param $id 提现ID
     * @param string $remark 审核不通过原因ID的集合 [1,2,3]
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
     * 【食堂提现 操作】
     * @param $status 审核状态  1:审核成功 2:审核不成功
     * @param $id 提现ID
     * @param string $remark 审核不通过原因ID的集合 [1,2,3]
     */
    public function canteen_tx($status,$id,$remark='')
    {
        if($status == 1){
            Db::name('canteen_income_expend')->where('id',$id)->setField('status',3);
            $this->success('审核通过');
        }else{
            Db::name('canteen_income_expend')->where('id',$id)->update([
                    'status' => 2,
                    'remark' => $remark
                    ]);//更新审核失败状态
            $this->success('审核不通过');
        }
        
    }



    /**
     * 【骑手提现 操作】
     * @param $status 审核状态  1:审核成功 2:审核不成功
     * @param $id 提现ID
     * @param string $remark 审核不通过原因ID的集合 [1,2,3]
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

        if($source == 3) {
            $remark = Db::name('canteen_income_expend')->where('id',$id)->value('remark');
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

    /**
     * 获取银行账户信息
     */
    public function getCardInfo(Request $request)
    {
        $id = $request->param('id');
        $source = $request->param('source');//1:骑手端 2:商家端 3:食堂端

        if(empty($source)) {
            $this->error('来源不能为空');
        }

        if(empty($id)) {
            $this->error('ID不能为空');
        }

        if($source == 2) {
            $res = Db::name('withdraw')
                    ->alias('a')
                    ->leftJoin('shop_more_info b','a.shop_id = b.shop_id')
                    ->field('b.back_card_num as back_num,b.branch_back as back_name,b.back_hand_name as name')
                    ->where('a.id',$id)
                    ->find();
            $this->success('账户获取成功',$res);
        }

        if($source == 3) {
            $res = Db::name('canteen_income_expend')
                    ->alias('a')
                    ->leftJoin('canteen_account b','a.canteen_id = b.canteen_id')
                    ->field('b.back_num,b.back_name,b.name')
                    ->where('a.id',$id)
                    ->find();
            $this->success('账户获取成功',$res);
        }
    }
}
	
 ?>