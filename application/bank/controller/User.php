<?php
namespace app\bank\controller;
use think\Db;
class User extends common
{
    public function index()
    {
        $name   = trim(input('get.name'));
        $type   =   intval(input('type')) ? intval(input('type')) : 1;
//        $list = Db::name('order_2017_01_tb')
//            ->union('SELECT * FROM order_2016_12_tb where o_u_id = 3 limit 10,15')
//            ->where(array('o_u_id'=>3))->select();
//        dump($list);
//exit;
        $wherestr   =   'u_u_idss = "'.Session('taokeid').'" ';
        $query      =   array('name'=>$name);
        
        if($name!='')
        {   
            $searchName =   str_replace('mm_', '', $name);
            if(preg_match("/^\d+_\d+_\d+$/", $searchName)){
                list($pid1, $pid2, $pid3) = explode('_',$searchName);
                $search_t_uid   =   Db::name('tgw_tb')->field('t_u_id')->where(array('t_mm1'=>$pid1, 't_mm2'=>$pid2, 't_mm3'=>$pid3))->find();
                $search_t_uid   =   empty($search_t_uid) ? '0' : $search_t_uid['t_u_id'];
                
                //$where['u_id']  =   $search_t_uid['t_u_id'];
                $wherestr   .=   'and u_id = "'.$search_t_uid.'" ';
            }else{
                $wherestr   .=   "and (u_username like '%".$name."%' or u_nic like '%".$name."%') ";
            }
            
            $so =   1;
        }else{
            $so =   2;
        }
        
        if($type == 2){
            $uids   =   Db::table('user_tb')->field('u_id,t_u_id')->join('tgw_tb', 'u_id=t_u_id', 'left')->where(array('u_u_idss'=>Session('taokeid')))->where('t_u_id', NULL)->group('u_id')->select();
            $uids   =   empty($uids) ? array(0) : get_field_array('u_id', $uids);
            $wherestr   .=  ' and u_id in ('.implode(',', $uids).')';
        }
        
        if($type == 3){
            $wherestr   .=  ' and u_state = 2';
        }
        
        $user = Db::name('user_tb')->where($wherestr)->order('u_state asc,u_id desc')->paginate(10,false,[
                'query' => $query,
            ]);
        $fcbl = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>Session('taokeid')))->find();
        
        $data = array(
                'user'=>$user,
                'fcbl'=>$fcbl,
                'so'=>$so,
                'a'=>2,
                'b'=>2,
            );
            $this->assign($data);
            return $this->fetch();

    }

    public function ustwo($uid)
    {
        if($uid){
            $u      = Db::name('user_tb')->field("u_id,u_username,u_parent_u_id,u_money,u_fcbl,u_code,u_nic,u_fcbl2,u_fcbl3,u_wqrmoney")->where(array('u_id'=>$uid))->find();
            $su     = Db::name('user_tb')->field("u_id,u_username,u_parent_u_id")->where(array('u_id'=>$u['u_parent_u_id']))->find();
            if($su){
                $parent_parent  = Db::name('user_tb')->field("u_id,u_username")->where(array('u_id'=>$su['u_parent_u_id']))->find();
            }else{
                $parent_parent  =   '';
            }
            
            $childUser  =   getUserChildAgents($uid, $u);
            $next_nums  =   isset($childUser[1]) ? Count($childUser[1]) : 0;
            $next_next  =   isset($childUser[2]) ? Count($childUser[2]) : 0;
            
            $tgw    = Db::name('tgw_tb')->where(array('t_u_idss'=>Session('taokeid'),"t_u_id"=>$u['u_id']))->select();
            $fcbl   = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>Session('taokeid')))->find();

            $data   = array(
                "u"     =>$u,
                "fcbl"  =>$fcbl,
                "su"    =>$su,
                "tgw"   =>$tgw,
                "uid"   =>$uid,
                'a'     =>2,
                'b'     =>0,
                'parent_parent'    =>  $parent_parent,
                'next_nums'=>$next_nums,
                'next_next'=>$next_next,
            );
            $this->assign($data);
            return $this->fetch();
        }

    }
    public function usu($uid, $type)
    {
        if($uid) {
            $type   =   intval($type);
            $username = input('get.username');
            
            $u      = Db::name('user_tb')->field("u_id,u_username,u_parent_u_id,u_money,u_fcbl,u_code,u_nic,u_fcbl2,u_fcbl3,u_wqrmoney")->where(array('u_id'=>$uid))->find();
            $su     = Db::name('user_tb')->field("u_id,u_username,u_parent_u_id")->where(array('u_id'=>$u['u_parent_u_id']))->find();
            if($su){
                $parent_parent  = Db::name('user_tb')->field("u_id,u_username")->where(array('u_id'=>$su['u_parent_u_id']))->find();
            }else{
                $parent_parent  =   '';
            }
            
            $childUser  =   getUserChildAgents($uid, $u);
            $next_nums  =   isset($childUser[1]) ? Count($childUser[1]) : 0;
            $next_next  =   isset($childUser[2]) ? Count($childUser[2]) : 0;
            
            $userIds    =   isset($childUser[$type]) ? $childUser[$type] : array(0);
            $user = Db::name('user_tb')->where(array('u_id' => ['in', $userIds]))->paginate(10, false, [
                'query' => array('type' => $type, 'username' => $username,),
            ]);
            $fcbl = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>Session('taokeid')))->find();
            $data = array(
                "u"     => $u,
                "fcbl"  => $fcbl,
                "su"    => $su,
                "user"  => $user,
                "uid"   => $uid,
                'a'=>2,
                'b'=>0,
                'parent_parent'    =>  $parent_parent,
                'next_nums'=>$next_nums,
                'next_next'=>$next_next,
                'type'  =>  $type,
            );
            $this->assign($data);
            return $this->fetch();
        }
    }
    public function usdd($uid,$last)
    {
        if($uid) {
            
            $name = input('get.name');
                
            $u      = Db::name('user_tb')->field("u_id,u_username,u_parent_u_id,u_money,u_fcbl,u_code,u_nic,u_fcbl2,u_fcbl3,u_wqrmoney")->where(array('u_id'=>$uid))->find();
            $su     = Db::name('user_tb')->field("u_id,u_username,u_parent_u_id")->where(array('u_id'=>$u['u_parent_u_id']))->find();
            if($su){
                $parent_parent  = Db::name('user_tb')->field("u_id,u_username")->where(array('u_id'=>$su['u_parent_u_id']))->find();
            }else{
                $parent_parent  =   '';
            }
            
            $childUser  =   getUserChildAgents($uid, $u);
            $next_nums  =   isset($childUser[1]) ? Count($childUser[1]) : 0;
            $next_next  =   isset($childUser[2]) ? Count($childUser[2]) : 0;
            
            $tgw = Db::name('tgw_tb')->where(array('t_u_id'=>$uid,'t_u_idss'=>Session('taokeid')))->select();
            $a = "";
            foreach($tgw as $k=>$v)
            {
                $a .= $v['t_id'].",";
            }
            
            if($last!="Lastmonth")
            {
                $yue    =   'by';
                $time = time();
                $Y = date("Y",$time);
                $M = date("m",$time);
                $or = "order_".$Y."_".$M."_tb";
                $where['o_t_id']= array('in',$a);
                $where['o_u_id']= $u['u_id'];
                $where['o_u_idss'] = Session('taokeid');
                $dd = Db::name($or)->alias("or")->join('tgw_tb w','or.o_t_id = w.t_id')->where($where)->where("o_ordernum like '%".$name."%' or o_goodsinformation like '%".$name."%'")->order('o_creattime desc')->paginate(10, false, [
                    'query' => array('name' => $name),
                ]);
                
            }
            else
            {
                $yue    =   'sy';
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

                $where['o_t_id']= array('in',$a);
                $where['o_u_idss'] = Session('taokeid');
                $dd = Db::name($or)->alias("or")->join('tgw_tb w','or.o_t_id = w.t_id')->where("o_ordernum like '%".$name."%' or o_goodsinformation like '%".$name."%'")->where($where)->order('o_creattime desc')->paginate(10, false, [
                    'query' => array('name' => $name),
                ]);
            }
            
            $fcbl = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>Session('taokeid')))->find();
            $data = array(
                "u" => $u,
                "fcbl" => $fcbl,
                "su" => $su,
                "uid" => $uid,
                "dd" => $dd,
                "yue"=> $yue,
                'a'=>2,
                'b'=>0,
                'parent_parent'    =>  $parent_parent,
                'next_nums'=>$next_nums,
                'next_next'=>$next_next,
            );
            $this->assign($data);
            return $this->fetch();
        }
    }

    public function addtgws()
    {
        $tnic = input('post.tnic');
        $tmac = input('post.tmac');
        $rem  = input('post.rem');
        $usname= input('post.usname');
        $prmac   = preg_match("/^mm_[0-9]{6,10}_[0-9]{6,10}+_[0-9]{6,10}+$/",$tmac,$mobile);
        if($prmac==0)
        {
            $arr = array("code"=>"-1","res"=>"PID格式不对！");
            jsons($arr);
        }
        else
        {
            $arr    = explode("_",$tmac);
            $tmm1   = $arr[1];$tmm2 = $arr[2];$tmm3 = $arr[3];
            $tm3    = Db::name("tgw_tb")->where(array('t_mm3'=>$tmm3))->find();
            $uname  = Db::name("user_tb")->where(array('u_username'=>$usname))->find();
            if($uname)
            {
                if($tm3)
                {
                    $arr = array("code"=>"-1","res"=>"已有的PID！");
                    jsons($arr);
                }
                else
                {
                    $add['t_nicname']   = $tnic;
                    $add['t_u_id']      = $uname['u_id'];
                    $add['t_mm1']       = $tmm1;
                    $add['t_mm2']       = $tmm2;
                    $add['t_mm3']       = $tmm3;
                    $add['t_remark']    = $rem;
                    $add['t_u_idss']    = Session('taokeid');
                    $addins = Db::name("tgw_tb")->insert($add);
                    if($addins)
                    {
                        savesafe(Session('taokeid'));
                        $arr = array("code"=>"0","res"=>"添加成功！");
                        jsons($arr);
                    }
                    else
                    {
                        $arr = array("code"=>"-1","res"=>"添加失败！");
                        jsons($arr);
                    }
                }
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"错误操作！");
                jsons($arr);
            }

        }
    }

    public function mt()
    {
        $tid = trim(input('post.tid'));
        if(empty($tid))
        {
            $arr = array("code"=>"-1","res"=>"参数错误！");
            jsons($arr);
        }
        else
        {
            $tgw = Db::name('tgw_tb')->where(array('t_id'=>$tid))->find();
            if($tgw)
            {
                $arr = array("code"=>"0","res"=>"获取成功！","t"=>$tgw);
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"获取失败！");
                jsons($arr);
            }
        }
    }

    public function deltgw()
    {
        $tid = trim(input('post.tid'));
        if(empty($tid))
        {
            $arr = array("code"=>"-1","res"=>"参数错误！");
            jsons($arr);
        }
        else
        {
            $tgw = Db::name('tgw_tb')->where(array('t_id'=>$tid))->delete();
            if($tgw)
            {
                savesafe(Session('taokeid'));
                $arr = array("code"=>"0","res"=>"删除成功！");
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"删除失败！");
                jsons($arr);
            }
        }
    }

    public function modifytgw()
    {
        $tnic = input('post.tnic');
        $rem  = input('post.rem');
        $tid  = input('post.tid');
        $uname = Db::name("tgw_tb")->where(array('t_id'=>$tid))->find();
        if($uname)
        {
            $add['t_nicname']   = $tnic;
            $add['t_remark']    = $rem;
            $add['t_u_idss']    = Session('taokeid');
            $addins = Db::name("tgw_tb")->where(array('t_id'=>$tid))->update($add);
            if($addins)
            {
                savesafe(Session('taokeid'));
                $arr = array("code"=>"0","res"=>"修改成功！");
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"未做任何修改！");
                jsons($arr);
            }
        }
        else
        {
            $arr = array("code"=>"-1","res"=>"错误操作！");
            jsons($arr);
        }
    }
    public function Reset(){
        $id   = trim(input('post.uid'));
        if(empty($id)){
            $arr = array("code"=>"-2","msg"=>"参数错误,请重试!");
            jsons($arr);
        }else{
            // 重置用户密码
            $user =  Db::name('user_tb')->where(array('u_id'=>$id))->find();
            $r = rand(100000,999999);
            $a          = substr( md5($r),12);
            $password   = substr($a,0,-10);
            $data['u_pass'] = $password;
            $count = Db::name('user_tb')->where(array('u_id'=>$id))->update($data);
            if($count){
                $arr = array("code"=>"0","msg"=>"用户:".$user['u_username']."\n密码重置成功,新密码: ".$r);
                jsons($arr);
            }else{
                $arr = array("code"=>"-1","msg"=>"密码重置失败,未知错误!");
                jsons($arr);
            }
        }
    }
    public function usones()
    {
        $uid = trim(input('post.uid'));
        if(empty($uid))
        {
            $arr = array("code"=>"-1","res"=>"参数错误！");
            jsons($arr);
        }
        else
        {
            $us = Db::name("user_tb")->field('u_id as usid,u_username as username,u_code as code,u_leve as leve,u_fcbl as fcbl,u_money as money,u_state as state,u_nic,u_fcbl2,u_fcbl3,u_wqrmoney')->where(array('u_parent_u_id'=>$uid))->select();
            if($us)
            {
                $arr = array("code"=>"0","res"=>"获取成功！","user1"=>$us);
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"获取失败！");
                jsons($arr);
            }
        }
    }
    public function ustwos()
    {
        $uid = trim(input('post.uid'));
        if(empty($uid))
        {
            $arr = array("code"=>"-1","res"=>"参数错误！");
            jsons($arr);
        }
        else
        {
            $us = Db::name("user_tb")->field('u_id as usid,u_username as username,u_code as code,u_leve as leve,u_fcbl as fcbl,u_parent_u_id,u_money as money,u_state as state,u_nic,u_fcbl2,u_fcbl3,u_wqrmoney')->where(array('u_parent_u_id'=>$uid))->select();
            if($us)
            {
                $arr = array("code"=>"0","res"=>"获取成功！","user2"=>$us);
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"获取失败！");
                jsons($arr);
            }
        }
    }

    public function jis()
    {
        $id = trim(input('post.id'));
        if(empty($id))
        {
            $arr = array("code"=>"-1","res"=>"参数错误！");
            jsons($arr);
        }
        else
        {
            $us = Db::name("user_tb")->field('u_id as usid,u_username as username,u_code as code,u_leve as leve,u_fcbl as fcbl,u_parent_u_id as pid,u_money as money,u_nic,u_fcbl2,u_fcbl3,u_wqrmoney')->where(array('u_leve'=>$id,'u_u_idss'=>Session('taokeid')))->select();
            if($us)
            {
                $arr = array("code"=>"0","res"=>"获取成功！","us"=>$us);
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"获取失败！");
                jsons($arr);
            }
        }
    }

    public function modifysuer()
    {
        $uid = trim(input('post.uid'));
        if(empty($uid))
        {
            $arr = array("code"=>"-1","res"=>"参数错误！");
            jsons($arr);
        }
        else
        {
            $us = Db::name("user_tb")->field('u_id as usid,u_username as username,u_code as code,u_leve as leve,u_fcbl as fcbl,u_parent_u_id,u_money as money,u_nic,u_fcbl2,u_fcbl3,u_wqrmoney,u_allmoney')->where(array('u_id'=>$uid))->find();
            //$one = Db::name("user_tb")->where(array('u_parent_u_id'=>$uid))->find();
            //$nou    =   $one ? 1 : 0; ##是否有下级代理
            //if($one)
//            {
//                $nou = 1;
//            }
//            else
//            {
//                $nou = 0;
//            }
            //$nou    =   $us['leve'] == 1 ? 1 : 0;
            $nou    =   0;
            if($us)
            {   
                $parentInfo =   Db::table('user_tb')->field('u_username')->where(array('u_id'=>$us['u_parent_u_id']))->find();
                $us['u_parent_username']    =   $parentInfo ? $parentInfo['u_username'] : ''; 
                $arr = array("code"=>"0","res"=>"获取成功！","user"=>$us,"nou"=>$nou);
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"获取失败！");
                jsons($arr);
            }
        }
    }
    public function fjyh()
    {
        $uid = trim(input('post.uid'));
        if(empty($uid))
        {
            $arr = array("code"=>"-1","res"=>"错误参数！");
            jsons($arr);
        }
        else{
            $user = Db::name('user_tb')->where(array('u_id'=>$uid))->find();
            if(!$user)
            {
                $arr = array("code"=>"-1","res"=>"错误参数2003！");
                jsons($arr);
            }

            if($user['u_state'] == 1)
            {
                $save['u_state'] = 2;
                $saveu = Db::name('user_tb')->where(array('u_id'=>$uid))->update($save);
                if($saveu)
                {
                    $arr = array("code"=>"0","res"=>"更变成功");
                    jsons($arr);
                }
                else
                {
                    $arr = array("code"=>"-1","res"=>"更变失败！");
                    jsons($arr);
                }
            }
            else if($user['u_state'] == 2)
            {
                $save['u_state'] = 1;
                $saveu = Db::name('user_tb')->where(array('u_id'=>$uid))->update($save);
                if($saveu)
                {
                    $arr = array("code"=>"0","res"=>"更变成功");
                    jsons($arr);
                }
                else
                {
                    $arr = array("code"=>"-1","res"=>"更变失败！");
                    jsons($arr);
                }
            }
        }
    }

    public function saveuser()
    {
        $uid  = trim(input('post.uid'));
        $fcbl = trim(input('post.fcbl'))/100;
        $fcbl2 = trim(input('post.fcbl2'))/100;
        $fcbl3 = trim(input('post.fcbl3'))/100;
        $nic = trim(input('post.nic'));
        //$jis  = intval(trim(input('post.jis')));
        $se5  = intval(trim(input('post.se5')));
        $taokeId    =   session('taokeid');
        if($fcbl>1){
            alert('本人佣金比例不能大于100');exit;
        }
        if($fcbl2>1){
            alert('二级佣金比例不能大于100');exit;
        }
        if($fcbl3>1){
            alert('三级佣金比例不能大于100');exit;
        }
        
        if(empty($uid))
        {
            $arr = array("code"=>"301","res"=>"操作非法！");
            jsons($arr);
        }
        else
        {
            $user = Db::name('user_tb')->where(array('u_id'=>$uid))->find();
            if(!$user){
                $arr = array("code"=>"302","res"=>"找不到该用户信息!");
                jsons($arr);
            }
            
            if($taokeId != $user['u_u_idss']){
                $arr = array('code'=>'303', 'res'=>'非法操作!该用户你无权操作!');
                jsons($arr);
            }
            
            if($uid == $se5){
                $arr = array("code"=>"304","res"=>"不可以选择自己为上级！");
                jsons($arr);
            }
            
            /*
            if($jis==1||$jis==2){
                if(!$se5){
                    $arr = array('code'=>'305', 'res'=>'必须要有上级代理!');
                    jsons($arr);
                }
                $usse = Db::name('user_tb')->where(array('u_id'=>$se5))->find();
                if(!$usse){
                    $arr = array("code"=>"306","res"=>"上级代理不存在");
                    jsons($arr);
                }
            }
            
            ##为什么$jis 不按照数据库的合伙人等级赋值而是小1。。。。
            $jis += 1;
            
            Db::startTrans();
            if($jis != $user['u_leve'] && $jis < $user['u_leve']){ ##级别上升
                 //$save['u_leve']    =   $jis;
                 //$save['u_parent_u_id']= $jis == 1 ? $taokeId : $se5; ##一级代理为淘客ID，非一级代理则为指定代理商
                 //$fcblArr   =   getTaokeFcblSet($taokeId, $jis); ##获取指定淘客的分成比例默认设置
                 //$save      +=  $fcblArr; ##合并数组
                 
                 ##检测是否有下级合伙人，当前只有二级合伙人升一级才需要升级二级名下的三级合伙人到二级
                 $childUser =   Db::table('user_tb')->field('u_id')->where('u_parent_u_id', $uid)->find();
                 if($childUser){
                    $fcblArr    =   getTaokeFcblSet($taokeId, 2); ##获取二级合伙人默认分成比例
                    $fcblArr['u_leve'] =   2;
                    //var_dump($fcblArr);exit;
                    $info       =   Db::table('user_tb')->where('u_parent_u_id', $uid)->update($fcblArr);
                    if($info === FALSE){
                        $arr = array("code"=>"307","res"=>"提升下级合伙人等级失败!");
                        Db::rollback();
                        jsons($arr);
                    }
                 }
                 
            }//else{ ##级别未改变 则只更新合伙人信息
            */
                $save['u_fcbl']     =   $fcbl;
                $save['u_fcbl2']    =   $fcbl2;
                $save['u_fcbl3']    =   $fcbl3;
                $save['u_parent_u_id']= $se5;
                //$save['u_leve']    =   $jis;
            //}
            
            /** 更新合伙人信息**/
            $save['u_nic']      =   $nic;
            $usave = Db::name('user_tb')->where(array('u_id'=>$uid))->update($save);
            if($usave === false){
                Db::rollback();
                $arr = array("code"=>"333","res"=>"保存合伙人信息失败!");
                jsons($arr);
            }
            /** end**/
            
            Db::commit();
            savesafe(Session('taokeid'));
            
            //if($jis != $user['u_leve'] && $jis < $user['u_leve']){
//                if($jis == 1){
//                    $name   =   '一级合伙人';
//                }else{
//                    $name   =   '二级合伙人';
//                }
//                $res    =   $user['u_username'].'已经提升为'.$name.',';
//                if($childUser){
//                    $res    .=  "\n他的下级合伙人也跟着提升了，";
//                }
//                $res    .=  "\n分成比例已经修改为默认，如需修改请重新调整！";
//            }else{
                $res    =   '修改成功！';
            //}
            $arr = array("code"=>"0","res"=>$res);
            jsons($arr);
            
            
            /*
            $one = Db::name("user_tb")->where(array('u_parent_u_id'=>$uid))->find();
            if(!$one)
            {

                if($uid == $se5)
                {
                    $arr = array("code"=>"-1","res"=>"不可以选择自己为上级！");
                    jsons($arr);
                }
                if($jis==1||$jis==2)
                {
                    $usse = Db::name('user_tb')->where(array('u_id'=>$se5))->find();
                    if(!$usse)
                    {
                        $arr = array("code"=>"-1","res"=>"错误参数2004！");
                        jsons($arr);
                    }
                    if($se5=='')
                    {
                        $arr = array("code"=>"-1","res"=>"错误参数2005！");
                        jsons($arr);
                    }
                }
                if($jis==0)
                {
                    $save['u_parent_u_id'] = Session('taokeid');
                    $save['u_leve'] = 1;
                }
                else if($jis==1)
                {
                    $save['u_leve'] = 2;
                    $save['u_parent_u_id'] = $se5;
                }
                else if($jis==2)
                {
                    $save['u_leve'] = 3;
                    $save['u_parent_u_id'] = $se5;
                }
            }
            $save['u_fcbl'] = $fcbl;
            $save['u_fcbl2'] = $fcbl2;
            $save['u_fcbl3'] = $fcbl3;
            $save['u_nic'] = $nic;
            $usave = Db::name('user_tb')->where(array('u_id'=>$uid))->update($save);
            if($usave)
            {
                savesafe(Session('taokeid'));
                $arr = array("code"=>"0","res"=>"修改成功！");
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"1","res"=>"修改失败！");
                jsons($arr);
            }*/
        }
    }

    public function adduser()
    {
        $fcbl = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>Session('taokeid')))->find();
        $data = array(
            'fcbl'=>$fcbl,
            'a'=>2,
            'b'=>3,);
        $this->assign($data);
        return $this->fetch();
    }

    public function addu()
    {
        $phone      = trim(input('post.phone'));
        $pass       = trim(input('post.password'));
        $fcbl       = trim(input('post.fcbl'))/100;
        $fcbl2      = trim(input('post.fcbl2'))/100;
        $fcbl3      = trim(input('post.fcbl3'))/100;
        $nic        = trim(input('post.nic'));
        //$dl         = trim(input('post.dl'));
        $sjdl       = intval(trim(input('post.sjdl')));
        
        if(strlen($phone)<6){
            alert('用户账号需要大于6位字符');exit;
        }
        if(strlen($phone)>20)
        {
            alert('用户账号不能大于20位字符');exit;
        }

        $user  = Db::name('user_tb')->where(array('u_username'=>$phone))->find();
        if($user)
        {
            alert('账号已被注册!');exit;
        }
        if(strlen($pass)<6)
        {
            alert('密码需要大于6位数！');exit;
        }
        
        $add['u_parent_u_id']   =   $sjdl;
            /*
            if($dl!=0)
            {
                if($sjdl>0)
                {
                    if($dl==1){
                        $add['u_leve'] = 2;
                    }else if($dl==2)
                    {
                        $add['u_leve'] = 3;
                    }else{
                        alert('非法操作！');exit;
                    }

                   $add['u_parent_u_id'] = $sjdl;
                }else{
                    alert('您需要有上级代理才能添加！');exit;
                }
            }
            else
            {
                $add['u_parent_u_id'] = Session('taokeid');
                $add['u_leve'] = 1;
            }*/
            $a          = substr( md5($pass),12);
            $password   = substr($a,0,-10);
            $co         = getcode();
            $code       = iscode($co);
            if($fcbl>1){
                alert('本人佣金比例不能大于100');exit;
            }
            if($fcbl2>1){
                alert('二级佣金比例不能大于100');exit;
            }
            if($fcbl3>1){
                alert('三级佣金比例不能大于100');exit;
            }
            
           $add['u_username'] = $phone;
           $add['u_pass']   = $password;
           $add['u_code']   = $code;
           $add['u_nic']    = $nic;
           $add['u_state']  = 1;
           $add['u_fcbl']   = $fcbl;
           $add['u_fcbl2']   = $fcbl2;
           $add['u_fcbl3']   = $fcbl3;
           $add['u_u_idss'] = Session('taokeid');
           $addu = Db::name('user_tb')->insert($add);
           if($addu>0)
           {
            savesafe(Session('taokeid'));
            alert('添加用户成功！',url('user/index'));exit;
           }
           else
           {
            alert('未知错误！');exit;
           }

    }

    /**
     * User::agent_login()
     * 一键跳转到代理商界面
     * @return void
     */
    function agent_login(){
        $code       =   200;
        $codeMsg    =   '成功';
        $agentId    =   input('post.agentId');
        $taokeId    =   session('taokeid');
        $agentInfo  =   getUserInfo($agentId);
        if($taokeId == $agentInfo['u_u_idss']){
            cookie(['expire'=>864000]);
            cookie('usid', $agentInfo['u_id']);
            cookie('uname', $agentInfo['u_username']);
            cookie('u_u_idss', $agentInfo['u_u_idss']);
            cookie('u_pass', $agentInfo['u_pass']);
            
            session('uname', $agentInfo['u_username']);
            session('usid', $agentInfo['u_id']);
            session('u_u_idss', $agentInfo['u_u_idss']);
            session('u_pass', $agentInfo['u_pass']);
        }else{
            $code   =   301;
            $codeMsg=   '非法操作！';
        }
        return json(array('code'=>$code, 'codeMsg'=>$codeMsg));
    }
    
    /**
     * User::countWqrMoney()
     * 统计指定合伙人的未确认金额
     * @return void
     */
    function countWqrMoney(){
        $uid    =   intval(Input('post.uid'));
        if(!$uid){
            $arr = array('code'=>601, 'res'=>'非法操作!');
            return json($arr);
        }
        $userInfo   =   getUserInfo($uid);
        if(!$userInfo){
            $arr = array('code'=>602, 'res'=>'没有该合伙人信息!');
            return json($arr);
        }
        
        if($userInfo['u_u_idss'] != session('taokeid')){
            $arr = array('code'=>603, 'res'=>'操作非法!');
            return json($arr);
        }
        
        $totalMoney =   $this->getUserWqrMoney($uid, $userInfo); ##获取计算得到的未确认金额
        
        /** 统计结束**/
        $data   =   array('userInfo'=>$userInfo, 'realWqrMoney'=>$totalMoney,'code'=>200, 'res'=>'返回数据');
        return json($data);
    }
    
    /**
     * User::getUserWqrMoney()
     * 获取合伙人未确认金额
     * @param mixed $uid
     * @param string $userInfo
     * @return
     */
    function getUserWqrMoney($uid, $userInfo=''){
        $agentIds   =   array();
        $res    =   getUserChildAgents($uid, $userInfo); ##获取下级合伙人集合
        if($res){
            foreach($res as $val){
                $agentIds = array_merge($agentIds, $val);
            }
        }
        $agentIds[] =   $uid;
        
        $time   =   time();
        $a      =   true;
        $where  =   array('o_u_id'=>['in', $agentIds], 'o_state'=>12);
        $totalMoney =   0;
        /** 循环统计每月付款中订单分配的未确认金额**/
        while($a){
            $order_table    =   tb($time);
            $order_record   =   ortb($time);
            
            $money  =   Db($order_table)->join($order_record, 'o_ordernum = or_o_ordernum and or_u_id = '.$uid, 'left')->where($where)->sum('or_money');
            $totalMoney += $money;
            
            $monthFirsthDay =   date('Y-m-01', $time);
            $time           =   strtotime('-1 month', strtotime($monthFirsthDay));
            
            if($time < strtotime('2017-01-01')){
                $a  =   FALSE;
            }
        }
        return $totalMoney;
    }
    
    /**
     * User::saveWqrMoney()
     * 修改未确认金额
     * @return void
     */
    function save_wqrMoney(){
        $uid    =   intval(trim(Input('post.uid')));
        if(!$uid){
            $arr = array('code'=>701, 'res'=>'非法操作!');
            return json($arr);
        }
        $userInfo   =   getUserInfo($uid);
        if(!$userInfo){
            $arr = array('code'=>702, 'res'=>'没有该合伙人信息!');
            return json($arr);
        }
        
        if($userInfo['u_u_idss'] != session('taokeid')){
            $arr = array('code'=>703, 'res'=>'操作非法!');
            return json($arr);
        }
        
        $totalMoney =   $this->getUserWqrMoney($uid, $userInfo); ##获取计算得到的未确认金额
        $info       =   Db::table('user_tb')->where('u_id', $uid)->update(array('u_wqrmoney'=>$totalMoney));
        if($info === false){
            $arr = array('code'=>704, 'res'=>'更新失败!');
        }else{
            $arr = array('code'=>200, 'res'=>'更新成功!');
        }
        return json($arr);
    }
    
    /**
     * User::get_user_list()
     * 获取用户列表
     * @return void
     */
    function get_user_list(){
        $search =   trim(input('post.name'));
        if(!$search){
            $arr = array('code'=>801, 'list'=>'');
            return json($arr);
        }
        $taokeId    =   session('taokeid');
        $where  =   array('u_u_idss'=>$taokeId, 'u_username'=>['like', '%'.$search.'%']);
        $list   =   Db::table('user_tb')->field('u_id,u_username')->where($where)->select();
        if(!$list){
            $arr = array('code'=>802, 'list'=>'');
        }else{
            $arr = array('code'=>200, 'list'=>$list);
        }
        return json($arr);
    }
    
}
