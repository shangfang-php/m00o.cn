<?php
namespace app\bank\controller;
use think\Db;
use think\Loader;
class Money extends common
{
    public function index()
    {
    	$name = input('get.name');
    	$money = Db::name('money_tb')->where(array('m_u_idss'=>Session('taokeid'),'m_state'=>0))->where("m_username like '%".$name."%' or m_alipayname like '%".$name."%'")->order('m_time desc')->paginate(10, false, [
                    'query' => array('name' => $name),
                ]);
    	$data = array(
    		'money'=>$money,
            'a'=>4,
            'b'=>5,
    		);
    	$this->assign($data);
        return $this->fetch();
    }
    public function isok()
    {
        $name = input('get.name');
        $money = Db::name('money_tb')->where(array('m_u_idss'=>Session('taokeid'),'m_state'=>1))->where("m_username like '%".$name."%' or m_alipayname like '%".$name."%'")->order('m_time desc')->paginate(10, false, [
                    'query' => array('name' => $name),
                ]);
        $data = array(
            'money'=>$money,
            'a'=>4,
            'b'=>5,
            );
        $this->assign($data);
        return $this->fetch();
    }

    public function isno()
    {
        $name = input('get.name');
        $money = Db::name('money_tb')->where(array('m_u_idss'=>Session('taokeid'),'m_state'=>2))->where("m_username like '%".$name."%' or m_alipayname like '%".$name."%'")->order('m_time desc')->paginate(10, false, [
                    'query' => array('name' => $name),
                ]);
        $data = array(
            'money'=>$money,
            'a'=>4,
            'b'=>5,
            );
        $this->assign($data);
        return $this->fetch();
    }
    public function nois()
    {
        $id = trim(input('post.mid'));
        if(empty($id))
        {
            $arr = array("code"=>"-1","res"=>"参数错误！");
            jsons($arr);
        }
        else
        {
            $mon = Db::name('money_tb')->where(array('m_id'=>$id))->find();
            $user= Db::name('user_tb')->where(array('u_username'=>$mon['m_username']))->find();
            $money = $user['u_money']+$mon['m_money'];

            $us['u_money'] = $money;
            $save['m_state'] = 2;
            $save['m_endtime'] = time();

            $m = Db::name('money_tb')->where(array('m_id'=>$id))->update($save);
            $u = Db::name('user_tb')->where(array('u_username'=>$mon['m_username']))->update($us);
            if($u)
            {
               $arr = array("code"=>"0","res"=>"已拒绝");
                jsons($arr); 
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"操作失败！");
                jsons($arr);
            }
        }
    }

    public function okis()
    {
        $id = trim(input('post.mid'));
        if(empty($id))
        {
            $arr = array("code"=>"-1","res"=>"参数错误！");
            jsons($arr);
        }
        else
        {
            $ms = Db::name('money_tb')->where(array('m_id'=>$id))->find();
            $save['m_state'] = 1;
            $save['m_endtime'] = time();

            $m = Db::name('money_tb')->where(array('m_id'=>$id))->update($save);

            if($m)
            {
               Db::name('user_tb')->where(array('u_username'=>$ms['m_username']))->setInc('u_ytxmoney',$ms['m_money']);
               $arr = array("code"=>"0","res"=>"已完成");
                jsons($arr); 
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"操作失败！");
                jsons($arr);
            }
        }
    }

    public function okiss()
    {
        $id = trim(input('post.nids'));
        if(empty($id))
        {
            $arr = array("code"=>"-1","res"=>"参数错误！");
            jsons($arr);
        }
        else
        {
            $where['m_id'] = array('in',$id);
            $mon = Db::name('money_tb')->where($where)->select();
            $isw = '';
            foreach ($mon as $key => $value) {
                $isw .= $value['m_username'].',';
//                Db::name('user_tb')->where(array('u_username'=>$value['m_username']))->setInc('u_ytxmoney',$value['m_money']);
            }
            $where2['u_username'] = array('in',$isw);
            Db::name('user_tb')->where($where2)->setInc('u_ytxmoney',$value['m_money']);
            $save['m_state'] = 1;
            $save['m_endtime'] = time();
            $where['m_id'] = array('in',$id);
            $m = Db::name('money_tb')->where($where)->update($save);
            if($m)
            {
               $arr = array("code"=>"0","res"=>"已完成");
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"操作失败！");
                jsons($arr);
            }
        }
    }

    public function noiss()
    {
        $id = trim(input('post.nids'));
        if(empty($id))
        {
            $arr = array("code"=>"-1","res"=>"参数错误！");
            jsons($arr);
        }
        else
        {
            $where['m_id'] = array('in',$id);
            $mon = Db::name('money_tb')->where($where)->select();
            foreach ($mon as $key => $value) {
            $user= Db::name('user_tb')->where(array('u_username'=>$value['m_username']))->find();
            $money = $user['u_money']+$value['m_money'];
            $u = Db::name('user_tb')->where(array('u_username'=>$value['m_username']))->update(array('u_money'=>$money));
            }
            $save['m_state'] = 2;
            $save['m_endtime'] = time();
            $m = Db::name('money_tb')->where($where)->update($save);
            if($u)
            {
               $arr = array("code"=>"0","res"=>"已拒绝");
                jsons($arr); 
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"操作失败！");
                jsons($arr);
            }
        }
    }

    public function savemoney()
    {

        $user = Db::name('user_tb')->where(array('u_u_idss'=>Session('taokeid')))->select();
        $data = array(
            'user'=>$user,
            'a'=>4,
            'b'=>9,
        );
        $this->assign($data);
        return $this->fetch();
    }

    public function moneylist()
    {
        $name = input('get.name');
        $type = trim(input('get.type'));
        if($type!='')
        {
            $where['mc_type'] = $type;
        }
        $where['mc_u_idss'] = Session('taokeid');
        $mct = Db::name('money_change_tb')->field('u_id,u_username,u_nic,mc_money,mc_type,mc_explain,mc_time')->join('user_tb u','money_change_tb.mc_u_id = u.u_id',"LEFT")->where("u_username like '%".$name."%' or u_nic like '%".$name."%'")->where($where)->order('mc_time desc')->paginate(10, false, [
            'query' => array('name' => $name,'type'=>$type),
        ]);
        $data = array(
            'mct'=>$mct,
            'a'=>4,
            'b'=>10,
        );
        $this->assign($data);
        return $this->fetch();
    }


    public function user()
    {
        $id = trim(input('post.id'));
        if(empty($id))
        {
            $arr = array("code"=>"-1","res"=>"参数错误！");
            jsons($arr);
        }
        else
        {
            $user = Db::name('user_tb')->field('u_id as id,u_username as username,u_money as money')->where(array('u_id'=>$id))->find();
            if($user)
            {
                $arr = array("code"=>"0","res"=>"获取成功！","u"=>$user);
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"参数错误！");
                jsons($arr);
            }
        }
    }
    public function modifymoney()
    {
        $uid = trim(input('post.usid'));
        $type = trim(input('post.type'));
        $yhye = trim(input('post.yhye'));

        $exp = trim(input('post.exp'));
        if(empty($uid))
        {
            alert('用户参数错误！');exit;
        }

        $u = Db::name('user_tb')->where(array('u_id'=>$uid))->find();
        if($u['u_money']!= $yhye)
        {
            alert('用户余额有变更，请重新操作！！');exit;
        }
        if($type==1)
        {
            $money = trim(input('post.zeng'));
            if($money=='')
            {
                alert('金额参数错误！');exit;
            }
            $um = $u['u_money']+$money;
            $save['u_money'] = $um;
            $saveu = Db::name('user_tb')->where(array('u_id'=>$uid))->update($save);
            if($saveu)
            {
                $add['mc_u_idss'] = Session('taokeid');
                $add['mc_u_id'] = $u['u_id'];
                $add['mc_money'] = $money;
                $add['mc_type'] = $type;
                $add['mc_umoney'] = $u['u_money'];
                $add['mc_time'] = time();
                $add['mc_explain'] = $exp;
                $addmc = Db::name('money_change_tb')->insert($add);

            }
            if($addmc)
            {
                alert('操作成功！');exit;
            }
            else
            {
                alert('操作失败！');exit;
            }

        }
        else if($type==2)
        {
            $money = trim(input('post.jian'));
            if($money=='')
            {
                alert('金额参数错误！');exit;
            }
            if($u['u_money']<$money)
            {
                alert('警告！减少用户金额不能少于他原有余额！');exit;
            }
            $um = $u['u_money']-$money;
            $save['u_money'] = $um;
            $saveu = Db::name('user_tb')->where(array('u_id'=>$uid))->update($save);
            if($saveu)
            {
                $add['mc_u_idss'] = Session('taokeid');
                $add['mc_u_id'] = $u['u_id'];
                $add['mc_money'] = $money;
                $add['mc_type'] = $type;
                $add['mc_time'] = time();
                $add['mc_umoney'] = $u['u_money'];
                $add['mc_explain'] = $exp;
                $addmc = Db::name('money_change_tb')->insert($add);

            }
            if($addmc)
            {
                alert('操作成功！');exit;
            }
            else
            {
                alert('操作失败！');exit;
            }
        }
        else
        {
            alert('类型参数错误！');exit;
        }


    }

    public function income()
    {

        $allm =  Db::name('user_tb')->where(array('u_u_idss'=>Session('taokeid')))->limit(10)->order("u_allmoney desc")->select();
        $alls =  Db::name('user_tb')->where(array('u_id'=>Session('taokeid')))->find();
        $fdata =  Db::name('fdata_tb')->where(array('f_u_idss'=>Session('taokeid')))->limit(10)->order("f_sort asc")->select();
        $data = array(
            'allm'=>$allm,
            'alls'=>$alls,
            'fdata'=>$fdata,
            'allm'=>$allm,
            'a'=>4,
            'b'=>11,
        );
        $this->assign($data);
        return $this->fetch();
    }

    public function addfdata()
    {

        $count = Db::name('fdata_tb')->where(array('f_u_idss'=>Session('taokeid')))->count();
        if($count>=10)
        {
            alert('最多只能添加10条排行数据!!');exit;
        }
        $username = input('post.username');
        $money    = input('post.money');
        $sort     = input('post.sort');
        if($username==''||empty($money)||empty($sort)){
            alert('不可以提交空数据！');exit;
        }
        else
        {
            $add['f_u_idss'] = Session('taokeid');
            $add['u_username'] = $username;
            $add['u_allmoney']  = $money;
            $add['f_sort']    = $sort;
            $f = Db::name('fdata_tb')->insert($add);
            if($f)
            {
                alert('添加成功',url('money/income'));exit;
            }
            else{
                alert('添加失败');exit;
            }
        }
    }

    public function fmodify()
    {
        $id = trim(input('post.id'));
        if(empty($id))
        {
            $arr = array("code"=>"-1","res"=>"错误参数");
            jsons($arr);
        }
        else
        {
            $fdata = Db::name('fdata_tb')->where(array('f_id'=>$id))->find();
            if($fdata)
            {
                $arr = array("code"=>"0","res"=>"获取成功","arr"=>$fdata);
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"获取失败");
                jsons($arr);
            }
        }
    }

    public function modifyfdata()
    {
        $username = input('post.usernames');
        $fid      = trim(input('post.fid'));
        $money    = trim(input('post.moneys'));
        $sort     = trim(input('post.sorts'));
        if($username==''||empty($money)||empty($sort)){
            alert('不可以提交空数据！');exit;
        }
        else
        {
            $save['u_username'] = $username;
            $save['u_allmoney']  = $money;
            $save['f_sort']    = $sort;
            $f = Db::name('fdata_tb')->where(array('f_id'=>$fid))->update($save);
            if($f)
            {
                alert('修改成功',url('money/income'));exit;
            }
            else{
                alert('修改失败');exit;
            }
        }
    }

    public function delfdata()
    {
        $id = trim(input('post.id'));
        if(empty($id))
        {
            $arr = array("code"=>"-1","res"=>"错误参数");
            jsons($arr);
        }
        else
        {
            $fdata = Db::name('fdata_tb')->where(array('f_id'=>$id,"f_u_idss"=>Session('taokeid')))->delete();
            if($fdata)
            {
                $arr = array("code"=>"0","res"=>"删除成功");
                jsons($arr);
            }
            else
            {
                $arr = array("code"=>"-1","res"=>"删除失败");
                jsons($arr);
            }
        }
    }

    public function uumodify()
    {
        $title = trim(input('post.title'));
        $rdio  = trim(input('post.rdio'));
        if($title=='')
        {
            alert('排行榜标题不能为空');exit;
        }
        $save['u_title']   = $title;
        $save['u_confirm'] = $rdio;

        $alls =  Db::name('user_tb')->where(array('u_id'=>Session('taokeid')))->update($save);
        if($alls)
        {
            alert('更改成功',url('money/income'));exit;
        }
        else{
            alert('更改失败');exit;
        }
    }
    
    /**
     * Money::export_data()
     * 导出提现申请数据
     * @return void
     */
    function export_data(){
        $u_id_ss    =   Session('taokeid');

        //include_once EXTEND_PATH.'php-export-data.class.php';
        Loader::import('Php_export_data', EXTEND_PATH);
        //var_dump(class_exists('Php_export_data'));exit;
        $excel  =   new \Php_export_data('browser','monty_tb.xls');
        $excel->initialize();
        $tdarr  =   array('用户账户', '真实姓名', '支付宝帐号', '提现金额', '提现时间', '提现状态');
        $excel->addRow($tdarr);
        
        $statuses   =   array(0=>'未处理', 1=>'已提现', 2=>'拒绝提现');
        $money = Db::name('money_tb')->where(array('m_u_idss'=>$u_id_ss,'m_state'=>0))->order('m_time desc')->select();
        if($money){
            foreach($money as $val){
                $tdarr  =   array(
                                $val['m_username'],
                                $val['m_alipayname'],
                                $val['m_alipayaccount'],
                                $val['m_money'],
                                date('Y-m-d H:i:s', $val['m_time']),
                                $statuses[$val['m_state']],
                            );
                
                $excel->addRow($tdarr);
            }
        }
        
    	$excel->finalize();
        exit;
    }
}
