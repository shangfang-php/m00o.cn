<?php
namespace app\bank\controller;
use think\Db;
class Index extends common
{
    public function index()
    {
        $where= array('u_u_idss'=>Session('taokeid'));
    	$yiji = Db::name('user_tb')->where($where)->count();
    	$forbid_users  =   Db::name('user_tb')->where(array('u_u_idss'=>Session('taokeid'),'u_state'=>2))->count(); ##����û�
        
        $tgw_sql       =   Db::table('user_tb')->join('tgw_tb', 'u_id=t_u_id', 'inner')->field('t_u_id')->where($where)->group('u_id')->select();
        $tgw_user      =   count($tgw_sql);
        $no_tgw         =   $yiji - $tgw_user; ##δ�����ƹ�Ϊ�ϻ���
//        $saji = Db::name('user_tb')->where(array('u_u_idss'=>Session('taokeid'),'u_leve'=>3))->count();
    	$fc   = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>Session('taokeid')))->find();
    	$time = time();
        $Y = date("Y",$time);
        $M = date("m",$time);
        if($M=="01"){
            $Ys = $Y-1;
            $Ms = 12;
        }else{
            $Ys = $Y;
            $Ms = $M-1;
            if($Ms<10){
                $Ms = '0'.$Ms;
            }
        }
        $or     = "order_".$Y."_".$M."_tb";
        $or1    = "order_".$Ys."_".$Ms."_tb";
        $dydan  = Db::name($or)->where(array('o_u_idss'=>Session('taokeid')))->count();
        $sydan  = Db::name($or1)->where(array('o_u_idss'=>Session('taokeid')))->count();
        $date   = date("Y-m-d",$time);
        $ritim  = strtotime($date);
        
        $where  =   ['o_u_idss'=>Session('taokeid'), 'o_creattime'=>array('gt',$ritim)];
        $ridan = Db::name($or)->where($where)->count();
    	$data = array(
    		'yiji'  =>$yiji,
    		//'erji'  =>$erji,
            //'saji'  =>$saji,
    		'fc'    =>$fc,
    		'dydan' =>$dydan,
    		'ridan' =>$ridan,
    		'sydan' =>$sydan,
            'forbid_users'=>$forbid_users,
            'no_tgw'   =>  $no_tgw,
            'a'=>1,
            'b'=>1,
    		);
    	$this->assign($data);
        return $this->fetch();
    }
}
