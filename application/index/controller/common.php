<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use think\Cookie;
use think\Db;
class Common extends Controller
{
    public function _initialize()
    {   
        header("Content-type: text/html; charset=utf-8");
        //session('usid', 1626);
//        session('uname', '18092354891');
        
        if(Session('uname')!=""||Session('usid')!="")
        {
            $sessionuser = Db::name('user_tb')->where(array('u_username'=>Session('uname'),'u_id'=>Session('usid')))->find();
            $session_pass   =   session('u_pass');
            if($session_pass != $sessionuser['u_pass']){ ##密码不一致，重新登录
                $this->redirect('Login/logout');
            }
            if(!$sessionuser)
            {
                $this->redirect('Login/index');
            }
        }
        else
        {
            $this->redirect('Login/index');
        }

    }
}
