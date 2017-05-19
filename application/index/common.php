<?php
use think\Db;

/**
 * getUserMoneyOrder()
 * 获取代理商指定时间段金额及订单总数
 * @param mixed $uid
 * @param mixed $startTime
 * @param integer $endTime
 * @return void
 */
function getUserMoneyOrder($uid, $startTime, $endTime = 0, $table = ''){
    if(!$table){
        list($year, $month) = explode('-', date('y-m'));
        $table  =   'order_record_'.$year.'_'.$month.'_tb';
    }
    $where  =   array('or_u_id'=>$uid, 'or_o_creattime'=>['>=', $startTime]);
    if($endTime){
        $where['or_o_creattime'] = ['<=', $endTime];
    }
    $field  =   'sum(or_money) as allMoney, count(or_id) as orderNums';
    $res    =   Db::table($table)->field($field)->where($where)->find();
    !$res['allMoney'] && $res['allMoney'] = 0;
    return $res;
}
