<?php
use think\Db;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function jsons($jsondata2)
{
    $sqls = json_encode($jsondata2);
    $sqlss = str_replace(":null",":\"\"",$sqls);
    $sqlss = str_replace(":false",":\"\"",$sqlss);
    $sqlss = str_replace(": false",":\"\"",$sqlss);
    echo $sqlss;exit;
}

function getcode(){
    return sprintf('%x',crc32(microtime()));
}

function iscode($code)
{
    $codearr = Db::name('user_tb')->where(array('u_code'=>$code))->find();
    if(!$codearr)
    {
        return $code;exit;
    }else{
        $getcode = getcode();
        iscode($getcode);exit;
    }
}

function savesafe($uid)
{
	$safesave['s_ret'] = 1;
	Db::name('safe_tb')->where(array('s_u_id'=>$uid))->update($safesave);
}

function alert($msg,$url=''){
	echo "<script charset='UTF-8'>";
	echo "alert('$msg');";
	if($url){
		echo "window.location.href='$url';";
	}else{
		echo "window.history.go(-1);";
	}
	echo "</script>";
}
function tb($time = 0){
	//$year = date('Y',time());
//	$month = date('m',time());
    $time   =   $time ? $time : time();
    list($year, $month) =   explode('-', date('Y-m', $time));
	return 'order_'.$year.'_'.$month.'_tb';
}
function prevtb(){
	$year = date('Y',time());
	$month = date('m',time());
	if($month == '01'){
		$years = $year - 1;
		return 'order_'.$years.'_12_tb';
	}else{
		$m = $month-1;
		return 'order_'.$year.'_0'.$m.'_tb';
	}
}
function subtext($text, $length)
	{
		if(mb_strlen($text, 'utf8') > $length) 
		return mb_substr($text, 0, $length, 'utf8').'...';
		return $text;
	}
function subtext1($text, $length)
{
	if(mb_strlen($text, 'utf8') > $length) 
	return mb_substr($text, 0, $length, 'utf8').'';
	return $text;
}
function subb($re){
	$ar = strpos($re,'_');
	if($ar == false){
		$rr = $re;
	}else{
		$rr = strstr($re,'_',true);
	}
	return $rr;
}

