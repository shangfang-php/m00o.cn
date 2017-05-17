<?php
namespace app\super\controller;
use think\Db;
class Index extends common
{
    public function index()
    {
        $tk = Db::name('user_tb')->where(array('u_leve'=>0,'u_state'=>1))->count();
    	$wtk = Db::name('user_tb')->where(array('u_leve'=>0,'u_state'=>3))->count();

    	$time = time();
            $Y = date("Y",$time);
            $M = date("m",$time);

            if($M=="01")
            {
                $Ys = $Y-1;
                $Ms = 12;
            }
            else{
                $Ys = $Y;
                $Ms = $M-1;
                if($Ms<10)
                {
                    $Ms = '0'.$Ms;
                }
            }
            $or = "order_".$Y."_".$M."_tb";
            $or1 = "order_".$Ys."_".$Ms."_tb";
            $dydan = Db::name($or)->count();
            $sydan = Db::name($or1)->count();
            $date = date("Y-m-d",$time);
            $ritim = strtotime($date);
            $where['o_u_idss'] = Session('taokeid');
            $where['o_creattime'] = array('eq',$ritim);
            $ridan = Db::name($or)->where($where)->count();
    	$data = array(
            'tk'=>$tk,
            'wtk'=>$wtk,
    		'dydan'=>$dydan,
    		'ridan'=>$ridan,
    		'sydan'=>$sydan,
            'a'=>1,
            'b'=>1,
    		);
    	$this->assign($data);
        return $this->fetch();
    }
}
