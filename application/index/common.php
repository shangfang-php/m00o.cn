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
        $order_table        = tb($startTime);
        $order_record_table = ortb($startTime);
    }
    $where  =   array('o_u_id'=>$uid);
    if($endTime){
        $where['o_creattime']    =   array('between', [$startTime, $endTime]);
    }else{
        $where['o_creattime']    =   ['>=', $startTime];
    }
    //print_r($where);exit;
    $field  =   'sum(or_money) as allMoney, count(o_id) as orderNums';
    //$res    =   Db::table($table)->field($field)->where($where)->find();
//    !$res['allMoney'] && $res['allMoney'] = 0;
    
    $res    =   Db::table($order_table)->Alias('a')->field($field)->join($order_record_table.' b', 'a.o_ordernum = b.or_o_ordernum and or_u_id = '.$uid, 'left')->where($where)->find();
    !$res['allMoney'] && $res['allMoney'] = 0;
    return $res;
}

/**
 * checkAgentRelation()
 * 检测两个代理商是否存在代理关系
 * @param mixed $parentId 上级供应商
 * @param mixed $agentId 检索的代理商
 * @return void
 */
function checkAgentRelation($parentId, $agentId){
    $agentsInfo =   getUserChildAgents($parentId);
    if(!$agentsInfo){
        return FALSE;
    }
    
    foreach($agentsInfo as $val){
        if(in_array($agentId, $val)){
            return TRUE;
        }
    }
    return FALSE;
}

/**
 * getNotice()
 * 获取公告内容
 * @param mixed $uid 登录代理ID
 * @param string $userInfo 代理信息
 * @return void
 */
function getNotice($uid, $userInfo = ''){
    if(!$userInfo){
        $userInfo   =   getUserInfo($uid);
    }
    $u_idss =   $userInfo['u_u_idss'];
    $where  =   array('u_idss'=>$u_idss, 'is_close'=>0);
    $notice =   Db::table('notice_set')->field('content,createTime')->where($where)->find();
    return $notice ? $notice : '';
}
