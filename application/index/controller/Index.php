<?php
namespace app\index\controller;
use app\index\model\index as indexo;
use think\Db;
use think\Request;
include_once("tb/TopSdk.php");
class Index extends common
{
	public function index(){
        $uid    =   session('usid');
        $today_start    =   strtotime(date('Y-m-d'));
        //var_dump($today_start);exit;
        $todayData  =   getUserMoneyOrder($uid, $today_start); ##获取当日数据
        $order_record_table     =   ortb(time());
        $todayData['allMoney']  =   Db::table($order_record_table)->where(array('or_o_creattime'=>['>=', $today_start], 'or_u_id'=>$uid))->sum('or_money'); ##今日收入包含下级贡献

        $month_start    =   strtotime(date('Y-m'));
        $monthData  =   getUserMoneyOrder($uid, $month_start); ##获取当月数据
        
        $userInfo   =   getUserInfo(intval($uid)); ##获取代理商信息

		$t = Db::name('tgw_tb')->join('user_tb','tgw_tb.t_u_id = user_tb.u_id','LEFT')->where('t_u_id',session('usid'))->select();
		//print_r($t);exit;
		$list_data = request_get("http://so.00o.cn/index.php");
		if(!$list_data){
			$list = array();
		}else{
			$list = json_decode($list_data,true);
		}
		//echo '<pre>';
		//print_r($list);exit;
		//$list = json_decode(file_get_contents("http://so.00o.cn/index.php"),true);
		//$u = Db::name('user_tb')->where('u_id',session('usid'))->find();
        $u  =   $userInfo;
		$uu = Db::name('user_tb')->where('u_id',$u['u_u_idss'])->find();
		$data = [
			'todayData'      => $todayData, //今日订单数及收入
			'monthData'      => $monthData, //本月订单数及收入
			'userInfo'       => $userInfo,//代理商信息
			'yesdayvalue'   => 0,//昨日预估
			'nowmonthvalue' => 0,//上月预估
			'yesmonthvalue' => 0,//本月预估
			'click'         => 0,//点击数
			'level'         => 1,
			't'				=>$t,
			/*'zp'			=>$uu['u_zpzh'],*/
			'uu'			=>$uu,
			'list'			=>$list,
			'idss'			=>$userInfo['u_u_idss'],
			'uid'			=>$uid,
			'len'			=>count($t),
            'notice'        =>  getNotice($uid, $userInfo),
            'adTime'        =>  cookie('adTime') ? cookie('adTime') : 0,
		];
		//print_r($data);exit;
		$this->assign($data);
		return $this->fetch();
	}
	//新版首页分类数据获取
	public function ajaxdata(){
		//echo 111;exit;
		//接收参数
		$cid = isset($_GET['cid'])?intval($_GET['cid']):0;
		$p = isset($_GET['p'])?intval($_GET['p']):1;
		/*if($cid>0){
			$url = "http://so.00o.cn/index.php?cid=".$cid."&p=".$p." ";
		}else{
			$url = "http://so.00o.cn/index.php?p=".$p;
		}*/
		$url = "http://so.00o.cn/index.php";
		//$datas = request_get($url);
		$param = array(
			"cid"=>$cid,
			"p"=>$p
		);
		$datas = request_post($url,$param);
		if(!$datas){
			$list = array();
		}else{
			$list = json_decode($datas,true);
		}
		//$datas = file_get_contents($url);
		$data = [
			'datas'=> $list,
			'page' => $p,
		];
		//print_r($list);exit;
		//$list = json_decode($data,true);
		exit(json_encode($data));
	}

