<?php
namespace app\index\controller;
//use app\index\model\index as indexo;
use think\Db;
use think\Request;

/**
 * Count
 * 数据统计界面
 * @package m00o.cn
 * @author Gary
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class Count extends common
{
	public function index(){
        $uid    =   session('usid');
        $type   =   input('type');
        
        $date   =   date('Y-m-d');
        switch($type){
            case 'today':
                $start_date =   strtotime($date); ##当天0点
                $end_date   =   '';
                break;
            case 'yestorday':
                $start_date =   strtotime(date('Y-m-d', strtotime('-1 day'))); ##前一天0点
                $end_date   =   strtotime($date) - 1; ##前一天23：59：59
                break;
            case 'month':
                $start_date =   strtotime(date('Y-m')); ##当月1号0点
                $end_date   =   '';
                break;
            case 'lastmonth':
                $start_date =   strtotime(date('Y-m', strtotime('-1 month'))); ##上月1号0点
                $end_date   =   strtotime(date('Y-m')) -1; ##上月最后一天 23：59：59
                break;
            default:
                 alert('类型非法', url('index/index'));
        }
        
        $data   =   $this->get_user_count_data($start_date, $end_date); ##获取用户统计数据
        
		$this->assign($data);
        $this->assign('type', $type);
		return $this->fetch('count_index');
	}
    
    /**
     * Count::get_user_count_data()
     * 获取用户统计数据
     * @param mixed $start_date
     * @param mixed $end_date
     * @return void
     */
    public function get_user_count_data($start_date, $end_date = ''){
        $uid    =   session('usid');
        $return =   array();
        $mydata =   getUserMoneyOrder($uid, $start_date, $end_date);
        $return['mydata']   =   $mydata;
        
        $childAgents    =   getUserChildAgents($uid); ##获取下级代理商信息
        if(!empty($childAgents)){
            $agentNums      =   0; ##代理商总数
            $agentsDataCount=   array(); ##直接下级代理订单金额、总数及贡献数据统计
            
            /** 获取合伙人订单总数及自己的提成**/
            $allAgents  =   array();
            $i          =   0;
            foreach($childAgents as $val){
                $i == 0 && $agents = $val; ##直接下级代理
                $agentNums  +=  count($val);
                $allAgents  =   array_merge($allAgents, $val);
                $i++;
            }
            unset($childAgents);
            
            $order_table    =   tb($start_date);
            $order_record_table=ortb($start_date);
            if($end_date){
                $where['o_creattime']   =   ['between', [$start_date, $end_date]];
            }else{
                $where['o_creattime']   =   ['>=', $start_date];
            }
            $where['o_u_id']    =   ['in', $allAgents];
            
            $orderCount =   Db::table($order_table)->Alias('a')->field('count(a.o_id) total, sum(or_money) money')->join($order_record_table.' b', 'a.o_ordernum = b.or_o_ordernum and or_u_id = '.$uid, 'left')->where($where)->find();
            //print_r($orderCount);exit;
            $orderCount['money'] = round($orderCount['money'],0);
            /** end**/
            $agentsDataCount    =   $this->get_agents_data_count($start_date, $end_date, $agents, $uid); ##获取下级代理订单信息
            
            $return['agentNums']        =   $agentNums;
            $return['orderCount']       =   $orderCount;
            $return['agentsDataCount']  =   $agentsDataCount;
        }else{
            $return['agentNums']        =   0;
            $return['orderCount']       =   array('money'=>0, 'total'=>0);
            $return['agentsDataCount']  =   array();
        }
        return $return;
    }
    
    /**
     * Count::get_agents_data_count()
     * 获取下级代理订单统计信息
     * @param mixed $start_date
     * @param mixed $end_date
     * @param mixed $agents 下级代理集合
     * @return void
     */
    public function get_agents_data_count($start_date, $end_date, $agents, $uid){
        $order_table    =   tb($start_date);
        $order_record_table=ortb($start_date);
        if($end_date){
            $where['o_creattime']   =   ['between', [$start_date, $end_date]];
        }else{
            $where['o_creattime']   =   ['>=', $start_date];
        }
        $where['o_u_id']    =   ['in', $agents];
        $return             =   array(); ##返回的数据
        //代理商贡献金额
        //$userMoney  =   Db::table($order_table)->Alias('a')->field('sum(or_money) money,o_u_id')->join($order_record_table.' b', 'a.o_ordernum = b.or_o_ordernum and or_u_id = '.$uid, 'left')->where($where)->group('o_u_id')->select();
        $agentOrders    =   Db::table($order_table)->Alias('a')->field('sum(or_money) money,count(o_id) nums, o_u_id')->join($order_record_table.' b', 'a.o_ordernum = b.or_o_ordernum and or_u_id = o_u_id', 'left')->where($where)->group('o_u_id')->order('money', 'desc')->limit(10)->select();
        if(!empty($agentOrders)){
            $agents =   array();
            foreach($agentOrders as $val){
                $o_u_id =   $val['o_u_id'];
                $val['agentMoney']  =   0; ##上级提成
                $val['u_leve']      =   0;
                $return[$o_u_id]    =   $val;
                $agents[]           =   $o_u_id;    
            }
            $userMoney  =   Db::table($order_table)->Alias('a')->field('sum(or_money) money,o_u_id')->join($order_record_table.' b', 'a.o_ordernum = b.or_o_ordernum and or_u_id = '.$uid, 'left')->where($where)->group('o_u_id')->select();
            if(!empty($userMoney)){
                foreach($userMoney as $val){
                    $o_u_id =   $val['o_u_id'];
                    $return[$o_u_id]['agentMoney']  =   $val['money'];  
                }
            }
            
            $userInfos  =   Db::table('user_tb')->field('u_leve,u_id,u_username')->where('u_id', 'in', $agents)->select();
            if(!empty($userInfos)){
                foreach($userInfos as $val){
                    $o_u_id =   $val['u_id'];
                    $return[$o_u_id]['u_leve']      =   $val['u_leve'];
                    $return[$o_u_id]['u_username']  =   $val['u_username'];
                }
            }
        }
        return $return;
    }

}

