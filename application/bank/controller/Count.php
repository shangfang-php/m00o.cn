<?php
namespace app\bank\controller;
use think\Db;
/**
 * Count
 * 数据统计功能
 * @package m00o.cn
 * @author Gary
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class Count extends common
{
    public function count_index()
    {
        $type       =   input('type') ? input('type') : 'today';
        $times      =   getStartAndEndTime($type);
        $startTime  =   $times['startTime'];
        $endTime    =   $times['endTime'];
        unset($times);
        
        $u_idss     =   session('taokeid');
        $cashWhere  =   array('m_u_idss'=>$u_idss, 'm_state'=>['in', '0,1']);
        $cashMoney  =   Db::table('money_tb')->field('sum(m_money) as money,m_state')->where($cashWhere)->group('m_state')->select(); ##统计待处理提现总金额
        $cashMoney  =   reverse_array($cashMoney, '', 'm_state');
        $waitMoney  =   isset($cashMoney['0']['money']) ? floatval($cashMoney['0']['money']) : 0; ##未完成提现总金额 
        $completeMoney  =   isset($cashMoney['1']['money']) ? floatval($cashMoney['1']['money']) : 0; ##已完成提现总金额
        
        if($endTime){
            $where['o_creattime']    =   ['between', array($startTime, $endTime)];
            $sqlWhere   =   "or_o_creattime between '{$startTime}' and '{$endTime}'";
        }else{
            $where['o_creattime']    =   ['>=', $startTime];
            $sqlWhere   =   "or_o_creattime >= '{$startTime}'";
        }
        
        $record_table   =   ortb($startTime);
        $order_table    =   tb($startTime);
        $user_table     =   'user_tb';
        $uids = $orderNums = $userInfo =array();
        
        ##下级代理总人数
        //$subQuery   =   Db::table($record_table)->field('or_id')->where($where)->group('or_u_id')->buildSql();
        //$sql        =   'select count(*) as totalNums from '.$subQuery.' a';
        $userNums   =   Db::table($user_table)->where(array('u_u_idss'=>$u_idss))->count();
        
        #####根据收入排行获取用户记录
        //$rankInfo   =   Db::name($record_table)->field('sum(or_money) totalMoney,or_u_id')->where($where)->group('or_u_id')->order('sum(or_money)', 'desc')->paginate(10, $userNums);
        $rankInfo   =   Db::table($user_table)->Alias('a')->field('u_id,u_username,u_nic,sum(or_money) as totalMoney')->join($record_table.' b', 'u_id = or_u_id and '.$sqlWhere, 'left')->where('u_u_idss', $u_idss)->group('u_id')->order('sum(or_money)', 'desc')->paginate(10, $userNums);
        foreach($rankInfo as $val){
            $uids[] =   $val['u_id'];
        }
        
        if($uids){
            ####获取用户订单总数
            $where['o_u_id']    =   ['in', $uids];
            $orderNums  =   Db::table($order_table)->field('count(o_id) orderNums, o_u_id')->where($where)->group('o_u_id')->select();
            $orderNums  =   empty($orderNums) ? array() : reverse_array($orderNums, 'orderNums', 'o_u_id');
            
            //##获取用户信息
//            $userInfo   =   Db::table('user_tb')->field('u_id, u_username, u_nic')->where('u_id', 'in', $uids)->select();
//            $userInfo   =   empty($userInfo) ? array() : reverse_array($userInfo, '', 'u_id');
        }
        
        $page = input('page') ? input('page') : 1;
        $data = array(
                    'waitMoney'     =>  $waitMoney,
                    'completeMoney' =>  $completeMoney,
                    'rankInfo'      =>  $rankInfo,
                    'orderNums'     =>  $orderNums,
                    //'userInfo'      =>  $userInfo,
                    'a'             =>  6,
                    'b'             =>  212,
                    'page'          =>  $page,
                    'type'          =>  $type,
                );
        $this->assign($data);
        return $this->fetch('count_index');
    }
    
    
    /**
     * Count::order_count()
     * 订单统计界面
     * @return void
     */
    function order_count(){
        $taokeId    =   session('taokeid');
        $order_table=   tb();
        $order_record_table=    ortb(time());
        //$field  =   'count(o_ordernum) as orderNums, sum(o_zzyj) as AllMoney, sum(or_money) as userMoney';
        $field  =   'o_ordernum,o_zzyj,sum(or_money) money, o_goodsimg, o_goodsinformation, o_goodsid, o_payprice, o_zzfc, o_zzyj';
        $where  =   array('o_u_idss'=>$taokeId, 'o_creattime'=>['>=', strtotime(date('Y-m-d'))], 'o_state'=>12);
        $res    =   Db::table($order_table)->join($order_record_table, 'o_ordernum=or_o_ordernum', 'left')->field($field)->where($where)->group('o_ordernum')->select();
        $orderNums  =   0;
        $allMoney   =   0;
        $or_money   =   0;
        
        $goodsInfo  =   array();
        
        ##获取前20商品
        if($res){
            foreach($res as $val){
                $orderNums += 1;
                $allMoney  += $val['o_zzyj'];
                $or_money  += $val['money'];
                
                $goodsId    =   $val['o_goodsid'];
                if(isset($goodsInfo[$goodsId])){
                    $goodsInfo[$goodsId]['o_zzyj'] += $val['o_zzyj'];
                    $goodsInfo[$goodsId]['nums']   += 1;
                }else{
                    $val['nums']    =   1;
                    $goodsInfo[$goodsId]    =   $val;
                }
            }
            
            $goodsOrders    =   get_field_array('nums', $goodsInfo);
            array_multisort($goodsOrders, SORT_DESC, $goodsInfo);
            
            $goodsInfo  =   array_slice($goodsInfo, 0,20);
        }
        
        $data   =   array('orderNums'=>$orderNums, 'allMoney'=>$allMoney, 'or_money'=>$or_money, 'goodsInfo'=>$goodsInfo, 'a'=>7, 'b'=>0);
        $this->assign($data);
        return $this->fetch();
    }
}
