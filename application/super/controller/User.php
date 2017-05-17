<?php
namespace app\super\controller;
use think\Db;
use think\Session;
class User extends common
{
    public function index()
    {
        $name   = input('get.name');
        $type   = input('get.type');
        $typetime   = input('get.typetime');

        if($typetime==1)
        {
            $where['a_endtime'] = array('GT',time());
        }else if($typetime==2){
            $where['a_endtime'] = array('ELT',time());
        }

        if($type==1)
        {
            $where['fc_power'] = 1;
        }else if($type==2){
            $where['fc_power'] = 2;
        }

        $where['u_leve'] = 0;
        $where['u_state'] = 1;
//        $list = Db::name('order_2017_01_tb')
//            ->union('SELECT * FROM order_2016_12_tb where o_u_id = 3 limit 10,15')
//            ->where(array('o_u_id'=>3))->select();
//        dump($list);
//exit;
        $user = Db::name('user_tb')->join('authorize_tb w','user_tb.u_id = w.a_u_id',"LEFT")->join('tkfcbl_tb t','user_tb.u_id = t.fc_u_idss',"LEFT")->where( $where)->where("u_username like '%".$name."%' or u_nic like '%".$name."%'")->order("u_id desc")->paginate(20,false,[
            'query' => array('name'=>$name,'type'=>$type,'typetime'=>$typetime),
        ]);

        $data = array(
            'user'=>$user,
            'a'=>2,
            'b'=>2,
        );
        $this->assign($data);
        return $this->fetch();
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
            $us = Db::name("user_tb")->field('u_id as usid,u_username as username,u_code as code,u_leve as leve,u_fcbl as fcbl,u_parent_u_id,u_money as money,u_nic')->where(array('u_id'=>$uid))->find();

            $dl = Db::name('user_tb')->where(array('u_u_idss'=>$uid,'u_leve'=>2))->find();
            if($dl)
            {
                $isy = 1;
            }else{
                $isy = 2;
            }
            $tkf = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>$uid))->find();
            $power = $tkf['fc_power'];
            $at = Db::name('authorize_tb')->where(array('a_u_id'=>$uid))->find();
            $endtime = date("Y-m-d H:i:s",$at['a_endtime']);
            
            if($us)
            {
                $arr = array("code"=>"0","res"=>"获取成功！","user"=>$us,'endtime'=>$endtime,'power'=>$power,'isy'=>$isy);
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


    public function sq()
    {
        $uid  = trim(input('post.uid'));
        $nic  = trim(input('post.nic'));
        $sq   = trim(input('post.sq'));
        $state = trim(input('post.state'));
        $stime = trim(input('post.stime'));
        if(empty($uid))
        {
            $arr = array("code"=>"-1","res"=>"错误参数2006！");
            jsons($arr);
        }
        else
        {
            $user = Db::name('user_tb')->where(array('u_id'=>$uid))->find();
            if(!$user)
            {
                $arr = array("code"=>"-1","res"=>"错误参数2003！");
                jsons($arr);
            }

            if($stime==1)
            {
                $endtime = time()+31536000;
            }
            else if($stime==2)
            {
                $etime   = trim(input('post.etime'));
                $endtime = strtotime($etime);
            }


            $save['u_nic'] = $nic;
            $save['u_state'] = $state;
            $usave = Db::name('user_tb')->where(array('u_id'=>$uid))->update($save);

            
            if($usave)
            {
                $sqb['a_time'] = time();
                $sqb['a_u_id'] = $user['u_id'];
                $sqb['a_endtime'] = $endtime;
                $sqbs = Db::name('authorize_tb')->insert($sqb);
                $add['fc_power'] =$sq;
                $add['fc_u_idss'] = $user['u_id'];
                $adds = Db::name('tkfcbl_tb')->insert($add);
                if($sqbs){$a = '授权时间添加成功';}else{$a='授权时间添加失败！';}
                if($adds){$b = '分成比率设置添加成功';}else{$b='分成比率设置添加失败！';}

                $arr = array("code"=>"0","res"=>"用户授权成功\n$b\n$a");
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"1","res"=>"操作失败！");
                jsons($arr);
            }
        }
    }
    public function saveuser()
    {
        $uid  = trim(input('post.uid'));
        $nic = trim(input('post.nic'));
        $sq = trim(input('post.sq'));
        $stime = trim(input('post.stime'));
        $etime = trim(input('post.etime'));
        if(empty($uid))
        {
            $arr = array("code"=>"-1","res"=>"错误参数2006！");
            jsons($arr);
        }
        else
        {
            $user = Db::name('user_tb')->where(array('u_id'=>$uid))->find();
            if(!$user)
            {
                $arr = array("code"=>"-1","res"=>"错误参数2003！");
                jsons($arr);
            }
            // $dl = Db::name('user_tb')->where(array('u_u_idss'=>$uid,'u_leve'=>2))->find();
            // if($dl)
            // {
            //     $arr = array("code"=>"-1","res"=>"下面已发展二级代理后不可更改");
            //     jsons($arr);
            // }
            $fsave['fc_power'] = $sq;
            $ff = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>$uid))->update($fsave);
            $endtime = strtotime($etime);
            $asave['a_endtime'] = $endtime;
            $aa = Db::name('authorize_tb')->where(array('a_u_id'=>$uid))->update($asave);
            $save['u_nic'] = $nic;
            $usave = Db::name('user_tb')->where(array('u_id'=>$uid))->update($save);
            if($usave||$ff||$aa)
            {
                $arr = array("code"=>"0","res"=>"修改成功！");
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"1","res"=>"修改失败！");
                jsons($arr);
            }
        }
    }

    public function adduser()
    {
        $data = array(
            'a'=>2,
            'b'=>3,);
        $this->assign($data);
        return $this->fetch();
    }

    public function addus()
    {
        $phone      = trim(input('post.phone'));
        $nic        = trim(input('post.nic'));
        $phonestr   = preg_match("/^1[34578]\d{9}$/",$phone,$mobile);
        $pass       = trim(input('post.password'));
        $sq         = trim(input('post.sq'));
        $state      = trim(input('post.state'));
        $stime      = trim(input('post.stime'));
        

        if($phonestr==1)
        {
            $user  = Db::name('user_tb')->where(array('u_username'=>$phone))->find();
            if($user)
            {
                alert('账号已被注册!');exit;
            }
            if(strlen($pass)<6)
            {
                alert('密码需要大于6位数！');exit;
            }
            if($stime==1)
            {
                $endtime = time()+31536000;
            }
            else if($stime==2)
            {
                $etime   = trim(input('post.etime'));
                $endtime = strtotime($etime);
            }
            $a          = substr( md5($pass),12);
            $password   = substr($a,0,-10);
            $co         = getcode();
            $code       = iscode($co);
           $add['u_leve'] = 0;
           $add['u_username'] = $phone;
           $add['u_pass']   = $password;
           $add['u_code']   = $code;
           $add['u_nic']   = $nic;
           $add['u_state']  = $state;
           $addu = Db::name('user_tb')->insertGetId($add);
           if($addu)
            {
                $adds['fc_power'] =$sq;
                $adds['fc_u_idss'] = $addu;
                $addf = Db::name('tkfcbl_tb')->insert($adds);

                $sqb['a_time'] = time();
                $sqb['a_u_id'] = $addu;
                $sqb['a_endtime'] = $endtime;
                Db::name('authorize_tb')->insert($sqb);
            }
           if($addu>0)
           {
            alert('添加淘客成功！',url('user/index'));exit;
           }
           else
           {
            alert('未知错误！');exit;
           }
        }
        else
        {
            alert('手机号不正确！');exit;
        }
    }

    public function userno()
    {
        $name   = input('get.name');
//        $list = Db::name('order_2017_01_tb')
//            ->union('SELECT * FROM order_2016_12_tb where o_u_id = 3 limit 10,15')
//            ->where(array('o_u_id'=>3))->select();
//        dump($list);
//exit;
         $user = Db::name('user_tb')->join('authorize_tb w','user_tb.u_id = w.a_u_id',"LEFT")->join('tkfcbl_tb t','user_tb.u_id = t.fc_u_idss',"LEFT")->where(array('u_leve'=>0,'u_state'=>2))->where("u_username like '%".$name."%' or u_nic like '%".$name."%'")->paginate(10,false,[
            'query' => array('name'=>$name,),
        ]);

        $data = array(
            'user'=>$user,
            'a'=>2,
            'b'=>2,
        );
        $this->assign($data);
        return $this->fetch();
    }

    public function usersh()
    {
        $type       = input('get.type');
        $name   = input('get.name');
//        $list = Db::name('order_2017_01_tb')
//            ->union('SELECT * FROM order_2016_12_tb where o_u_id = 3 limit 10,15')
//            ->where(array('o_u_id'=>3))->select();
//        dump($list);
//exit;
        $user = Db::name('user_tb')->where("u_username like '%".$name."%' or u_nic like '%".$name."%'")->where(array('u_leve'=>0,'u_state'=>3))->order("u_id desc")->paginate(20,false,[
            'query' => array('type' => $type,'name'=>$name,),
        ]);

        $data = array(
            'user'=>$user,
            'a'=>2,
            'b'=>7,
        );
        $this->assign($data);
        return $this->fetch();
    }

    public function localid()
    {
        $tkid = trim(input('post.tkid'));
        if($tkid)
        {
            $user = Db::name('user_tb')->field('u_username,u_id,u_leve')->where(array('u_id'=>$tkid))->find();
            if($user)
            {
                Session::set('taokeid',$user['u_id']);
                Session::set('taokname',$user['u_username']);
                Session::set('expire',3600);
                 $arr = array("code"=>"0","msg"=>"成功");
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"0","msg"=>"失败");
                jsons($arr);
            
            }
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

    public function delsq()
    {
        $id   = trim(input('post.uid'));
        if(empty($id)){
            $arr = array("code"=>"-2","msg"=>"参数错误,请重试!");
            jsons($arr);
        }else{
            // 重置用户密码
            $user =  Db::name('user_tb')->where(array('u_id'=>$id,"u_state"=>3))->delete();

            if($user){
                $arr = array("code"=>"0","msg"=>"删除成功");
                jsons($arr);
            }else{
                $arr = array("code"=>"-1","msg"=>"删除失败,未知错误!");
                jsons($arr);
            }
        }
    }
}
