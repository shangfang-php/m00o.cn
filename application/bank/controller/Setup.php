<?php
namespace app\bank\controller;
use think\Session;
use think\Db;
class Setup extends common
{
    public function index()
    {
    	 $data = array(
            'a'=>99,
            'b'=>99,
            );
        $this->assign($data);
        return $this->fetch();
    }
    public function mon()
    {
        $fcbl = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>Session('taokeid')))->find();
        $link = Db::name('yuming')->where(array('uidss'=>Session('taokeid')))->find();
        $user = Db::name('user_tb')->field('u_type,u_dlzp,u_zpzh')->where(array('u_id'=>Session('taokeid')))->find();
        $data = array(
            'fcbl'=>$fcbl,
            'link'=>$link,
            'user'=>$user,
            'a'=>5,
            'b'=>8,
            );
        $this->assign($data);
        return $this->fetch();
    }
    public function moneys()
    {
        $num    = input('post.num');
        $link   = input('post.link');
        $type['u_dlzp']   = trim(input('post.dlzp'));
        $type['u_zpzh']   = trim(input('post.zpzh'));
        $type['u_type']   = trim(input('post.type'));
//        if($type['u_type']!='1'||$type['u_type']!='2')
//        {
//            alert('非法操作！');exit;
//        }
//        if($type['u_type']==1)
//        {
//            $user = Db::name('user_tb')->field('u_type')->where(array('u_id'=>Session('taokeid')))->find();
//            if($user['u_type']==2)
//            {
//                $time = time();
//                $d = date("d",$time);
//                if($d<28)
//                {
//                    alert('用户提现规则需要在21号后才可以修改！');exit;
//                }
//            }
//        }
        if($link)
        {
            $add['link'] = $link;
            $add['uidss'] = Session('taokeid');
            $lin = Db::name('yuming')->where(array('uidss'=>Session('taokeid')))->find();
            if( $lin)
            {
                $link = Db::name('yuming')->where(array('uidss'=>Session('taokeid')))->update($add);
            }
            else if(!$lin)
            {
                $add['time'] = time();
                $link = Db::name('yuming')->insert($add);
            }
        }
        $save['fc_minmoney'] = $num;
        $fcbl = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>Session('taokeid')))->update($save);
        $u = Db::name('user_tb')->where(array('u_id'=>Session('taokeid')))->update($type);
        if($fcbl||$link||$u)
        {
            alert('设置成功！',url('setup/mon'));exit;
        }
        else
        {
            alert('您没做任何修改！');exit;
        }
    }
    public function savepass()
    {
        $pass    = input('post.password');
        $passnew = input('post.passnew');
        $passtwo = input('post.passtwo');
        $user = Db::name('user_tb')->where(array('u_id'=>Session('taokeid')))->find();
        $a = substr( md5($pass),12);
        $password = substr($a,0,-10);

        if($user['u_pass']!=$password){
            alert('旧密码输入错误！');exit;
        }
        if($pass==$passnew)
        {
            alert('旧密码跟新密码不能相同！');exit;
        }
        if(strlen($passnew)<6)
        {
            alert('密码不能小于六位数！');exit;
        }
        if($passnew!=$passtwo){
            alert('重复密码输入不正确！');exit;
        }
        $b = substr( md5($passnew),12);
        $c = substr($b,0,-10);
        $save['u_pass'] = $c;
        $d = Db::name('user_tb')->where(array('u_id'=>Session('taokeid')))->update($save);

        if($d)
        {
            Session::delete('taokeid');
            Session::delete('taokname');
            alert('修改成功,请重新登录！');exit;
        }
        else
        {
            alert('修改失败！');exit;
        }

    }
    public function logout()
    {
        Session::delete('taokeid');
        Session::delete('taokname');
        $this->redirect('Index/index');exit;
    }
    public function set()
    {

        $fcbl = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>Session('taokeid')))->find();
        $data = array(
            'fcbl'=>$fcbl,
            'a'=>5,
            'b'=>7,
            );
        $this->assign($data);
        return $this->fetch();
    }
    public function link()
    {
        $link = Db::name('yuming')->where(array('uidss'=>Session('taokeid')))->find();
        $data = array(
            'link'=>$link,
            'a'=>5,
            'b'=>11,
        );
        $this->assign($data);
        return $this->fetch();
    }
    public function links()
    {
        $link = input('post.link');

    }
    public function fcbls()
    {
        $one3   = input('post.one3')/100;
        $one2   = input('post.one2')/100;
        $one   = input('post.one')/100;
        $two2  = input('post.two2')/100;
        $two   = input('post.two')/100;
        $three = input('post.three')/100;
        $save['fc_one'] = $one;
        $save['fc_one2'] = $one2;
        $save['fc_one3'] = $one3;
        $save['fc_two'] = $two;
        $save['fc_two2'] = $two2;
        $save['fc_three'] = $three;
        $save['fc_redo'] = 1;
        $f = Db::name('tkfcbl_tb')->where(array('fc_u_idss'=>Session('taokeid')))->update($save);
        if($f)
        {
            alert('修改成功！',url('setup/set'));exit;
        }
        else
        {
            alert('修改失败！');exit;
        }
    }
}
