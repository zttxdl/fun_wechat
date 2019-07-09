<?php
namespace app\console;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

//执行脚本  php think
class Orders extends Command
{
    protected function configure()
    {
        $this->setName('orders')->setDescription('超时订单自动取消');
    }
 
    /**
     * 超时订单自动取消 付款减库存需要加上库存（每分钟/次）
     */
    protected function execute(Input $input, Output $output)
    {

        
        $orderlist=Db::table('fun_orders')->where('add_time','<',time()-15*60)->where('pay_status',0)->where('status',1)->select();
       
        foreach ($orderlist as $k => $v) {
           Db::table('fun_orders')->where('id',$v['id'])->update(['trading_closed_time'=>time(),'status'=>9]);
           //付款减库存的商品
           $goodslist=Db::table('fun_orders_info')->where('orders_id',$v['id'])->field('product_id,num')->select();

           //如果使用红包 状态回滚
            if($v['platform_coupon_money'] > 0){
                Db::table('fun_my_coupon')->where('id',$v['platform_coupon_id'])->setField('status',1);
                
            }

            foreach ($goodslist as $key => $value) {
                $today = date('Y-m-d',time());
                //加库存
                Db::table('fun_today_deals')
                ->where('product_id',$value['product_id'])
                ->where('today',$today)
                ->setInc('num',$value['num']);
            }
        }
        $num = count($orderlist);

        $output->writeln("OrdersCommand:$num");
    
    }
}