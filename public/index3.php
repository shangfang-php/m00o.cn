<?php
header("Content-type:text/html;charset=utf-8");
$text = $_GET['m'];
$p = $_GET['p'];
if(!$p)
{
	$pages = 1;
}
else
{
	$pages=$p;
}
$z = goods_list($text,$pages);
$page = page($z);
if($page==2003)
{
	echo '没有数据！';exit;
}
$l = lists($z);
$arr = array('code'=>1002,'res'=>'获取成功','page'=>$page,'data'=>$l);
$sqls = json_encode($arr);
$sqlss = str_replace(":null",":\"\"",$sqls);
echo $sqlss;
function goods_list($text,$pages)
{
	$cookie_file='';

	$ch =curl_init();
	curl_setopt($ch,CURLOPT_URL,'http://www.dataoke.com/search/?keywords='.$text.'&xuan=keyword&page='.$pages);
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
	curl_setopt($ch,CURLOPT_URL,'http://www.dataoke.com/search/?keywords='.$text.'&xuan=keyword&page='.$pages);    
	$header = array();  
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);  
	curl_setopt($ch,CURLOPT_HEADER,true);  
	curl_setopt($ch,CURLOPT_HTTPHEADER,$header);  
	curl_setopt($ch,CURLOPT_COOKIE,$cookie_file);  
	  
  
	$zz = curl_exec($ch);  

	$zz=str_replace('\r','',$zz );
	$zz=str_replace( '\n' ,'',$zz);
	$zz=str_replace( '\t','',$zz);
	$zz=preg_replace( '/\s/','',$zz);    
	return $zz;
}
function page($zz)
{
	$l = preg_match_all('/<divclass=\"page_total\">(.*?)<\/div>/',$zz,$num);
	print_r($zz);
	if($l==0)
	{
		return 2003;exit;
	}
	$nums = $num[1][0];
	$nums=str_replace('共<b>','',$nums );
	$nums=str_replace('</b>条','',$nums );

	if($nums/100>1)
	{
		$page = ceil($nums/100);
	}
	else{
		$page = 1;
	}
	
	return $page;
}
function lists($zz)
{
	$regexstr='/goods-items_(?P<dtk_goodsid>\d{5,10})\"(.*?)data_goodsid=\"(?P<tb_goodsid>\d{10,20})\"';
	$regexstr.='(.*?)data-original=\"(?P<dtk_goodsimg>(.*?))\"';
	$regexstr.='(.*?)target=\"_blank\">(?P<dtk_goodsname>(.*?))<\/a>';
	//$regexstr.='(.*?)<\/span><p><b><iclass=\"rmb-style\">￥<\/i>(?P<dtk_qhj>(.*?))<\/b>';
	//$regexstr.='(.*?)<b><iclass=\"rmb-style\">￥<\/i>(?P<dtk_qj>(.*?))<\/b>';
	//$regexstr.='(.*?)<span>(?P<dtk_laiy>(.*?))<\/span>';
	//$regexstr.='(.*?)<spanclass=\"margin-num\">(?P<dtk_yhq>(.*?))<\/span>';
	$regexstr.='/';
	preg_match_all($regexstr, $zz, $match);
	$num = 0;
	$arr = array();
	foreach($match['dtk_goodsid'] as $key=>$value) {
		$arr[$num]['dtk_goodsid'] = $match['dtk_goodsid'][$num];
		$arr[$num]['tb_goodsid'] = $match['tb_goodsid'][$num];
		$arr[$num]['dtk_goodsimg'] = $match['dtk_goodsimg'][$num];
		$arr[$num]['dtk_goodsname'] = $match['dtk_goodsname'][$num];
		//$arr[$num]['dtk_qhj'] = $match['dtk_qhj'][$num];
		//$arr[$num]['dtk_qj'] = $match['dtk_qj'][$num];
		//$arr[$num]['dtk_zj'] = $match['dtk_qj'][$num] + $match['dtk_qhj'][$num];
		//$yhq=str_replace('优惠券剩余：','',$match['dtk_yhq'][$num] );
		//$yhq=str_replace('<b>','',$yhq );
		//$yhq=str_replace('</b>','',$yhq );
		//$arr[$num]['dtk_yhq'] = $yhq;
		//$laiy=str_replace('></i>','',$match['dtk_laiy'][$num]);
		//$laiy=str_replace('"','',$laiy);
		//$laiy=str_replace('tag-qiang','',$laiy);
		//$laiy=str_replace('tag-trans','',$laiy);
		//$laiy=str_replace('tag-gold','',$laiy);
		//$laiy=str_replace('tag-haitao','',$laiy);
		//$laiy=str_replace('tag-ju','',$laiy);
		//$laiy=str_replace('tag-','',$laiy);
		//$laiy=str_replace('<iclass=','',$laiy);
		//$arr[$num]['dtk_laiy'] = $laiy;
		$num++;
	}
	return $arr;
}
?>