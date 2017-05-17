<?php
namespace app\super\controller;
use think\Session;
use think\Db;
class Setup extends common
{
    public function index()
    {
         $su = Db::name('super')->where(array('id'=>Session('supersid')))->find();
    	 $data = array(
            'su'=>$su,
            'a'=>5,
            'b'=>6,
            );
        $this->assign($data);
        return $this->fetch();
    }

    public function savepass()
    {
        $text    = input('post.text');
        $pass    = input('post.passold');
        $passnew = input('post.passnew');
        $passtwo = input('post.passtwo');

        $user = Db::name('super')->where(array('id'=>Session('supersid')))->find();
        
        $a = substr( md5($pass),0,4);
        $b = substr( md5($pass),4,4);//
        $c = substr( md5($pass),8,4);
        $d = substr( md5($pass),12,4);//
        $e = substr( md5($pass),16,4);
        $f = substr( md5($pass),20,4);
        $g = substr( md5($pass),24,4);
        $h = substr( md5($pass),28,4);//
        $password = $g.$a.$e.$f.$c;
        if($user['superpas']!=$password){
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
        $a = substr( md5($passnew),0,4);
        $b = substr( md5($passnew),4,4);//
        $c = substr( md5($passnew),8,4);
        $d = substr( md5($passnew),12,4);//
        $e = substr( md5($passnew),16,4);
        $f = substr( md5($passnew),20,4);
        $g = substr( md5($passnew),24,4);
        $h = substr( md5($passnew),28,4);//
        $pas = $g.$a.$e.$f.$c;
        $save['supername'] = $text;
        $save['superpas'] = $pas;
        $d = Db::name('super')->where(array('id'=>Session('supersid')))->update($save);

        if($d)
        {
            Session::delete('supersid');
            Session::delete('superspic');
            Session::delete('super');
            alert('更变成功,请重新登录！');exit;
        }
        else
        {
            alert('修改失败！');exit;
        }

    }

    public function logout()
    {
        Session::delete('supersid');
        Session::delete('superspic');
        Session::delete('super');
        $this->redirect('Index/index');exit;
    }

    public function link()
    {
        $name = input('get.name');
        $yuming = Db::name('yuming')->join('user_tb u','yuming.uidss=u.u_id',"LEFT")->where("u_username like '%".$name."%' or u_nic like '%".$name."%'")->paginate(10,false,[
            'query' => array('name'=>$name,),
        ]);
        $data = array(
            'yuming'=>$yuming,
            'a'=>6,
            'b'=>1,
        );
        $this->assign($data);
        return $this->fetch();
    }
}
