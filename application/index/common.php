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
 * getUserChildAgents()
 * 获取下级代理信息
 * @param mixed $uid 用户ID
 * @param string $userInfo 用户信息
 * @param bool $isDepth 是否逐级查询 默认是
 * @return void
 */
function getUserChildAgents($uid, $userInfo = '', $isDepth = true){
    if(!$userInfo){
        $userInfo   =   getUserInfo($uid);
    }
    $return =   array();
    $u_leve =   $userInfo['u_leve'];
    if($u_leve == 3){
        return  $return;
    }
    
    $table  =   'user_tb';
    $where['u_parent_u_id'] =   $uid;
    $res    =   Db::table($table)->field('u_id')->where($where)->select();
    if(!empty($res)){
        $agents =   get_field_array('u_id', $res);
        $key    =   $u_leve + 1;
        $return[$key] = $agents; ##下级代理商集合
        if($isDepth){
            $times  =   2 - $u_leve; ##需要循环拉取下级代理的次数
            if($times > 0){
                for($i = 1; $i<= $times; $i++){
                    $where['u_parent_u_id'] =   ['in', $agents];
                    $res    =   Db::table($table)->field('u_id')->where($where)->select();
                    if(empty($res)){
                        break;
                    }
                    $agents =   get_field_array('u_id', $res);
                    $key += 1;
                    $return[$key] = $agents;
                }
            }
        }
    }
    return $return;
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
