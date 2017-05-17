<?php
namespace app\super\controller;
use think\Db;
class Order extends common
{
    public function index($last)
    {
        if($last!="Lastmonth")
        {
            $name = input('get.name');
            $type = input('get.type');
            if($type!='')
            {
                if($type==1){$ot = 12;}else if($type==2){$ot = 3;}else if($type==3){$ot = 13;}else if($type==4){$ot = 14;}
                $where['o_state'] = $ot;
            }

            $time = time();
            $Y = date("Y",$time);
            $M = date("m",$time);
            $or = "order_".$Y."_".$M."_tb";

            $dd = Db::name($or)->alias("or")->join('tgw_tb w','or.o_t_id = w.t_id',"LEFT")->join('user_tb u','or.o_u_id = u.u_id',"LEFT")->where("o_ordernum like '%".$name."%' or o_goodsinformation like '%".$name."%'")->paginate(10, false, [
                'query' => array('name' => $name,'type'=>$type),
            ]);
            $data = array(
                "dd" => $dd,
                "yue"=>"by",
                'a'=>3,
                'b'=>4,
            );
            $this->assign($data);
            return $this->fetch();
        }
        else
        {
            $name = input('get.name');
            $type = input('get.type');
            if($type!='')
            {
                if($type==1){$ot = 12;}else if($type==2){$ot = 3;}else if($type==3){$ot = 13;}else if($type==4){$ot = 14;}
                $where['o_state'] = $ot;
            }
            $time = time();
            $Ys = date("Y",$time);
            $Ms = date("m",$time);
            if($Ms=="01")
            {
                $Y = $Ys-1;
                $M = 12;
            }
            else{
                $Y = $Ys;
                $M = $Ms-1;
                if($M<10)
                {
                    $M = '0'.$M;
                }
            }

            $or = "order_".$Y."_".$M."_tb";
            $dd = Db::name($or)->alias("or")->join('tgw_tb w','or.o_t_id = w.t_id',"LEFT")->join('user_tb u','or.o_u_id = u.u_id',"LEFT")->where("o_ordernum like '%".$name."%' or o_goodsinformation like '%".$name."%'")->paginate(10, false, [
                'query' => array('name' => $name,'type'=>$type),
            ]);
            $data = array(
                "dd" => $dd,
                "yue"=>"sy",
                'a'=>3,
                'b'=>4,
            );
            $this->assign($data);
            return $this->fetch();
        }
    }
}
