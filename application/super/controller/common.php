<?php
namespace app\super\controller;
use think\Controller;
use think\Session;
use think\Db;
class common extends Controller
{
    public function _initialize()
    {
        header("Content-type:text/html;charset=utf-8");
        if(Session('supersid')!=""||Session('superspic')!="")
        {
             $user = Db::name('super')->where(array('id'=>Session('supersid'),'supername'=>Session('superspic'),'power'=>Session('super')))->find();
             if(!$user)
             {
                Session::delete('supersid');
                Session::delete('superspic');
                Session::delete('super');
                alert('账户已被更改！请重新登录！',url('Login/index'));exit;
             }
        }
        else
        {
            $this->redirect('Login/index');
        }
    }
}