//Jane备份2017-4-21 12:41
function sc_bak($tkid){
	//$zz = file_get_contents('http://www.dataoke.com/item?id='.$tkid);
	$cookie_file='';
	$ch =curl_init();
	curl_setopt($ch,CURLOPT_URL,'http://www.dataoke.com/item?id='.$tkid);
	$header = array();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HEADER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
	curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
	$content = curl_exec($ch);
	$url2=strstr( $content, 'Access-Control-Allow-Origin',true);
	$url2=strstr( $url2, 'Location:');
	//$url2 = str_replace(array("\r\n", "\r", "\n"," ","Location:"), "", $url2);  
	$url2='http://www.dataoke.com/item?id='.$tkid;
	$ch =curl_init();
	curl_setopt($ch,CURLOPT_URL,$url2);
	$header = array();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HEADER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
	curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
	$content = curl_exec($ch);
	$cookie_file=strstr( $content, 'token=');
	$cookie_file=strstr( $cookie_file, 'GMT',true).'GMT;';
	$cookie2=strstr( $content, 'random=');
	$cookie2=strstr( $cookie2, 'Access-Control-Allow-Origin',true);
	$cookie2 = str_replace(array("\r\n", "\r", "\n"), "", $cookie2);   
	$cookie_file.=$cookie2.';';
	curl_setopt($ch,CURLOPT_URL,'http://www.dataoke.com/item?id='.$tkid);    
	$header = array();  
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);  
	curl_setopt($ch,CURLOPT_HEADER,true);  
	curl_setopt($ch,CURLOPT_HTTPHEADER,$header);  
	curl_setopt($ch,CURLOPT_COOKIE,$cookie_file);
	$zz = curl_exec($ch); 
	/*try
		{
			$zz = file_get_contents('http://www.dataoke.com/item?id='.$tkid);
		}
		catch(\Exception $e)
		{
			alert('网络不稳定,请稍候重试!',url('index/index'));
		}*/
	$zz=str_replace('\r','',$zz );
	$zz=str_replace( '\n' ,'',$zz);
	$zz=str_replace( '\t','',$zz);
	$zz=preg_replace( '/\s/','',$zz);
	$regexstr='/<spanclass=\"yong-trans-attention\"><\/span>(?P<dtk_dx>(.*?))<';
	$regexstr.='(.*?)activity_id=(?P<dtk_dxx>(.*?))\"';
	$regexstr.='/';
	$pd =  preg_match_all($regexstr, $zz, $match);
	$arr = array();
	if($pd==1)
	{
		if($match['dtk_dx'][0]=="通用计划")
		{
			$a = "";
		}
		else if($match['dtk_dx'][0] == "定向秒过")
		{
			$a =1;
		}
		else
		{
			$a = "";
		}
		$arr['dx']  = $a;
		$arr['vid'] =  $match['dtk_dxx'][0];
	}
	else if($pd==0)
	{    
	    
		$regexstr='/(.*?)b><\/span><p>(?P<dtk_qq>(.*?))<';
		$regexstr.='(.*?)activity_id=(?P<dtk_dxx>(.*?))\"';
		$regexstr.='/';
		preg_match_all($regexstr, $zz, $match);
        
		###暂时注释，反正到最后都等于空。。。
	    /*if(isset($match['dtk_qq'][0]) && $match['dtk_qq'][0] == "鹊桥")
		{
			$a = "";
		}
		else
		{
			$a = "";
		}*/
        $a = '';

		$arr['dx'] =  $a;
		$arr['vid'] =  $match['dtk_dxx'][0];
	}

	return $arr;	
}
function sc($coupon_id,$jihua_type){
	$arr = array();
	$arr['vid'] = $coupon_id;
	//$res = preg_match('/^定向/',$jihua_type);
	if($jihua_type == ' '||$jihua_type == 'jh'||$jihua_type == null|| $jihua_type == 'null'){
		$arr['dx'] =  '1';
	}else{
		$arr['dx'] = ' ';
	}
	return $arr;
}
function wa($gid){
	/*$zz = file_get_contents('http://www.dataoke.com/gettpl?gid='.$gid);*/
	$cookie_file='';
	$ch =curl_init();
	curl_setopt($ch,CURLOPT_URL,'http://www.dataoke.com/gettpl?gid='.$gid);
	$header = array();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HEADER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
	curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
	$content = curl_exec($ch);
	$url2=strstr( $content, 'Access-Control-Allow-Origin',true);
	$url2=strstr( $url2, 'Location:');
	$url2 = str_replace(array("\r\n", "\r", "\n"," ","Location:"), "", $url2);  
	$ch =curl_init();
	curl_setopt($ch,CURLOPT_URL,$url2);
	$header = array();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HEADER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
	curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
	$content = curl_exec($ch);
	$cookie_file=strstr( $content, 'token=');
	$cookie_file=strstr( $cookie_file, 'GMT',true).'GMT;';
	$cookie2=strstr( $content, 'random=');
	$cookie2=strstr( $cookie2, 'Access-Control-Allow-Origin',true);
	$cookie2 = str_replace(array("\r\n", "\r", "\n"), "", $cookie2);   
	$cookie_file.=$cookie2.';';
	curl_setopt($ch,CURLOPT_URL,'http://www.dataoke.com/gettpl?gid='.$gid);    
	$header = array();  
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);  
	curl_setopt($ch,CURLOPT_HEADER,true);  
	curl_setopt($ch,CURLOPT_HTTPHEADER,$header);  
	curl_setopt($ch,CURLOPT_COOKIE,$cookie_file);
	$zz = curl_exec($ch); 
		/*try
		{
			$zz = file_get_contents('http://www.dataoke.com/gettpl?gid='.$gid);
		}
		catch(\Exception $e)
		{
			alert('网络不稳定,请稍候重试!',url('index/index'));
		}*/
	//dump($gid);exit;
	$zz=str_replace('\r','',$zz );
	$zz=str_replace( '\n' ,'',$zz);
	$zz=str_replace( '\t','',$zz);
	$zz=preg_replace( '/\s/','',$zz);
	$regexstr='/\?id=\d{10,15}<\/a><\/br>(?P<dtk_wa>(.*?))$';
	$regexstr.='/';
	preg_match_all($regexstr, $zz, $match);
	return $match['dtk_wa'];
}

