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
        $this->setName('orders')->setDescription('Here is the auto join  weal');
        //设置参数
        $this->addArgument('limit', null, '页码', null);//页码
    }
 
    /**
     * 超时订单自动取消 付款减库存需要加上库存（每分钟/次）
     */
    protected function execute(Input $input, Output $output)
    {
        
        $orderlist=Db::table('fun_orders')->where('add_time','<',time()-15*60)->where('pay_status',0)->where('status',1)->column('id');
       
        foreach ($orderlist as $k => $v) {
           Db::table('fun_orders')->where('id',$v)->update(['trading_closed_time'=>time(),'order_status'=>9]);
           //付款减库存的商品
           $goodslist=Db::table('fun_orders_info')->where('orders_id',$v)->field('product_id,num')->select();
           foreach ($goodslist as $key => $value) {
                $today = date('Y-m-d',time());
                //加库存
                Db::table('fun_today_deals')
                ->where('product_id',$value->product_id)
                ->where('today',$today)
                ->setInc('num',$value->num);
           }
        }
    
    }
}