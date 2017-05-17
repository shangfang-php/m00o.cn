<?php
namespace app\bank\controller;
use think\Db;
class Index extends common
{
    public function index()
    {
    	$yiji = Db::name('user_tb')->where(array('u_u_idss'=>Session('taokeid'),'u_leve'=>1))->count();
    	$erji = Db::name('user_tb')->where(array('u_u_idss'=>Session('taokeid'),'u_leve'=>2))->count();
        $saji = Db::name('user_tb')->where(array('u_u_idss'=>Session('taokeid'),'u_leve'=>3))->count();
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
        $where['o_u_idss'] = Session('taokeid');
        $where['o_creattime'] = array('gt',$ritim);
        $ridan = Db::name($or)->where($where)->count();
    	$data = array(
    		'yiji'  =>$yiji,
    		'erji'  =>$erji,
            'saji'  =>$saji,
    		'fc'    =>$fc,
    		'dydan' =>$dydan,
    		'ridan' =>$ridan,
    		'sydan' =>$sydan,
            'a'=>1,
            'b'=>1,
    		);
    	$this->assign($data);
        return $this->fetch();
    }
}