function cjapi($name,$i){
	//$z = file_get_contents('http://www.xccloud.xin/index.php?m=Api&keyword='.$name.'&p='.$i);
	$z = file_get_contents("http://so.00o.cn/index.php?keyword=".$name."&p=".$i);
	$zz = json_decode($z,true);
	return $zz;
}
function ortb($time){
	$y = date('y',$time);
	$m = date('m',$time);
	$mm = $m + 0;
	if($y <= 17 && $m < 4){
		return 'order_record_tb';
	}else{
		return 'order_record_'.$y.'_'.$m.'_tb';
	}
}

function prevortb($time){
	$y = date('y',$time);
	$m = date('m',$time);
	if($y <= 17 && $m < 4){
		return 'order_record_tb';
	}else if($m == '01'){
		$years = $year - 1;
		return 'order_record_'.$y.'_12_tb';
	}else{
		$mm = $m-1;
		if($y <= 17 && $mm < 4){
			return 'order_record_tb';
		}else{
			return 'order_record_'.$y.'_0'.$mm.'_tb';
		}
		
	}
}
function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
		curl_setopt($ch, CURLOPT_TIMEOUT,30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        
        return $data;
    }
function request_get($url) {
	if (empty($url)) {
		return false;
	}
	//初始化
	$ch = curl_init();
	//设置选项，包括URL
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$output = curl_exec($ch);
	//释放curl句柄
	curl_close($ch);
	return $output;
    }

/**
 * getUserInfo()
 * 获取代理商信息
 * @param mixed $uid 
 * @param mixed $type 1 uid查询 2 username查询
 * @return void
 */
function getUserInfo($uid, $type = 1){
    $field  =   $type == 1 ? 'u_id' : 'u_username';
    $where[$field]  =   $uid;
    $res    =   Db::table('user_tb')->where($where)->find();
    //print_r($res);exit;
    return $res;
}

/**
 * get_field_array()
 * 获取二维数组中指定键对应值的数组集合
 * @param mixed $field 指定键名或数组
 * @param array $array 循环的二维数组
 * @return array 返回二维数组
 */
function get_field_array($field, $array){
    $return =   array();
    if(is_array($array)){
        if(is_array($field)){
            foreach($field as $v){
                $return[$v] =   array();
            }
        }
        
        foreach($array as $val){
            if(is_array($field)){
                foreach($field as $v){
                    $return[$v][]   =   isset($val[$v]) ? $val[$v] : '';
                }
            }else{
                $return[] = isset($val[$field]) ? $val[$field] : '';   
            }
        }
    }
    return array_filter($return); 
}

/**
 * reverse_array()
 * 从一个数组中拿出指定的字段作为键名，另外一个字段作为值生成数组
 * @param mixed $array
 * @param mixed $val_filter  作为值的字段
 * @param string $key_filter 作为键名的字段 为空则为key值
 * @param bool $isMulti 是否生成多维数组
 * @return void
 */
function reverse_array($array, $val_filter, $key_filter = '', $isMulti = FALSE){
    $return     =   array();
    if(empty($array)){
    	return $return; //增加拦截传递为空数组的情况
    }
    foreach($array as $key=>$val){
        $k  =   $key_filter ? $val[$key_filter] : $key;
        $v  =   $val_filter ? $val[$val_filter] : $val;
        if($isMulti){
            $return[$k][]   =   $v;
        }else{
            $return[$k]     =   $v;
        }
    }
    return array_filter($return);
}

