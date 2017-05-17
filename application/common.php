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
function tb(){
	$year = date('Y',time());
	$month = date('m',time());
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
	$z = file_get_contents('http://www.xccloud.xin/index.php?m=Api&keyword='.$name.'&p='.$i);
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        
        return $data;
    }

