<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use think\Cookie;
use think\Db;
include_once("SendTemplateSMS.php");
class Login extends Controller
{
    public function index()
    {

        if(Cookie::get('uname') && Cookie::get('usid') && Cookie::get('level')){
            Session::set('uname',Cookie::get('uname'));
            Session::set('usid',Cookie::get('usid'));
            Session::set('level',Cookie::get('level'));
            Session::set('expire',3600);
            $this->redirect('index/index');
        }
        return $this->fetch();
    }

    public function login()
    {

        $name = trim(input('post.name'));
        $pass = input('post.pass');
        if($name==''||$pass=='')
        {
            $arr = array("code"=>"-1","msg"=>"账号密码不能为空！");
            jsons($arr);
        }
        else
        {	
			$user = db('user_tb')->where('u_username',$name)->find();
            if($user)
            {

				$a = substr( md5($pass),12);
                $password = substr($a,0,-10);
				if($password == $user['u_pass'])
				{
                    if($user['u_state']==2)
                    {
                        $arr = array("code"=>"-1","msg"=>"账号被封禁！");
                        jsons($arr);
                    }
                    if($user['u_leve']==0)
                    {
                        $arr = array("code"=>"-1","msg"=>"管理员不能登陆");
                        jsons($arr);
                    }
                    Cookie::delete('usid');
                    Cookie::delete('uname');
                    Cookie::delete('level');
                    Cookie::set('usid',$user['u_id'],864000);
                    Cookie::set('uname',$name,864000);
                    Cookie::set('level',$user['u_leve'],864000);
                    Session::set('uname',$name);
                    Session::set('usid',$user['u_id']);
                    Session::set('usid',$user['u_leve']);
                    Session::set('expire',3600);
                    
					$arr = array("code"=>"0","msg"=>"登录成功");
					jsons($arr);
				}else{
					$arr = array("code"=>"-1","msg"=>"密码错误");
					jsons($arr);
				}
                
            }
            else
            {
                $arr = array("code"=>"-1","msg"=>"没有此账户");
                jsons($arr);
            }
        }
    }
	public function logout()
	{
		session('uname',null);
        session('usid',null);
        cookie('uname',null);
        cookie('usid',null);
		$this->redirect('login/index');
	}
    public function reg()
    {
        return $this->fetch();
    }

