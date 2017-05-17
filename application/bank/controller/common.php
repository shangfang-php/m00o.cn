<?php
namespace app\bank\controller;
use think\Controller;
use think\Session;
use think\Db;
class common extends Controller
{
    public function _initialize()
    {

        header("Content-type:text/html;charset=utf-8");
        
        if(Session('taokeid')!=""||Session('taokname')!="")
        {
            $sessionuser = Db::name('user_tb')->field('u_id as usid,u_username as uname,u_code as code,u_money as money')->where(array('u_username'=>Session('taokname'),'u_id'=>Session('taokeid'),"u_leve"=>0))->find();

            $mmxx = Db::name('money_tb')->where(array('m_u_idss'=>Session('taokeid'),'m_state'=>0))->find();
            $authorize = Db::name('authorize_tb')->where(array('a_u_id'=>Session('taokeid')))->find();
            if(!$sessionuser)
            {
                $this->redirect('Login/index');
            }
            $sion = array(
                'sion' =>$sessionuser,
                'mmxx' =>$mmxx,
                'authorize' =>$authorize,
            );
            $this->assign($sion);
        }
        else
        {
            $this->redirect('Login/index');
        }
    }
}