/**
 * getUserTxBalance()
 * 获取用户可提现余额
 * @param mixed $uid 查询的代理商ID
 * @param mixed $u_idss 上级淘客ID
 * @param mixed $userInfo 代理商信息
 * @param mixed $u_idss_info 淘客信息
 * @return void
 */
function getUserTxBalance($uid, $u_idss = 0, $userInfo = 0, $u_idss_info = 0){
    if(!$userInfo){
        $userInfo   =   getUserInfo($uid);
    }
    
    !$u_idss && $u_idss = $userInfo['u_u_idss'];
    if(!$u_idss_info){
        $u_idss_info    =   getUserInfo($u_idss);
    }
    
    $userMoney  =   $userInfo['u_money']; ##代理商总余额
    
    if($u_idss_info['u_type'] == 1 || $u_idss_info['u_type'] == 0){
        $money  =   $userMoney;
	}else if($u_idss_info['u_type'] == 2){
		$d = date('d',time());
    	if($d < 21){
    		//alert('21号开始提现',url('index/my',array('uid'=>$user['u_id'])));
    		$m = date('Y-m',time());
    		$mm = date('Y-m',strtotime("last month"));
    		//echo $m;exit;
    		$f = Db::name('income_tb')->where(array('i_y_m'=>$m,'i_uid'=>$uid,'i_idss'=>$u_idss))->find();
    		$ff = Db::name('income_tb')->where(array('i_y_m'=>$mm,'i_uid'=>$uid,'i_idss'=>$u_idss))->find();

    		$fm   =   empty($f) ? 0 : $f['i_money'];
    		$ffm  =   empty($ff) ? 0 : $ff['i_money'];
    
    		//$data['money'] = $user['u_money'] - $fm -$ffm;
    		$money    =   bcsub($userMoney, bcadd($fm, $ffm, 2), 2);
    
    	}else{
    		$m = date('Y-m',time());
    		$f = Db::name('income_tb')->where(array('i_y_m'=>$m,'i_uid'=>$uid,'i_idss'=>$u_idss))->find();
            $fmoney = empty($f) ? 0 : $f['i_money'];
    		$money  = bcsub($userMoney, $fmoney, 2);
    		//dump($data);exit;
    	}
	}
    
    $money < 0 && $money = 0;
    return $money;
}

/**
 * getStartAndEndTime()
 * 根据类型获取起止时间
 * @author Gary
 * @param mixed $type today今日 yestorday昨日 month本月 lastmonth上月
 * @return void array('startTime'=>1,'endTime'=>'')
 */
function getStartAndEndTime($type){
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
             alert('类型非法', url('index/index'));
    }
    return array('startTime'=>$start_date, 'endTime'=>$end_date);
}

/**
 * getTaokeFcblSet()
 * 获取淘客的默认分成比例设置
 * @param mixed $taokeId
 * @param integer $level
 * @return void
 */
function getTaokeFcblSet($taokeId, $level = 0){
    $return =   array('u_fcbl'=>0, 'u_fcbl2'=>0, 'u_fcbl3'=>0);
    if($level == 1){
        $field  =   'fc_one,fc_one2,fc_one3';
    }elseif($level == 2){
        $field  =   'fc_two,fc_two2';
    }elseif($level == 3){
        $field  =   'fc_three';
    }else{
        return $return;
    }
    $res    =   Db::table('tkfcbl_tb')->field($field)->where('fc_u_idss', $taokeId)->find();
    if($res){
        if($level == 1){
            $return['u_fcbl']   =   $res['fc_one'];
            $return['u_fcbl2']  =   $res['fc_one2'];
            $return['u_fcbl3']  =   $res['fc_one3'];
        }elseif($level == 2){
            $return['u_fcbl']   =   $res['fc_two'];
            $return['u_fcbl2']  =   $res['fc_two2'];
        }else{
            $return['u_fcbl']   =   $res['fc_three'];
        }
    }
    return $return;
}
