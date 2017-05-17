<?php
namespace app\super\controller;
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

        $a = substr( md5($pass),0,4);
        $b = substr( md5($pass),4,4);//
        $c = substr( md5($pass),8,4);
        $d = substr( md5($pass),12,4);//
        $e = substr( md5($pass),16,4);
        $f = substr( md5($pass),20,4);
        $g = substr( md5($pass),24,4);
        $h = substr( md5($pass),28,4);//
        $password = $g.$a.$e.$f.$c;
        $u = Db::name('super')->where(array('supername'=>$name,'superpas'=>$password,'state'=>1))->find();
        if($u)
        {
            Session::set('supersid',$u['id']);
            Session::set('superspic',$u['supername']);
            Session::set('super',$u['power']);
            Session::set('expire',3600);
            $this->redirect('Index/index');exit;
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
}
