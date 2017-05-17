<?php
namespace app\index\controller;
use app\index\model\index as indexo;
use think\Db;
use think\Request;
include_once("tb/TopSdk.php");
class Index extends common
{
    public function index() //首页
    {
    	$uid = session('usid');
		$day0 = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间
		$day = time();//现在时间
		$day1 = strtotime(date("Y-m-d"));//今天开始时间
        
        list($year, $month, $day)   =   explode('-', date('Y-m-d'));
		$ortb = ortb(time());
		$prevortb = prevortb(time());
		if($month == '01'){
			$y = $year - 1;
			$smonth = strtotime($y.'-'.'12-01');
			$emonth = strtotime($year.'-'.'01-01');
		}else{
			$mon = $month - 1;
			$smonth = strtotime($year.'-'.$mon.'-01');
			$emonth = strtotime($year.'-'.$month.'-01');
		}
		$where['o_u_id'] = session('usid');
		$where['o_creattime'] = array(array('gt',$day1),array('lt',time()));//time() 之前用的$day  $day你在17行已经重新赋值了，不在是当前的时间戳了 2017.04.02 --刺客
		$nowday = Db::name(tb())->field('o_id')->where($where)->count();
                
		$nowmonth = Db::name(tb())->field('o_id')->where(array('o_u_id'=>session('usid')))->count();
		$all = Db::query('select * from '.$ortb.' where or_u_id = '.$uid);
		//dump($all);exit;
		$alll = Db::query('select * from '.$prevortb.' where or_u_id = '.$uid);
		$nowdayvalue = 0;
		foreach ($all as $key => $value) {
			if($value['or_o_creattime'] > $day1 && $value['or_o_creattime'] < time()){//time() 之前用的$day  $day你在17行已经重新赋值了，不在是当前的时间戳了 2017.04.02 --刺客
				$nowdayvalue += $value['or_money'];
			}
		}
		$yesdayvalue = 0;
		if($day == 1){
			foreach ($alll as $key => $value) {
				if($value['or_o_creattime'] > $day0 && $value['or_o_creattime'] < $day1){
					$yesdayvalue += $value['or_money'];
				}
			}
		}else{
			foreach ($all as $key => $value) {
				if($value['or_o_creattime'] > $day0 && $value['or_o_creattime'] < $day1){
					$yesdayvalue += $value['or_money'];
				}
			}
		}
		$yesmonthvalue = 0;
		foreach ($alll as $key => $value) {
			if($value['or_o_creattime'] > $smonth && $value['or_o_creattime'] < $emonth){
				//$yesmonthvalue += $value['or_money'];
				$yesmonthvalue  =   bcadd($yesmonthvalue, $value['or_money'], 2);
			}
		}
		$nowmonthvalue = 0;
		foreach ($all as $key => $value) {
			if($value['or_o_creattime'] > $emonth && $value['or_o_creattime'] < time()){//time() 之前用的$day  $day你在17行已经重新赋值了，不在是当前的时间戳了 2017.04.02 --刺客
				//$nowmonthvalue += $value['or_money'];
				$nowmonthvalue  =   bcadd($nowmonthvalue, $value['or_money'], 2);
			}
		}
		//$click = Db::name('user_tb')->field('u_click')->where('u_id',session('usid'))->find();
		$click = Db::query('select u_click from user_tb where u_id = '.$uid);
		//代理数量
		if(session('level') == 1){
			$e = Db::name('user_tb')->field('u_id')->where('u_parent_u_id',session('usid'))->select();
			$s = '';
			$ss = '';
			foreach($e as $k=>$v){
				$s .= $v['u_id'].',';
			}
			$ww['u_parent_u_id'] = array('in',$s);
			$ee = Db::name('user_tb')->field('u_id')->where($ww)->select();
			foreach($ee as $k=>$v){
				$ss .= $v['u_id'].',';
			}
			$str = $s.$ss;
			$c = count(explode(',',$str));
			$this->assign(['num'=>$c-1,'leve'=>1]);
		}else if(session('level') == 2){
			$count = Db::name('user_tb')->field('u_id')->where('u_parent_u_id',session('usid'))->count();
			$this->assign(['num'=>$count,'leve'=>2]);
		}else{
			$this->assign(['num'=>0,'leve'=>session('level')]);
		}
		
		$data = [
			'nowday'        => $nowday, //今日付款数
			'nowmonth'      => $nowmonth, //本月订单数
			'nowdayvalue'   => $nowdayvalue,//今日预估
			'yesdayvalue'   => $yesdayvalue,//昨日预估
			'nowmonthvalue' => $nowmonthvalue,//上月预估
			'yesmonthvalue' => $yesmonthvalue,//本月预估
			'click'         => $click[0]['u_click'],//点击数
			'level'         => session('level')
		];
		$this->assign($data);
        return $this->fetch();
    }
    //首页下拉
    public function loads(){
    	
    	$day0 = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间
		$day = time();//现在时间
		$day1 = strtotime(date("Y-m-d"));//今天开始时间
		$year = date('Y',time());
		$month = date('m',time());
		$ortb = ortb(time());
		if($month == '01'){
			$y = $year - 1;
			$smonth = strtotime($y.'-'.'12-01');
			$emonth = strtotime($year.'-'.'01-01');
		}else{
			$mon = $month - 1;
			$smonth = strtotime($year.'-'.$mon.'-01');
			$emonth = strtotime($year.'-'.$month.'-01');
		}
    	//代理
		$dl = Db::name('user_tb')->field('u_id')->where('u_parent_u_id',session('usid'))->select();
		$aa = [];
		$array = [];
		foreach($dl as $k => $v){
			$aa[] = $v['u_id'];
		}
		$aaa = '';
		foreach($aa as $k => $v){
			$aaa .= $v.',';
		}
		$w['u_parent_u_id'] = array('in',$aaa);
		$dll = Db::name('user_tb')->field('u_id')->where($w)->select();
		foreach ($dll as $k => $v) {
			$array[] = $v['u_id'];
		}
		$arr = array_merge($aa,$array);
		$where1['o_creattime'] = array(array('gt',$day1),array('lt',$day));
		if(count($arr) != 0){
			$where1['o_u_id']  = ['in',$arr];
			$where2['o_u_id']  = ['in',$arr];
			$where5['or_u_id'] = session('usid');
			$where55['o_u_id'] = session('usid');
			// $where5['o_u_id'] = ['in',$arr];
			$where6['or_u_id'] = session('usid');
			$where66['o_u_id'] = session('usid');
			// $where6['o_u_id'] = ['in',$arr];
			$dlnowday = Db::name(tb())->field('o_id')->where($where1)->count();
			$dlnowmonth = Db::name(tb())->field('o_id')->where($where2)->count();
			//今日代理
			$where5['or_o_creattime'] = array(array('gt',$day1),array('lt',$day));
			$where55['o_creattime'] = array(array('gt',$day1),array('lt',$day));
			//$dlnowdayvalue = db('order_record_tb')->field('or_money')->where($where5)->sum('or_money');
			$dlnowdaydd = Db::name(tb())->field('o_ordernum')->where($where55)->select();
			$dlnowdayddd = array();
			foreach ($dlnowdaydd as $key => $value) {
				$dlnowdayddd[]= $value['o_ordernum'];
			}
			//dump($dlnowdayddd).'<br />';exit;
			$dlnowdaycc = Db::name($ortb)->field('or_o_ordernum,or_money')->where($where5)->select();
			$dlnowdayccc = array();
			$allmoney=0.00;
			foreach ($dlnowdaycc as $key => $value) {
				if(in_array($value['or_o_ordernum'],$dlnowdayddd)){

				}else{
					$allmoney+=$value['or_money'];
				}
			}
			//dump($allmoney);exit;
			//昨日代理
			$where6['or_o_creattime'] = array(array('gt',$day0),array('lt',$day1));
			$where66['o_creattime'] = array(array('gt',$day0),array('lt',$day1));  
			//$dlyesdayvalue = db('order_record_tb')->field('or_money')->where($where6)->sum('or_money');
			$dlyesdaydd = Db::name(tb())->field('o_ordernum')->where($where66)->select();
			$dlyesdayddd = array();
			foreach ($dlyesdaydd as $key => $value) {
				$dlyesdayddd[]= $value['o_ordernum'];
			}
			//dump($dlyesdayddd).'<br />';
			$dlyesdaycc = Db::name($ortb)->field('or_o_ordernum,or_money')->where($where6)->select();
			$dlyesdayccc = array();
			$allsmoney=0.00;
			foreach ($dlyesdaycc as $key => $value) {
				if(in_array($value['or_o_ordernum'],$dlyesdayddd)){

				}else{
					$allsmoney+=$value['or_money'];
				}
			}
		
		}else{
			$dlnowday      =0;
			$dlnowmonth    =0;
			$allmoney =0;
			$allsmoney =0;
		}
		
		$data = [
			'dlnowday'      => $dlnowday, //dl今日付款数
			'dlnowmonth'    => $dlnowmonth, //dl本月订单数
			'dlnowdayvalue' => $allmoney,//今日代理
			'dlyesdayvalue' => $allsmoney,//昨日代理 
		];
		return json($data);
    }
	//本月自己
    public function lists() 
    {
		$day = time();
		$day1 = strtotime(date("Y-m-d"));
		$ortb = ortb(time());
		$id = input('param.uid');
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));exit;
		}
		$type = input('param.utype');
		$where['o_u_id'] = $id;
		$where['or_u_id'] = $id;
		if($type == 1){
			$where['o_creattime'] = array(array('gt',$day1),array('lt',$day));
			session('utype',1);
		}else{
			session('utype',null);
		}
		$tb = tb();
		$list = Db::name($tb)->join($ortb,$tb.'.o_ordernum = '.$ortb.'.or_o_ordernum',"LEFT")->order('o_creattime desc')->where($where)->paginate(5);
		//dump($list);exit;
		$data = [
			'list' => $list,
			'uid' => $id
		];
		$this->assign($data);
        return $this->fetch();
    }
    //上月自己
	public function sylists() 
    {
		$id = input('param.uid');
		$ortb = prevortb(time());
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));exit;
		}
		$tb = prevtb();
		//dump($tb).exit;
		$where['o_u_id'] = $id;
		$where['or_u_id'] = $id;
		$list = Db::name($tb)->join($ortb,$tb.'.o_ordernum = '.$ortb.'.or_o_ordernum',"LEFT")->order('o_creattime desc')->where($where)->paginate(5);
		
		$data = [
			'list' => $list,
			'uid' => $id
		];
		$this->assign($data);
        return $this->fetch();
    }
    //本月代理
	public function listss() 
    {
		$day = time();
		$day1 = strtotime(date("Y-m-d"));
		$ortb = ortb(time());
		$id = input('param.uid');
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));exit;
		}
		$type = input('param.utype');

		$tb = tb();
		$u = Db::name('user_tb')->where(['u_id'=>$id])->find();
		if($u['u_leve'] == 3){
			alert('你没有下级代理',url('index'));exit;
		}
		$user = Db::name('user_tb')->where(['u_parent_u_id'=>$id])->select();
		$aa = [];
		$array = [];
		foreach($user as $k => $v){
			$aa[] = $v['u_id'];
		}
		foreach($aa as $k => $v){
			$ddll = Db::name('user_tb')->field('u_id')->where('u_parent_u_id',$v)->select();
			foreach($ddll as $kk => $vv){
				$array[] = $vv['u_id'];
			}
		}
		$arr = array_merge($aa,$array);

		//dump($arr);exit;
		if($type == 2){
			$where['o_creattime'] = array(array('gt',$day1),array('lt',$day));
			session('utype',2);
		}else{
			session('utype',null);
		}
		if(count($arr) != 0){
			$where['o_u_id'] = ['in',$arr];
			$where['or_u_id'] = session('usid');
			$list = Db::name($tb)->join($ortb,$tb.'.o_ordernum = '.$ortb.'.or_o_ordernum',"LEFT")->order('o_creattime desc')->where($where)->paginate(5);
				$data = [
				'list' => $list,
				'uid' => $id
				];
			$this->assign($data);
    		return $this->fetch();
		}else{
			return $this->fetch('wdl');
		}
    }
    //上月代理
	public function sylistss() 
    {
		$id = input('param.uid');
		$ortb = prevortb(time());
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));exit;
		}
		$tb = prevtb();
		$user = Db::name('user_tb')->where(['u_parent_u_id'=>$id])->select();
		$aa = [];
		$array = [];
		foreach($user as $k => $v){
			$aa[] = $v['u_id'];
		}
		foreach($aa as $k => $v){
			$ddll = Db::name('user_tb')->field('u_id')->where('u_parent_u_id',$v)->select();
			foreach($ddll as $kk => $vv){
				$array[] = $vv['u_id'];
			}
		}
		$arr = array_merge($aa,$array);
		//dump($arr);exit;
		if(count($arr) != 0){
			$where['o_u_id'] = ['in',$arr];
			$where['or_u_id'] = session('usid');
			$list = Db::name($ortb)->join($tb,$ortb.'.or_o_ordernum = '.$tb.'.o_ordernum')->order('o_creattime desc')->where($where)->paginate(5);
			$data = [
				'list' => $list,
				'uid' => $id
			];
			$this->assign($data);
	        return $this->fetch();
		}else{
			return $this->fetch('wdl');
		}
		
    }
    //代理数量
	public function dlnum(){
		$leve = input('param.leve');
		if($leve == 1){
			$e = Db::name('user_tb')->where('u_parent_u_id',session('usid'))->select();
			$s = array();
			foreach($e as $k=>$v){
				$s[] = Db::name('user_tb')->where('u_parent_u_id',$v['u_id'])->select();
			}
			foreach($s as $k=>$v){
				foreach($v as $kk=>$vv){
				}
			}
			//dump($e);dump($s);exit;
			$data=[
				'e'=>$e,
				's'=>$s
			];
		}else if($leve == 2){
			$c[] = Db::name('user_tb')->where('u_parent_u_id',session('usid'))->select();
			$e = array(
				 array (
					"u_username" => "qwertyuiop123456"
				),
			);
			//dump($e);dump($c);exit;
			$data=[
				'e'=>$e,
				's'=>$c
			];
		}else if($leve == 3){
			alert('你没有下级',url('index'));exit;
		}else{
			alert('不可看',url('index'));exit;
		}
		$this->assign($data);
		return $this->fetch();
	}
	//排行榜
	public function rank() {
		$user = Db::name('user_tb')->where('u_id',session('usid'))->find();
		$tk = Db::name('user_tb')->where('u_id',$user['u_u_idss'])->find();
		$where['u_u_idss'] = $user['u_u_idss'];
		$map['f_u_idss'] = $user['u_u_idss'];
		$rank = Db::name('user_tb')->order('u_allmoney desc')->where($where)->select();
		$rank1 = Db::name('fdata_tb')->order('u_allmoney desc')->where($map)->select();
		//dump($rank);exit;
		if($tk['u_confirm'] == 2){
			$this->assign('title',$tk['u_title']);
			$this->assign('rank',$rank);
			return $this->fetch();
		}else if($tk['u_confirm'] == 1){
			$this->assign('title',$tk['u_title']);
			$this->assign('rank',$rank1);
			return $this->fetch();
		}else if($tk['u_confirm'] == 0){
			return $this->fetch('rank1');
		}
		
	}
	//提现
    public function money() 
    {
		$uid = input('param.uid');
		if($uid != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));exit;
		}
		$user = Db::name('user_tb')->where(['u_id'=>$uid])->find();
		$u = Db::name('user_tb')->where('u_id',$user['u_u_idss'])->find();
		if($user['u_leve'] == 0){
			alert('管理员不能提现',url('index/my',['uid'=>$uid]));exit;
		}
        
		if($u['u_type'] == 1 || $u['u_type'] == 0){
			$data = [
				'money' => $user['u_money'],
				'username' => $user['u_username'],
				'uid' => $user['u_id'],
			];	
		}else if($u['u_type'] == 2){
			$d = date('d',time());
			if($d < 21){
				//alert('21号开始提现',url('index/my',array('uid'=>$user['u_id'])));
				$m = date('Y-m',time());
				$mm = date('Y-m',strtotime("last month"));
				//echo $m;exit;
				$f = Db::name('income_tb')->where(array('i_y_m'=>$m,'i_uid'=>$user['u_id'],'i_idss'=>$user['u_u_idss']))->find();
				$ff = Db::name('income_tb')->where(array('i_y_m'=>$mm,'i_uid'=>$user['u_id'],'i_idss'=>$user['u_u_idss']))->find();
                
				$data = [
					'username' => $user['u_username'],
					'uid' => $user['u_id'],
				];
				if($f){
					$fm = $f['i_money'];
				}else{
					$fm = 0;
				}
				if($ff){
					$ffm = $ff['i_money'];
				}else{
					$ffm = 0;
				}
                
				//$data['money'] = $user['u_money'] - $fm -$ffm;
                $data['money'] = bcsub($user['u_money'], bcadd($fm, $ffm, 2), 2);
				if($data['money'] < 0){
					$data['money'] = 0;
				}
				//echo $data['money'];exit;
			}else{
				$m = date('Y-m',time());
				$f = Db::name('income_tb')->where(array('i_y_m'=>$m,'i_uid'=>$user['u_id'],'i_idss'=>$user['u_u_idss']))->find();
				$data = [
					'username' => $user['u_username'],
					'uid' => $user['u_id'],
				];
				if($f){
					//$data['money'] = $user['u_money'] - $f['i_money'];
                    $data['money'] = bcsub($user['u_money'], $f['i_money'], 2);
					if($data['money'] < 0){
						$data['money'] = 0;
					}
				}else{
					$data['money'] = $user['u_money'];
				}
				//dump($data);exit;
			}
		}
		$this->assign($data);
        return $this->fetch();
    }
    //提现操作
	public function tx() 
    {
		$alipayaccount = input('post.alipayaccount');
		$alipayname = input('post.alipayname');
		$username = input('post.username');
		$txmoney = input('post.txmoney');
		$uid = session('usid');
		if($uid != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));exit;
		}
		$user = Db::name('user_tb')->where(['u_id'=>$uid])->find();
		$u = Db::name('user_tb')->where('u_id',$user['u_u_idss'])->find();
		if($u['u_type'] == 1 || $u['u_type'] == 0){
			$money = $user['u_money'];
		}else if($u['u_type'] == 2){
			$d = date('d',time());
			if($d<21){
				$m = date('Y-m',time());
				$mm = date('Y-m',strtotime("last month"));
				//echo $m;exit;
				$f = db('income_tb')->where(array('i_y_m'=>$m,'i_uid'=>$user['u_id'],'i_idss'=>$user['u_u_idss']))->find();
				$ff = db('income_tb')->where(array('i_y_m'=>$mm,'i_uid'=>$user['u_id'],'i_idss'=>$user['u_u_idss']))->find();
				$data = [
					'username' => $user['u_username'],
					'uid' => $user['u_id'],
				];
				if($f){
					$fm = $f['i_money'];
				}else{
					$fm = 0;
				}
				if($ff){
					$ffm = $ff['i_money'];
				}else{
					$ffm = 0;
				}

				$data['money'] = $user['u_money'] - $fm -$ffm;
				if($data['money'] < 0){
					$data['money'] = 0;
				}
				$money = $data['money'];
			}else{
				$m = date('Y-m',time());
				$f = Db::name('income_tb')->where(array('i_y_m'=>$m,'i_uid'=>$user['u_id'],'i_idss'=>$user['u_u_idss']))->find();
				$data = [
					'username' => $user['u_username'],
					'uid' => $user['u_id'],
				];
				if($f){
					$data['money'] = $user['u_money'] - $f['i_money'];
					if($data['money'] < 0){
						$data['money'] = 0;
					}
				}
				$money = $data['money'];
			}
		}
		if($txmoney > $money){
			alert('你没那么多钱',url('index/money',['uid'=>$uid]));exit;
		}else{
			$id = Db::name('user_tb')->where('u_username',$username)->find();
			$min = Db::name('tkfcbl_tb')->where('fc_u_idss',$id['u_u_idss'])->find();
			if($txmoney < $min['fc_minmoney']){
				alert('提现金额不能小于'.$min['fc_minmoney'],url('index/my',['uid'=>$uid]));exit;
			}else{
				$data = [
					'm_alipayaccount' => $alipayaccount,
					'm_alipayname' => $alipayname,
					'm_username' => $username,
					'm_money' => $txmoney,
					'm_state' => 0,
					'm_time' => time(),
					'm_u_idss' => $id['u_u_idss'],
				];
				$add = db('money_tb')->insert($data);
				if($add){
					$save = Db::name('user_tb')->where('u_id',$uid)->setDec('u_money',$txmoney);
					alert('已提交给管理员，请等待',url('index/my',['uid'=>$uid]));exit;
				}	
			}
			
		}
    }
    //邀请码
	public function yqm() 
    {
		$id = input('param.uid');
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));exit;
		}
		$user = Db::name('user_tb')->where('u_id',$id)->find();
		$data = [
			'yqm' => $user['u_code'],
		];
		$this->assign($data);
        return $this->fetch();
    }
	//个人中心
    public function my() 
    {
		$id = input('param.uid');
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));exit;
		}
		$user = Db::name('user_tb')->where('u_id',$id)->find();
		$data = ['user' => $user,];
		$this->assign($data);
		return $this->fetch();
    }
	//修改密码
    public function modify() 
    {
		header("Content-type:text/html;charset=utf-8");
		$id = input('param.uid');
		if($id == 937)
		{
			alert('无权访问！');exit;
		}
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));exit;
		}
		$user = db('user_tb')->where('u_id',$id)->find();
		$data = [
			'user' => $user
		];
		$this->assign($data);
        return $this->fetch();
    }
    //修改密码验证
	public function ver(){ 
		$pass1 = input('post.password1');
		$pass2 = input('post.password2');
		$id = input('post.id');
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));exit;
		}
		$a = substr( md5($pass1),12);
		$password1 = substr($a,0,-10);
		$b = substr( md5($pass2),12);
		$password2 = substr($b,0,-10);
		$u = db('user_tb')->where(['u_id'=>$id,'u_pass'=>$password1])->find();
		if(!$u){
			$arr = array("code"=>"1","msg"=>"原密码错误");
			jsons($arr);
		}else{
			$save = Db::table('user_tb')->where('u_id',$id)->update(['u_pass' => $password2]);
			if($save){
				$arr = array("code"=>"0","msg"=>"修改成功");
				jsons($arr);
			}else{
				$arr = array("code"=>"0","msg"=>"修改失败(可能是两次密码一样)");
				jsons($arr);
			}
		}
	}
	//搜索订单
	public function search(){
		$num = input('post.num');
		$ortb = ortb(time());
		$prevortb = prevortb(time());
		if(!$num){
			alert('请输入订单号',url('index/index'));exit;
		}else{
			$where['or_o_ordernum'] = array('like','%'.$num.'%');
			$where['or_u_id'] = session('usid');
			$ord = db(tb())->join($ortb,tb().'.o_ordernum = '.$ortb.'.or_o_ordernum')->where($where)->select();
			$ord1 = db(prevtb())->join($prevortb,prevtb().'.o_ordernum = '.$prevortb.'.or_o_ordernum')->where($where)->select();
			//dump($ord);dump($ord1);exit;
			if($ord || $ord1){
				$ooo = array_merge($ord, $ord1);
				//dump($ooo);exit;
				$this->assign('ord',$ooo);
				return $this->fetch();
			}else if(!$ord && !$ord1){
				alert('未查询到任何订单！',url('index/index'));exit;
			}	
		}
	}
	//找品
	public function searchp(){
		$name = trim(input('get.name'));
		$p = trim(input('get.p'));
		if($p <= 1){
			$p = 1;
		}
		try
		{
			//$z = file_get_contents('http://www.xccloud.xin/index.php?m=Api&keyword='.$name.'&p='.$p);
			$z = file_get_contents("http://so.00o.cn/index.php?keyword=".$name."&p=".$p."");
			//$z = file_get_contents('http://www.t5166.com/index.php?m=Api&keyword='.$name.'&p='.$p);
		}
		catch(\Exception $e)
		{
			alert('网络不稳定,请稍候重试1!',url('index/index'));exit;
		}
		
		$zz = json_decode($z,true);
		//echo '<pre>';
		//print_r($zz);exit;
		if(!$zz){
			alert('没有数据',url('index/index'));exit;
		}
		$t = Db::name('tgw_tb')->join('user_tb','tgw_tb.t_u_id = user_tb.u_id','LEFT')->where('t_u_id',session('usid'))->select();
		$u = Db::name('user_tb')->where('u_id',session('usid'))->find();
		$uu = Db::name('user_tb')->where('u_id',$u['u_u_idss'])->find();
		if(!$t){
			alert('管理员没有给你设置PID',url('index/index'));exit;
		}else{
			if ($uu['u_dlzp'] != 1) {
				return $this->fetch('searchss');
			}else{
				$data = [
					'list'=>$zz,
					't'=>$t,
					'idss'=>$t[0]['u_u_idss'],
					'uid'=>$t[0]['t_u_id'],
					'name'=>$name,
					'zpzh'=>$uu['u_zpzh'],
				];	
				$this->assign($data);
				return $this->fetch();
			}
		}
	}
	//找品下拉
	public function loadss(){
		$name = input('post.name');
		$p = input('post.p');
		$pp = $p+1;
		$data = [
			'datas'=> cjapi($name,$pp),
			'page' => $p+1,
		];
		return json($data);
	}
	//生成文案
	public function tj(){
		$post = $_POST;
		$tkid = $post['tkid'];
		if(!$tkid){
			alert('参数错误');exit;
		}
		//$vv = sc($tkid); //Jane update 2017-4-21
		//$vv = sc($post['jihua_type'],$post['coupon_id']);

		if(!isset($post['jihua_type'])||empty($post['jihua_type'])||is_null($post['jihua_type'])){
			$post['jihua_type'] = '';
		}

		$vv = sc($post['coupon_id'],$post['jihua_type']);
		$logo = $post['imgurl'];
		$title = $post['gname'];
		$url_uland="http://uland.taobao.com/coupon/edetail?activityId=".$vv['vid']."&pid=".$post['pid']."&itemId=".$post['gid']."&src=mili_etuia&dx=".$vv['dx'];
		$tkl_post['url'] = urlencode($url_uland);
                $tkl_post['logo'] = $logo;
                $tkl_post['title'] = $title;
                $tkl = request_post('http://kl2.00o.cn/index.php',$tkl_post);
                
		//$resp = $c->execute($req);
		$url="http://uland.taobao.com/coupon/edetail?activityId=".$vv['vid']."&pid=".$post['pid']."&itemId=".$post['gid']."&src=mili_etuia&dx=".$vv['dx'];
		$surl = request_post('http://00o.cn/api.php',array('smallurl'=>$url));
		if(!$surl){
			$surl = '';
		}else{
			$surl = json_decode($surl,true);
			$surl = $surl['url'];
		}
		$jj = wa($post['tkid']);
		if($jj){
			$jjj = $jj[0];
		}else{
			$jjj = '';
		}
		$data = [
			'list' => $post,
			'jj'   => $jjj,
			'tkl'  => $tkl,
			'surl' => $surl
		];

		$add['h_goodsid'] = $post['gid'];
		$add['h_alimamaid'] = $post['mm1'];
		//$add['h_time'] = strtotime(date('Y-m-d',time()));
		$add['h_time'] = strtotime(date('Y-m-d'));
		//$add['h_time'] = time();
		$aa = Db::name('high_tb')->where($add)->find();
		if(!$aa){
			Db::name('high_tb')->insert($add);
		}
		$this->assign($data);
		return $this->fetch();
	}
}