    public function reguser()
    {
        $phone      = trim(input('post.phone'));
        $core       = trim(input('post.core'));
        $mac        = trim(input('post.mac'));
        $pass       = trim(input('post.pass'));
       if( strlen($phone)<6)
       {
           $arr = array("code"=>"-1","res"=>"账号不能小于6个字符！");
           jsons($arr);
       }
        if($mac=='')
        {
            $arr = array("code"=>"-1","res"=>"请输入邀请码！");
            jsons($arr);
        }
        $user  = Db::name('user_tb')->where(array('u_username'=>$phone))->find();
        if($user)
        {
            $arr = array("code"=>"-1","res"=>"账号已被注册！");
            jsons($arr);
        }

        if(!captcha_check($core))
        {
            $arr = array("code"=>"-1","res"=>"验证码不正确！");
            jsons($arr);
        }
        else
        {

            $co     = getcode();
            $code   = iscode($co);
            $parent  = Db::name('user_tb')->where(array('u_code'=>$mac,'u_state'=>1))->find();
            if(!$parent)
            {
                $arr = array("code"=>"-1","res"=>"无效的邀请码！");
                jsons($arr);
            }
            if($parent['u_leve']!=0)
            {
                $fcbl = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>$parent['u_u_idss']))->find();
                if($fcbl['fc_power']!=1)
                {
                    $arr = array("code"=>"-1","res"=>"无效的邀请码！");
                    jsons($arr);
                }
            }
            else
            {
                $fcbl = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>$parent['u_id']))->find();

            }

            $a = substr( md5($pass),12);
            $password = substr($a,0,-10);
            $add['u_username']  = $phone;
            $add['u_pass']      = $password;
            $add['u_code']      = $code;
            $add['u_state']     = 1;
            $add['u_parent_u_id'] = $parent['u_id'];
            if($parent)
            {
                if($parent['u_leve']==0)
                {

                    $add['u_leve']      = 1;
                    $add['u_fcbl']      = $fcbl['fc_one'];
                    $add['u_fcbl2']     = $fcbl['fc_one2'];
                    $add['u_fcbl3']     = $fcbl['fc_one3'];
                    $add['u_u_idss']   = $parent['u_id'];


                }
                else if($parent['u_leve']==1)
                {

                    $add['u_leve']      = 2;
                    $add['u_fcbl']      = $fcbl['fc_two'];
                    $add['u_fcbl2']     = $fcbl['fc_two2'];
                    $add['u_fcbl3']     = $fcbl['fc_two3'];
                    $add['u_u_idss']   = $parent['u_u_idss'];
                }
                else if($parent['u_leve']==2)
                {
                    $add['u_leve']      = 3;
                    $add['u_fcbl']      = $fcbl['fc_three'];
                    $add['u_fcbl2']     = $fcbl['fc_three2'];
                    $add['u_fcbl3']     = $fcbl['fc_three3'];
                    $add['u_u_idss']   = $parent['u_u_idss'];
                }
                else if($parent['u_leve']==3)
                {
                    $arr = array("code"=>"-1","res"=>"无效的邀请码！");
                    jsons($arr);
                }
                $adduser = Db::name('user_tb')->insert($add);
                if($adduser)
                {
                    if($parent['u_leve']==0)
                    {
                        savesafe($parent['u_id']);
                    }else{
                        savesafe($parent['u_u_idss']);
                    }
                    $arr = array("code"=>"0","res"=>"注册成功");
                    jsons($arr);
                }
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"错误的邀请码！！");
                jsons($arr);
            }
        }
    }

    public function phonecore()
    {
        $phone      = trim(input('post.phone'));
        $phonestr   = preg_match("/^1[34578]\d{9}$/",$phone,$mobile);
        if($phonestr==0)
        {
            $arr = array("code"=>"-1","res"=>"请输入正确的手机号");
            jsons($arr);
        }
        else if($phonestr==1)
        {
            $us = Db::name('user_tb')->where(array('u_username'=>$phone))->find();
            if($us)
            {
                 $arr = array("code"=>"-1","res"=>"该手机号已注册！");
                    jsons($arr);
            }
            $rand = rand(100000,999999);    //生成6位数随机数
            $datas = array($rand,'10分钟');
            $tempId ="150547";
            $time = time();
            $veri = Db::name('verification_tb')->where(array('v_phone'=>$phone))->find();
            if(!$veri)
            {
                sendTemplateSMS($phone,$datas,$tempId);
                $data = array(
                    'v_phone'    => $phone,
                    'v_code'     => $rand,
                    'v_sendtime' => $time,
                );
                $addv = Db::name('verification_tb')->insert($data);
                if($addv)
                {
                    $arr = array("code"=>"0","res"=>"已发送验证码,请注意查收");
                    jsons($arr);
                }
            }
            else
            {
                $overtime = $time-$veri['v_sendtime'];
                if($overtime<60)
                {//如果时间小于一分钟
                    $arr = array("code"=>"-1","res"=>"操作频繁");
                    jsons($arr);
                }
                else if($overtime>60&&$overtime<600)
                {//如果时间大于一分钟小于十分钟
                    $num = $veri['v_code']; //取出随机数
                    $datas = array($num,'10分钟');
                    sendTemplateSMS($phone,$datas,$tempId);
                    $date['v_sendtime'] = $time;
                    $save = Db::name('verification_tb')->where("v_phone=".$phone)->update($date);
                    if($save)
                    {
                        $arr = array("code"=>"0","res"=>"已发送验证码,请注意查收");
                        jsons($arr);
                    }
                }else if($overtime>600){//如果时间大于十分钟
                    sendTemplateSMS($phone,$datas,$tempId);
                    $date = $time;
                    $da = array(
                        'v_code'     => $rand,
                        'v_sendtime' => $date,
                    );
                    $save = Db::name('verification_tb')->where("v_phone=".$phone)->update($da);
                    if($save)
                    {
                        $arr = array("code"=>"0","res"=>"已发送验证码,请注意查收");
                        jsons($arr);
                    }
                }
            }

        }
    }

}
