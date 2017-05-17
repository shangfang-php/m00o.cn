<?php
namespace app\bank\controller;
use think\Controller;
use think\Session;
use think\Db;
class Login extends Controller
{
    public function index()
    {
        header("Content-type:text/html;charset=utf-8");

        return $this->fetch();
    }

    public function login()
    {
        header("Content-type:text/html;charset=utf-8");
        $name = trim(input('post.name'));
        $pass = input('post.pass');
        if($name=="")
        {
            alert('用户名不能为空！');exit;
        }
        if($pass=='')
        {
            alert('密码不能为空！');exit;
        }
        $a = substr( md5($pass),12);
        $password = substr($a,0,-10);
        $u = Db::name('user_tb')->where(array('u_username'=>$name,'u_pass'=>$password,'u_leve'=>0))->find();
        if($u)
        {
            if($u['u_state']==2)
            {
                alert('抱歉，您的帐号已被封禁，请联系管理员');
            }
            else if($u['u_state']==3)
            {
                alert('未授权账号，请联系管理员购买开通!');
            }
            else if($u['u_state']==1)
            {
                Session::set('taokeid',$u['u_id']);
                Session::set('taokname',$name);
                $this->redirect('Index/index');exit;
            }
        }
        else
        {
            alert('帐号或者密码错误！');exit;
        }
    }

    public function signup()
    {
         header("Content-type:text/html;charset=utf-8");
        return $this->fetch();
    }

    public function reguser()
    {
         header("Content-type:text/html;charset=utf-8");
        $phone      = trim(input('post.phone'));
        $core       = trim(input('post.core'));
        $pass       = trim(input('post.pass'));
        $pass1      = trim(input('post.pass1'));
        if($pass!=$pass1)
        {
            $arr = array("code"=>"-1","res"=>"两次密码输入不相同！");
            jsons($arr);
        }
        $user  = Db::name('user_tb')->where(array('u_username'=>$phone))->find();
        $vcore = Db::name('verification_tb')->where(array('v_phone'=>$phone))->find();
        if($user)
        {
            $arr = array("code"=>"-1","res"=>"手机号已注册！");
            jsons($arr);
        }
        if($vcore['v_code']!=$core)
        {
            $arr = array("code"=>"-1","res"=>"验证码不正确！");
            jsons($arr);
        }
        else
        {
            $overtime = time()-$vcore['v_sendtime'];
            if($overtime>600)
            {
                $arr = array("code"=>"-1","res"=>"验证码已过期,请重新获取");
                jsons($arr);
            }
            else
            {
                $co     = getcode();
                $code   = iscode($co);
                $a = substr( md5($pass),12);
                $password = substr($a,0,-10);
                $add['u_username']  = $phone;
                $add['u_pass']      = $password;
                $add['u_code']      = $code;
                $add['u_state']     = 3;
                $add['u_leve']      = 0;

                $adduser = Db::name('user_tb')->insert($add);
                if($adduser)
                {
                    $arr = array("code"=>"0","res"=>"注册成功");
                    jsons($arr);
                }
                else{
                    $arr = array("code"=>"-1","res"=>"注册失败");
                    jsons($arr);
                }
            }
        }
    }

    public function soso()
    {
        $name = trim(input('post.name'));
        if($name!='')
        {
            $user = Db::name('user_tb')->field('u_username,u_state,u_leve')->where(array('u_username'=>$name))->find();
            if($user)
            {
                $this->assign('user',$user);
                return $this->fetch();exit;
            }else
            {
                $this->assign('user',999);
                return $this->fetch();exit;
            }

        }
        $this->assign('user','');
        return $this->fetch();
    }

    public function wxche()
    {


        return $this->fetch();

    }

    public function jsre()
    {

        $timestamp = time();
        $wxnonceStr = rand(100000, 999999);
        $wxticket = wx_get_jsapi_ticket();
        $wxOri = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s",
            $wxticket, $wxnonceStr, $timestamp,
            'http://m.00o.cn/bank/login/wxche'
        );
        $wxSha1 = sha1($wxOri);
        $arr = array('appid'=>'wx54902e474d11fce8','timestamp'=>$timestamp,'wxnonceStr'=>$wxnonceStr,'wxticket'=>$wxticket,'wxSha1'=>$wxSha1);
        jsons($arr);
    }
}
