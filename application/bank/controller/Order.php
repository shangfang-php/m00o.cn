<?php
namespace app\bank\controller;
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
            $where['o_u_idss'] = Session('taokeid');
            $dd = Db::name($or)->alias("or")->join('tgw_tb w','or.o_adid = w.t_mm3',"LEFT")->join('user_tb u','or.o_u_id = u.u_id',"LEFT")->where($where)->where("o_ordernum like '%".$name."%' or o_goodsinformation like '%".$name."%' or u_username like '%".$name."%'")->order('o_creattime desc')->paginate(10, false, [
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
            $where['o_u_idss'] = Session('taokeid');
            $dd = Db::name($or)->alias("or")->join('tgw_tb w','or.o_t_id = w.t_id',"LEFT")->join('user_tb u','or.o_u_id = u.u_id',"LEFT")->where("o_ordernum like '%".$name."%' or o_goodsinformation like '%".$name."%' or u_username like '%".$name."%'")->where($where)->order('o_creattime desc')->paginate(10, false, [
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
    
    /**
     * Order::update_order_status()
     * 更新订单状态
     * @return void
     */
    public function update_order_status(){
        $orderNumber    =   trim(input('post.orderNum'));
        $orderStatus    =   intval(input('post.orderNewStatus'));
        $creattime      =   intval(input('post.creattime'));
        if(!$orderNumber){
            $return =   array('code'=>'201', 'msg'=>'订单号不能为空');
            return jsons($return);
        }
        
        if(!in_array($orderStatus, array(3,13))){
            $return =   array('code'=>'202', 'msg'=>'状态非法');
            return jsons($return);
        }
        
        $orderTable =   tb($creattime);

        $taokeId    =   session('taokeid');
        $orderInfo  =   Db::table($orderTable)->where(array('o_ordernum'=>$orderNumber))->find();
        if(empty($orderInfo)){
            $return =   array('code'=>'203', 'msg'=>'找不到该订单信息');
            return jsons($return);
        }
        
        if($orderInfo['o_u_idss'] != $taokeId){
            $return =   array('code'=>'204', 'msg'=>'操作非法');
            return json($return);
        }
        $oldStatus  =   $orderInfo['o_state'];
        if($oldStatus != 12){
            $return =   array('code'=>'205', 'msg'=>'只允许更改付款中订单状态');
            return jsons($return);
        }
        
        $safe_info  =   Db::table('safe_tb')->where(array('s_u_id'=>$taokeId))->find();
        if(!$safe_info){
            $return =   array('code'=>'206', 'msg'=>'没有帐号同步信息，无法同步订单');
            return jsons($return);
        }
        
        $url    =   'http://db.00o.cn/db/Order.php?act=addorder';
        //$url    =   'http://localhost.db.com/Order.php?act=addorder';
        $orderInfo['o_state']   =   $orderStatus;
        $orderInfo['o_endtime'] =   time();
        $data   =   array('tkid'=>$taokeId, 'key'=>$safe_info['s_key'], 'data'=>urlencode(json_encode(array($orderInfo))));
        
        $return =   array('code'=>'207', 'msg'=>'更新订单失败');
        $res    =   curl($url, $data);
        if($res){
            $res    =   json_decode($res, TRUE);
            if(isset($res['code']) && $res['code'] == 1002){ ##更新成功
                $data   =   array('ordernum'=>$orderNumber, 'content'=>$orderNumber.':更新订单状态,原状态:'.$oldStatus.',新状态：'.$orderStatus, 'uid'=>$taokeId,'createTime'=>time());
                Db::table('order_operator_record')->insert($data);
                $return =   array('code'=>'200', 'msg'=>'更新成功');
            }
        }
        return jsons($return);
    }
}