	public function index_bak() //首页  Jane 备注 2017-5-18
	{
		//echo 333;exit;
		$uid = session('usid');
		$day0 = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间
		$day = time();//现在时间
		$day1 = strtotime(date("Y-m-d"));//今天开始时间
		$year = date('Y',time());
		$month = date('m',time());
		$day = date('d',time());
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
				$yesmonthvalue += $value['or_money'];
			}
		}
		$nowmonthvalue = 0;
		foreach ($all as $key => $value) {
			if($value['or_o_creattime'] > $emonth && $value['or_o_creattime'] < time()){//time() 之前用的$day  $day你在17行已经重新赋值了，不在是当前的时间戳了 2017.04.02 --刺客
				$nowmonthvalue += $value['or_money'];
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

		/*$data = [
			'nowday'        => $nowday, //今日付款数
			'nowmonth'      => $nowmonth, //本月订单数
			'nowdayvalue'   => $nowdayvalue,//今日预估
			'yesdayvalue'   => $yesdayvalue,//昨日预估
			'nowmonthvalue' => $nowmonthvalue,//上月预估
			'yesmonthvalue' => $yesmonthvalue,//本月预估
			'click'         => $click[0]['u_click'],//点击数
			'level'         => session('level')
		];*/
		$z = file_get_contents('http://so.00o.cn/index.php?keyword=女装&p=1');//Add By Jane 2017-5-9
		$zz = json_decode($z,true);//Add By Jane 2017-5-9
		$t = Db::name('tgw_tb')->join('user_tb','tgw_tb.t_u_id = user_tb.u_id','LEFT')->where('t_u_id',session('usid'))->select();
		$data = [
			'nowday'        => $nowday, //今日付款数
			'nowmonth'      => $nowmonth, //本月订单数
			'nowdayvalue'   => $nowdayvalue,//今日预估
			'yesdayvalue'   => $yesdayvalue,//昨日预估
			'nowmonthvalue' => $nowmonthvalue,//上月预估
			'yesmonthvalue' => $yesmonthvalue,//本月预估
			'click'         => $click[0]['u_click'],//点击数
			'level'         => session('level'),
			'list'=>$zz,//Add By Jane 2017-5-9
			't'=>$t,
		];
		//print_r($zz);exit;
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
	
	/**
	 * Index::order_lists()
	 * 订单列表
	 * @return
	 */
	public function order_lists()
	{
        $type   =   input('type');
        $uid    =   input('uid');

        if(Session('usid') != $uid){
            alert('非法用户', url('index'));
        }
        
        $date   =   date('Y-m-d');
        switch($type){
            case 'today':
                $start_date =   strtotime($date); ##当天0点
                $end_date   =   '';
                break;
            case 'yestorday':
                $start_date =   strtotime(date('Y-m-d', strtotime('-1 day'))); ##前一天0点
                $end_date   =   strtotime($date) - 1; ##前一天23：59：59
                break;
            case 'month':
                $start_date =   strtotime(date('Y-m')); ##当月1号0点
                $end_date   =   '';
                break;
            case 'lastmonth':
                $month_first=   strtotime(date('Y-m-01')); ##本月第一天时间戳
                $start_date =   strtotime(date('Y-m-01', strtotime('-1 month', $month_first))); ##上月1号0点
                $end_date   =   strtotime(date('Y-m')) -1; ##上月最后一天 23：59：59
                break;
            default:
                 alert('类型非法', url('index'));
        }
        
        ##拼接sql条件
        if($start_date && $end_date){
            $where['a.o_creattime'] =   ['between', [$start_date, $end_date]];
        }else{
            $where['a.o_creattime'] =   ['>=', $start_date];
        }
        $where['a.o_u_id']  =   $uid;
        ###拼接结束##
        //print_r($where);exit;
        $tb     =   tb($start_date);
        $ortb   =   ortb($start_date);
        $list   =   Db::name($tb)->alias('a')->join($ortb.' b','a.o_ordernum = b.or_o_ordernum and a.o_u_id=b.or_u_id', "LEFT")->order('a.o_creattime desc')->where($where)->paginate(5);
    	$data = [
    		'list'    => $list,
    		'uid'     => $uid,
            'type'    => $type,
            'action'  => 'order_lists'
    		];
        /*
        
		$day = time();
		$day1 = strtotime(date("Y-m-d"));
		$ortb = ortb(time());
		$id = input('param.uid');
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));
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
		];*/
		$this->assign($data);
		return $this->fetch('lists');
	}
	//上月自己
	public function sylists()
	{
		$id = input('param.uid');
		$ortb = prevortb(time());
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));
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
	/**
	 * Index::listss()
	 * 展示下级所有代理订单信息
	 * @return
	 */
	public function show_agent_order()
	{
        $type   =   input('type');
        $uid    =   session('usid');
        
        $date   =   date('Y-m-d');
        switch($type){
            case 'today':
                $start_date =   strtotime($date); ##当天0点
                $end_date   =   '';
                break;
            case 'yestorday':
                $start_date =   strtotime(date('Y-m-d', strtotime('-1 day'))); ##前一天0点
                $end_date   =   strtotime($date) - 1; ##前一天23：59：59
                break;
            case 'month':
                $start_date =   strtotime(date('Y-m')); ##当月1号0点
                $end_date   =   '';
                break;
            case 'lastmonth':
                $month_first=   strtotime(date('Y-m-01')); ##本月第一天时间戳
                $start_date =   strtotime(date('Y-m-01', strtotime('-1 month', $month_first))); ##上月1号0点
                $end_date   =   strtotime(date('Y-m')) -1; ##上月最后一天 23：59：59
                break;
            default:
                 alert('类型非法', url('index'));
        }
        
        $childAgents    =   getUserChildAgents($uid);
        if($childAgents){
            $agentIds   =   array();
            foreach($childAgents as $val){
                $agentIds   =   array_merge($agentIds, $val);
            }
            unset($childAgents);
            
            ##拼接sql条件
            if($start_date && $end_date){
                $where['a.o_creattime'] =   ['between', [$start_date, $end_date]];
            }else{
                $where['a.o_creattime'] =   ['>=', $start_date];
            }
            $where['a.o_u_id']  =   ['in', $agentIds];
            ###拼接结束##
            $tb     =   tb($start_date);
            $ortb   =   ortb($start_date);
            $list   =   Db::name($tb)->alias('a')->join($ortb.' b','a.o_ordernum = b.or_o_ordernum and b.or_u_id='.$uid, "LEFT")->where($where)->order('or_o_creattime desc')->paginate(10);
        }else{
            $list   =   array();
        }
        
    	$data = [
    		'list'    => $list,
    		'uid'     => $uid,
            'type'    => $type,
            'action'  => 'show_agent_order'
    		];
        $this->assign($data);
		return $this->fetch('lists');
       
       
		$day = time();
		$day1 = strtotime(date("Y-m-d"));
		$ortb = ortb(time());
		$id = input('param.uid');
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));
		}
		$type = input('param.utype');

		$tb = tb();
		$u = Db::name('user_tb')->where(['u_id'=>$id])->find();
		if($u['u_leve'] == 3){
			alert('你没有下级代理',url('index'));
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
			alert('不要乱搞，我跟你讲',url('index'));
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
			alert('你没有下级',url('index'));
		}else{
			alert('不可看',url('index'));
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
			alert('不要乱搞，我跟你讲',url('index'));
		}
		$user = Db::name('user_tb')->where(['u_id'=>$uid])->find();
		$u = Db::name('user_tb')->where('u_id',$user['u_u_idss'])->find();
		if(!$user['u_u_idss']){
			alert('管理员不能提现',url('index/my',['uid'=>$uid]));
		}
        
        if($user['u_state'] != 1){
            alert('用户禁止提现',url('index/my',['uid'=>$uid]));exit;
        }
        
        $data = [
					'username' => $user['u_username'],
					'uid' => $user['u_id'],
				];
        $money  =   getUserTxBalance($uid, $user['u_u_idss'], $user, $u);
        $data['money']  =   $money;
		
        $data['taokeInfo'] = $u;
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
			alert('不要乱搞，我跟你讲',url('index'));
		}
		$user = Db::name('user_tb')->where(['u_id'=>$uid])->find();
		$u = Db::name('user_tb')->where('u_id',$user['u_u_idss'])->find();
        
        if($user['u_state'] != 1){
            alert('用户禁止提现',url('index/my',['uid'=>$uid]));exit;
        }
		
        $money  =   getUserTxBalance($uid, $user['u_u_idss'], $user, $u); ##获取可提现余额
        
		if($txmoney > $money){
			alert('你没那么多钱',url('index/money',['uid'=>$uid]));
		}else{
			$id = Db::name('user_tb')->where('u_username',$username)->find();
			$min = Db::name('tkfcbl_tb')->where('fc_u_idss',$id['u_u_idss'])->find();
			if($txmoney < $min['fc_minmoney']){
				alert('提现金额不能小于'.$min['fc_minmoney'],url('index/my',['uid'=>$uid]));
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
					alert('已提交给管理员，请等待',url('index/my',['uid'=>$uid]));
				}
			}

		}
	}
	//邀请码
	public function yqm()
	{
		$id = input('param.uid');
		if($id != session('usid')){
			alert('不要乱搞，我跟你讲',url('index'));
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
			alert('不要乱搞，我跟你讲',url('index'));
		}
		$user = Db::name('user_tb')->where('u_id',$id)->find();
        $parentUser =   getUserInfo($user['u_u_idss']);
		$data = ['user' => $user,'parentUser'=>$parentUser];
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
			alert('不要乱搞，我跟你讲',url('index'));
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
			alert('不要乱搞，我跟你讲',url('index'));
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
			alert('请输入订单号',url('index/index'));
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
				alert('未查询到任何订单！',url('index/index'));
			}
		}
	}
	//找品
	public function searchp(){
		//echo 222;exit;
		header("Content-type: text/html; charset=utf-8");
		$name = trim(input('get.name'));
		//$p = trim(input('get.p'));
		$p = isset($_GET['p'])?$_GET['p']:0;
		$type = isset($_GET['type'])?$_GET['type']:'';
		$order = isset($_GET['order'])?$_GET['order']:'desc';
		if($p <= 1){
			$p = 1;
		}
		//try
		//{
			// $z = file_get_contents('http://www.xccloud.xin/index.php?m=Api&keyword='.$name.'&p='.$p);
			//$z = file_get_contents('http://www.t5166.com/index.php?m=Api&keyword='.$name.'&p='.$p);
		//	$z = file_get_contents("http://so.00o.cn/index.php?keyword=".$name."&p=".$p."&type=".$type."&order=".$order);

		//}
		//catch(\Exception $e)
		//{
		//	alert('网络不稳定,请稍候重试!',url('index/index'));
		//}
		//$url = "http://so.00o.cn/index.php?keyword=".$name."&p=".$p."&type=".$type."&order=".$order;
		$url = "http://so.00o.cn/index.php";
		$param  = array(
			'keyword'=>$name,
			'p'=>$p,
			'type'=>$type,
			'order'=>$order
		);
		$z = request_post($url,$param);
		if(!$z){
			$zz = array();
		}else{
			$zz = json_decode($z,true);
		}

		//echo '<pre>';
		//print_r($zz);exit;
		if(empty($zz)){
			alert('没有数据',url('index/index'));
			//alert('1212',url('index/index'));
		}
		$t = Db::name('tgw_tb')->join('user_tb','tgw_tb.t_u_id = user_tb.u_id','LEFT')->where('t_u_id',session('usid'))->select();
		$u = Db::name('user_tb')->where('u_id',session('usid'))->find();
		$uu = Db::name('user_tb')->where('u_id',$u['u_u_idss'])->find();
		if(!$t){
			alert('管理员没有给你设置PID',url('index/index'));
		}else{
			if ($uu['u_dlzp'] != 1) {
				return $this->fetch('searchss');
			}else{
				//echo '<pre>';
				//print_r($zz);exit;
				$data = [
					'list'=>$zz,
					't'=>$t,
					'len'=>count($t),
					'idss'=>$t[0]['u_u_idss'],
					'uid'=>$t[0]['t_u_id'],
					'name'=>$name,
					'zpzh'=>$uu['u_zpzh'],
					'dlzp'=>$uu['u_dlzp'],
					'type'=>$type,
					'order'=>$order,
					'p'=>$p
				];

				$this->assign($data);
				return $this->fetch();
			}
		}
	}
	//找品下拉
	public function loadss_bak(){
		$name = input('post.name');
		$p = input('post.p');
		$pp = $p+1;
		$data = [
			'datas'=> cjapi($name,$pp),
			'page' => $p+1,
		];
		return json($data);
	}
	public function loadss(){
		$name = input('post.name');
		$p = input('post.p');
		$type = input('post.type');
		$order = input('post.order');
		$pp = $p+1;
		//当前的排序方法
		//$z = file_get_contents("http://so.00o.cn/index.php?keyword=".$name."&p=".$pp."&type=".$type);
		//$z = file_get_contents("http://so.00o.cn/index.php?keyword=".$name."&p=".$pp."&type=".$type."&order=".$order);
		//$url = "http://so.00o.cn/index.php?keyword=".$name."&p=".$pp."&type=".$type."&order=".$order;
		//$z = request_get($url);
		$url = "http://so.00o.cn/index.php";
		$param = array(
			"keyword"=>$name,
			"p"=>$pp,
			"type"=>$type,
			"order"=>$order
		);
		$z = request_post($url,$param);
		if(!$z){
			$datas = array();
		}else{
			$datas = json_decode($z,true);
		}
		$data = array(
			'datas'=>$datas,
			'page'=>$pp
		);
		exit(json_encode($data));
	}

	//生成文案
	public function tj(){
		$post = $_POST;
		$tkid = isset($_POST['tkid'])?$post['tkid']:0;
		if($tkid == 0 || empty($tkid)){
			alert('参数错误');exit;//2017-6-6 修改ajax返回数据
			//echo "<script>alert('参数错误');</script>";exit;
			//exit(array('status'=>'2009','msg'=>'参数有误'));
		}
		//$tkid = $post['tkid'];
		/*if(!$tkid){
			alert('参数错误');
		}*/
		//$vv = sc($tkid); //Jane update 2017-4-21
		//$vv = sc($post['jihua_type'],$post['coupon_id']);

		if(!isset($post['jihua_type'])||empty($post['jihua_type'])||is_null($post['jihua_type'])){
			$post['jihua_type'] = '';
		}
		$coupon_id = input('post.coupon_id');
		if(!isset($coupon_id)||empty($coupon_id)||is_null($coupon_id)){
			//alert('优惠券有误');exit;//2017-6-6 修改ajax返回数据
			echo "<script>alert('优惠券有误');</script>";exit;
		}
		//$vv = sc($post['coupon_id'],$post['jihua_type']);
		$vv = sc($coupon_id,$post['jihua_type']);
		$logo = $post['imgurl'];
		$title = $post['gname'];
		//pid   mm_321342432_43243432_432432432
		//$url_uland="http://uland.taobao.com/coupon/edetail?activityId=".$vv['vid']."&pid=".$post['pid']."&itemId=".$post['gid']."&src=mili_etuia&dx=".$vv['dx'];
		//获取高佣接口数据中的coupon_click_url&activityId=".$vv['vid']
		$pid = $post['pid'];
		$pid_arr = explode('_',$pid);
		$memberid = $pid_arr[1];
		$mem_param = array(
			'memberid'=>$memberid,
			'username'=>'jane妞',
			'password'=>'271236'
		);
		$member_data = request_post("http://api.00o.cn/user.php",$mem_param);
		$res= json_decode($member_data,true);
		if($res['code'] == '2009'){
			alert('联系管理员授权登录');exit;//2017-6-6 修改ajax返回数据
			//echo "<script>alert('联系管理员授权登录');</script>";exit;
		}
		$token = $res['data']['token'];
		//alert($token);exit;
		//print_r($token);
		$gy_param = array(
			'token'=>$token,
			'item_id'=>$post['gid'],
			'adzone_id'=>$pid_arr[3],
			'platform'=>1,
			'site_id'=>$pid_arr[2]
		);
		//alert($gy_param);exit;
		$gy_data = request_post("http://tbapi.00o.cn/highapi.php",$gy_param);
		$gy_data = json_decode($gy_data,true);
		//alert($gy_data);exit;
		try
		{
			if(isset($gy_data['result'])){
				$coupon_click_url = $gy_data['result']['data']['coupon_click_url'];
			}else{
				alert('请联系管理员刷新token OR 查看PID');exit;
			}
		}
		catch(\Exception $e)
		{
			//alert('网络不稳定,请稍候重试!',url('index/index'));
			alert('请联系管理员刷新token');exit;//2017-6-6 修改ajax返回数据
			//echo "<script>alert('请联系管理员刷新token');</script>";exit;
		}

		//alert($coupon_click_url);exit;
		/*if(empty($coupon_click_url)){
			alert('联系管理员修复');exit;
		}*/
		$url_uland = $coupon_click_url."&activityId=".$vv['vid'];
		//$url_uland = $gy_data
		$tkl_post['url'] = urlencode($url_uland);
		$tkl_post['logo'] = $logo;
		$tkl_post['title'] = $title;
		$tkl = request_post('http://kl.00o.cn/index.php',$tkl_post);

		//$resp = $c->execute($req);
		//$url="http://uland.taobao.com/coupon/edetail?activityId=".$vv['vid']."&pid=".$post['pid']."&itemId=".$post['gid']."&src=mili_etuia&dx=".$vv['dx'];
		//$surl = request_post('http://00o.cn/api.php',array('smallurl'=>$url));
		$surl = request_post('http://00o.cn/api.php',array('smallurl'=>$url_uland));
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
		$jjj = strip_tags($jjj);
		$data = [
			'list' => $post,
			'jj'   => $jjj,
			'tkl'  => $tkl,
			'surl' => $surl
		];
		/*$add['h_goodsid'] = $post['gid'];
		$add['h_alimamaid'] = $post['mm1'];
		$add['h_time'] = strtotime(date('Y-m-d',time()));
		$aa = Db::name('high_tb')->where($add)->find();
		if(!$aa){
			Db::name('high_tb')->insert($add);
		}*/
		$this->assign($data);
		return $this->fetch();
	}
	//判断所传数据是否有误
	public function tjCheck(){
		$tkid = input('post.tkid');
		if($tkid == 0 || empty($tkid)){
			exit(json_encode(array('status'=>'2009','msg'=>'参数有误')));
		}
		$coupon_id = input('post.coupon_id');
		if(!isset($coupon_id)||empty($coupon_id)||is_null($coupon_id)){
			exit(json_encode(array('status'=>'2009','msg'=>'优惠券有误')));
		}

		$pid = input('post.pid');//exit(json_encode(array('status'=>'2009','msg'=>$pid)));
		if(empty($pid)){
			exit(json_encode(array('status'=>'2009','msg'=>'联系管理员查看PID')));
		}

		$pid_arr = explode('_',$pid);
		if(is_array($pid_arr)){
			$memberid = $pid_arr[1];
		}else{
			exit(json_encode(array('status'=>'2009','msg'=>'联系管理员查看PID')));
		}
		$mem_param = array(
			'memberid'=>$memberid,
			'username'=>'jane妞',
			'password'=>'271236'
		);
		$member_data = request_post("http://api.00o.cn/user.php",$mem_param);
		$res= json_decode($member_data,true);
		if($res['code'] == '2009'){
			//alert('联系管理员授权登录');exit;//2017-6-6 修改ajax返回数据
			exit(json_encode(array('status'=>'2009','msg'=>'联系管理员授权登录')));
		}
		$token = $res['data']['token'];
		//alert($token);exit;
		//print_r($token);
		$item_id = input('post.gid');
		$gy_param = array(
			'token'=>$token,
			'item_id'=>$item_id,
			'adzone_id'=>$pid_arr[3],
			'platform'=>1,
			'site_id'=>$pid_arr[2]
		);
		//alert($gy_param);exit;
		$gy_data = request_post("http://tbapi.00o.cn/highapi.php",$gy_param);
		$gy_data = json_decode($gy_data,true);
		//alert($gy_data);exit;
		try
		{
			$coupon_click_url = $gy_data['result']['data']['coupon_click_url'];
		}
		catch(\Exception $e)
		{
			//alert('请联系管理员刷新token');exit;//2017-6-6 修改ajax返回数据
			exit(json_encode(array('status'=>'2009','msg'=>'请联系管理员刷新token')));
		}
		exit(json_encode(array('status'=>'200','msg'=>'success')));
	}
	public function number(){
		return $this->fetch();
	}
	public function numberber(){
		return $this->fetch();
	}
	public function numberp(){
		return $this->fetch();
	}
	public function numblast(){
		return $this->fetch();
	}
	public function sanji(){
		return $this->fetch();
	}

	public function ceshi(){
		return $this->fetch();
	}
    
    /**
     * Index::money_record()
     * 提现记录
     * @return void
     */
    function money_record(){
        $uid    =   session('usid');
        $uname  =   session('uname');
        $where  =   array('m_username'=>$uname);
        $states =  array(0=>'申请中', 1=>'提现成功', 2=>'拒绝提现');
        
        $res    =   Db::table('money_tb')->field('m_alipayname,m_alipayaccount,m_money,m_state,m_time')->where($where)->order('m_id', 'desc')->limit(20)->select();
        $data   =   array('res'=>$res, 'uname'=>$uname,'states'=>$states);
        $this->assign($data);
        return $this->fetch();
    }

}

