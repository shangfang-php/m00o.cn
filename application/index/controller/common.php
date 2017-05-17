<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use think\Db;
class Common extends Controller
{
    public function _initialize()
    {
        header("Content-type: text/html; charset=utf-8");
        if(Session('uname')!=""||Session('usid')!="")
        {
            $sessionuser = Db::name('user_tb')->where(array('u_username'=>Session('uname'),'u_id'=>Session('usid')))->find();
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
