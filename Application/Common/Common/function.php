<?php
use Common\Lib\Crypt3Des;
use Common\Lib\AliyunMsn;
use Common\Lib\SavorRedis;

function getCombinationToString($val)
{
    // 保存上一个的值
    static $res = array();
    if(empty($res))
    {
        $res = $val;
    }else{
        // 临时数组保存结合的结果
        $list = array();
        foreach ($res as $k => $v) {
            foreach ($val as $key => $value) {
                $list[$k.'_'.$key] = $v.'_'.$value;
            }
        }
        $res = $list;
    }
    return $res;
}

function jd_union_api($params,$api,$method='get'){
    $redis  =  \Common\Lib\SavorRedis::getInstance();
    $redis->select(12);
    $cache_key = 'system_config';
    $res_config = $redis->get($cache_key);
    $all_config = json_decode($res_config,true);
    $jd_config = array();
    foreach ($all_config as $v){
        if($v['config_key']=='jd_union_smallapp'){
            $jd_config = json_decode($v['config_value'],true);
            break;
        }
    }
    $all_params = array();
    $all_params['app_key'] = $jd_config['app_key'];
    $all_params['method'] = $api;
    $all_params['param_json'] = json_encode($params);
    $all_params['sign_method'] = 'md5';
    $all_params['timestamp'] = date('Y-m-d H:i:s');
    $all_params['v'] = '1.0';
    ksort($all_params);
    $str = '';
    $appScret = $jd_config['app_secret'];
    foreach ($all_params as $k => $v) $str .= $k . $v;
    $sign = strtoupper(md5($appScret . $str . $appScret));
    $all_params['sign'] = $sign;

    $curl = new \Common\Lib\Curl();
    $api_url = 'https://router.jd.com/api';
    $res_data = array();
    if($method=='get'){
        $url = $api_url.'?'.http_build_query($all_params);
        $res = '';
        $curl::get($url,$res);
        if($res){
            $res = json_decode($res,true);
            foreach ($res as $v){
                if($v['code']){
                    $res_data = $v;
                }else{
                    $res_data = json_decode($v['result'],true);
                }
                break;

            }
        }
    }
    return $res_data;

}

function forscreen_serial($openid,$forscreen_id,$oss_addr=''){
    $md5_str = $openid.$forscreen_id;
    if(!empty($oss_addr)){
        $addr_info = parse_url($oss_addr);
        if(strpos($addr_info['path'],'/')===0){
            $path = substr($addr_info['path'],1);
        }else{
            $path = $addr_info['path'];
        }
        $md5_str.=$path;
    }
    $serial = md5($md5_str);
    return $serial;
}

function check_http(){
    $http_str = 'http://';
// 	return $http_str;//如判断出错，则直接手动调整
    if(isset($_SERVER['HTTP_USE_HTTPS']) && $_SERVER['HTTP_USE_HTTPS'] === 'yes'){
        $http_str = 'https://';
    }elseif(!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!== 'off'){
        $http_str = 'https://';
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
        $http_str = 'https://';
    }elseif(!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off'){
        $http_str = 'https://';
    }
    return $http_str;
}

function get_host_name(){
    $http = check_http();
    return $http.$_SERVER['HTTP_HOST'];
}

function get_filesize($url,$user='',$pw=''){
    ob_start();
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    if (!empty($user) && !empty($pw)){
        $headers = array('Authorization: Basic ' . base64_encode($user.':'.$pw));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $okay = curl_exec($ch);
    curl_close($ch);
    $head = ob_get_contents();
    ob_end_clean();
    $regex = '/Content-Length:\s([0-9].+?)\s/';
    $count = preg_match($regex, $head, $matches);
    if (isset($matches[1])){
        $size = $matches[1];
    }else{
        $size = '';
    }
    return $size;
}

function bonus_random($total,$num,$min,$max){
    $data = array();
    if ($min * $num > $total) {
        return array();
    }
    if($max*$num < $total){
        return array();
    }
    while ($num >= 1) {
        $num--;
        $kmix = max($min, $total - $num * $max);
        $kmax = min($max, $total - $num * $min);
        $kAvg = $total / ($num + 1);
        //获取最大值和最小值的距离之间的最小值
        $kDis = min($kAvg - $kmix, $kmax - $kAvg);
        //获取0到1之间的随机数与距离最小值相乘得出浮动区间，这使得浮动区间不会超出范围
        $r = ((float)(rand(1, 10000) / 10000) - 0.5) * $kDis * 2;
        $k = sprintf("%.2f", $kAvg + $r);
        $total -= $k;
        $data[] = $k;
    }
    shuffle($data);
    return $data;
}
/**
 * 发送主题消息
 * @param $message消息内容 酒楼ID或者array('酒楼ID')
 * @param $type 1.酒楼的基础信息、2.包间的基础信息、3.机顶盒的基础信息、4.电视的基础信息、5.音量开关、6.节目单、
 * 7.宣传片、8.A类广告、9.B类广告、10.C类广告、11.点播、12.推荐菜 、13.机顶盒apk、14.loading图、15.酒楼logo图
 * @return Ambigous <boolean, mixed>
 */
function sendTopicMessage($message,$type){
    return true;
    if(empty($message) || empty($type)){
        return false;
    }
    $all_type = array('1'=>'hotel','2'=>'room','3'=>'box','4'=>'tv','5'=>'volume','6'=>'programmenu',
        '7'=>'promotionalvideo','8'=>'adsa','9'=>'adsb','10'=>'adsc','11'=>'demand','12'=>'recommendation',
        '13'=>'apk','14'=>'loading','15'=>'logo');
    $accessId = C('OSS_ACCESS_ID');
    $accessKey= C('OSS_ACCESS_KEY');
    $endPoint = C('QUEUE_ENDPOINT');
    $topicName = C('TOPIC_NAME');

    $ali_msn = new AliyunMsn($accessId, $accessKey, $endPoint);
    $mir_time = getmicrotime();
    $serial_num = $mir_time*10000;
    if(!is_array($message)){
        $message = array($message);
    }
    $now_message = array();
    foreach ($message as $v){
        $now_message[] = array('hotel_id'=>"$v",'serial_num'=>"$serial_num");
    }
    $messageBody = base64_encode(json_encode($now_message));
    $messageTag = $all_type[$type];
    $res = $ali_msn->sendTopicMessage($topicName,$messageBody,$messageTag);
    return $res;
}
/**
 * 发送主题消息
 * @param $message消息内容 酒楼ID或者array('酒楼ID')
 * @param $type 1.酒楼的基础信息、2.包间的基础信息、3.机顶盒的基础信息、4.电视的基础信息、5.音量开关、6.节目单、
 * 7.宣传片、8.A类广告、9.B类广告、10.C类广告、11.点播、12.推荐菜 、13.机顶盒apk、14.loading图、15.酒楼logo图
 * @return Ambigous <boolean, mixed>
 */
function sendTopicMessage_back($message,$type){
    return true;
    if(empty($message) || empty($type)){
        return false;
    }
    $all_type = array('1'=>'hotel','2'=>'room','3'=>'box','4'=>'tv','5'=>'volume','6'=>'programmenu',
        '7'=>'promotionalvideo','8'=>'adsa','9'=>'adsb','10'=>'adsc','11'=>'demand','12'=>'recommendation',
        '13'=>'apk','14'=>'loading','15'=>'logo');
    $accessId = C('OSS_ACCESS_ID');
    $accessKey= C('OSS_ACCESS_KEY');
    $endPoint = C('QUEUE_ENDPOINT');
    $topicName = C('TOPIC_NAME');
    $call_back_url = 'http://'.C('SAVOR_API_URL').'/small/SendTopic/reSendTopicMessage';
    $ali_msn = new AliyunMsn($accessId, $accessKey, $endPoint);
    $mir_time = getmicrotime();
    $serial_num = $mir_time*10000;
    if(!is_array($message)){
        $message = array($message);
    }
    $now_message = array();
    foreach ($message as $v){
        $now_message[] = array('hotel_id'=>"$v",'serial_num'=>"$serial_num",'callback'=>"$call_back_url");
    }
    $messageBody = base64_encode(json_encode($now_message));
    $messageTag = $all_type[$type];
    $res = $ali_msn->sendTopicMessage($topicName,$messageBody,$messageTag);
    return $res;
}

function insert_sort($arr){
    $count = count($arr);
	for($i=1; $i<$count; $i++){
	    $tmp = $arr[$i];
		$j = $i - 1;
		if ($j>0){
		    while($arr[$j] > $tmp){
    			$arr[$j+1] = $arr[$j];  
    			$arr[$j] = $tmp;  
    			$j--;
		    }
		}
	}
	return $arr;  
}

function select_sort($arr){
	$count = count($arr);  
	for($i=0; $i<$count; $i++){
	    $k = $i;  
		for($j=$i+1; $j<$count; $j++){
		    if ($arr[$k] > $arr[$j])  $k = $j;  
			if ($k != $i){
			    $tmp = $arr[$i];
			    $arr[$i] = $arr[$k];
			    $arr[$k] = $tmp;
			}
		}
	}
	return $arr;
}

function bubble_sort($array){
    $count = count($array);   
	if ($count <= 0) return false;
	for($i=0; $i<$count; $i++){
	    for($j=$count-1; $j>$i; $j--){
	        if ($array[$j] < $array[$j-1]){
	            $tmp = $array[$j];
	            $array[$j] = $array[$j-1];
	            $array[$j-1] = $tmp;
	        }
	    }
	}
	return $array;   
}

function getmicrotime(){
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
}

function str_addslashes(&$_value){
    if (!empty($_value) && is_array($_value)){
        foreach($_value as $_key=>$_val){
            str_addslashes($_value[$_key]);
        }
    }elseif(!empty($_value)){
        $_value = addslashes($_value);
    }
	return true;
}

function isUserName($vStr){
	return preg_match('/^[_a-zA-Z][\w]{3,15}$/', $vStr);
}

function isPassword($vStr){
    return preg_match('/^[\w]{6,20}$/', $vStr);
}

function isTel($vStr){
	return preg_match('/^[\d\-]{6,20}$/', $vStr);
}

function isZipCode($vStr){
	return preg_match('/^[\d]{6}$/', $vStr);
}

function isProof($vStr){
	return preg_match('/^[\d]{8}\-0[\d]+\-[0-9a-fA-F]{6}$/', $vStr);
}

function isIDCard($vStr){
	$vCity = array(
		'11','12','13','14','15','21','22',
		'23','31','32','33','34','35','36',
		'37','41','42','43','44','45','46',
		'50','51','52','53','54','61','62',
		'63','64','65','71','81','82','91'
	);
	
	if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;
	if (!in_array(substr($vStr, 0, 2), $vCity)) return false;
	$vStr = preg_replace('/[xX]$/i', 'a', $vStr);
	$vLength = strlen($vStr);
	if ($vLength == 18){
		$vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
	}else{
		$vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
	}
	
	if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
	if ($vLength == 18){
		$vSum = 0;
		for ($i = 17 ; $i >= 0 ; $i--){
			$vSubStr = substr($vStr, 17 - $i, 1);
			$vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
		}
  		if($vSum % 11 != 1) return false;
  	}
  	return true;
}

function isCreditCard($card_num){
	if(is_numeric($card_num) && strlen($card_num) >8){
		return true;
	}else{
		return false;
	}
}

function isExpireDate($card_num){
	if(is_numeric($card_num) && strlen($card_num) == 4){
		$month = substr($card_num, 0, 2);
		if (intval($month) > 12 ){
			return false;
		}else{
			return true;
		}
	}else{
		return false;
	}
}

function isMobile($vStr) {
	return preg_match("/^13[0-9]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|16[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|19[0-9]{1}[0-9]{8}$/", $vStr);
}

function isQQ($vStr) {
    return preg_match("/^[1-9]{1}[0-9]{4,14}$/", $vStr);
}

function isSpace($vStr){
	return preg_match('/^[ ]$/', $vStr);
}

/**
 * 获得用户的ip
 *
 * @return ip
 */
function getClientIP(){
	if(isset($_SERVER)){
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
			$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}elseif(isset($_SERVER["HTTP_CLIENT_IP"])) {
			$realip = $_SERVER["HTTP_CLIENT_IP"];
		}else{
			$realip = $_SERVER["REMOTE_ADDR"];
		}
	}else{
		if(getenv("HTTP_X_FORWARDED_FOR")){
			$realip = getenv("HTTP_X_FORWARDED_FOR");
		}elseif(getenv("HTTP_CLIENT_IP")){
			$realip = getenv("HTTP_CLIENT_IP");
		}else{
			$realip = getenv("REMOTE_ADDR");
		}
    }
    return addslashes($realip);
}

/**
 * 根据周取时间段
 *
 * @param unknown_type $year 年
 * @param unknown_type $week 周
 * @return unknown
 * $date = "2008-12-29";
 * implode("|", getTimeMiddle(2009,01));
 */

function getTimeMiddle($year,$week){
		//得到一年新的开始
		$start_day = ($year-1)."-12-23";
		for($j=1;$j<=14;$j++){
			$tmp_time = strtotime($start_day)+86400*$j;
			$tmp_day = date("Y-m-d",$tmp_time);
			if(getWeek($tmp_day) == 1){
				$start_time = strtotime($tmp_day);
				break;
			}
		}
		for($i=0;$i<365;$i++){
			$tmp_time = $start_time+86400*$i;
			$tmp_day = date("Y-m-d",$tmp_time);
			if(getWyear($tmp_day) == $year && getWeek($tmp_day) == $week){
				return aweek($tmp_day, 1);
				break;
			}
		}
		return null;
}

/**
 * 按周 取年份
 *
 * @param unknown_type $date 日期 YYYY-MM-DD
 * @return unknown
 * 
 * $date = "2008-12-29";
 * echo getWyear($date)."年".getWeek($date)."周";
 */
function getWyear($date){
	$y = date("Y",strtotime($date));
	$m = date("m",strtotime($date));
	$w = date("W",strtotime($date));
	
	if($m=='12' && $w=='01'){
	    return ($y+1);
	}else{
	    return ($y);
	}
}

/**
 * 取周
 *
 * @param unknown_type $date 日期 YYYY-MM-DD
 * @return unknown
 */
function getWeek($date){
	$w = date("W",strtotime($date));
	return $w;
}

/**
 * 取得给定日期所在周的开始日期和结束日期
 * 
 * @param unknown_type $gdate $gdate 日期，默认为当天，格式：YYYY-MM-DD
 * @param unknown_type $first $first 一周以星期一还是星期天开始，0为星期天，1为星期一
 * @return unknown 返回：数组array("开始日期", "结束日期");
 * 
 * $date = "2008-12-29";
 * echo implode("|", aweek($date, 1));
 */
function aweek($gdate = "", $first = 0){
    if(!$gdate) $gdate = date("Y-m-d");
    $w = date("w", strtotime($gdate));//取得一周的第几天,星期天开始0-6
    $dn = $w ? $w - $first : 6;//要减去的天数
    $st = date("Y-m-d", strtotime("$gdate -".$dn." days"));
    $en = date("Y-m-d", strtotime("$st +6 days"));
    return array($st, $en);//返回开始和结束日期
}

// 取得某月天数,可用于任意月份
function getMonthDays($year,$month){
    switch($month){
        case 4:
        case 6:
        case 9:
        case 11:
            $days = 30;
            break;
        case 2:
            if ($year%4==0){
                if ($year%100==0){
                    $days = $year%400==0 ? 29 : 28;
                }else{
                    $days =29;
                }
            }else{
                $days = 28;
            }
            break;
        default:
            $days = 31;
            break;
    }
    return $days;
}

function download($file_dir,$file_name){
	//参数说明：
	//file_dir:文件所在目录
	//file_name:文件名
	$file_dir = chop($file_dir);//去掉路径中多余的空格
	//得出要下载的文件的路径
	if($file_dir != ''){
		$file_path = $file_dir;
		if(substr($file_dir,strlen($file_dir)-1,strlen($file_dir)) != '/')
		$file_path .= '/';
		$file_path .= $file_name;
	}else{
		$file_path = $file_name;
	}

	//判断要下载的文件是否存在
	if(!file_exists($file_path)){
		echo '对不起,你要下载的文件不存在。';
		return false;
	}

	$file_size = filesize($file_path);
	header("Content-type:application/octet-tream");
	header('Content-Transfer-Encoding: binary');
	header("Content-Length:$file_size");
	header("Content-Disposition:attachment;filename=".$file_name);
	@readfile($file_name);
}

//判断邮箱
function isEmailnew($vStr){
    $vLength = strlen($vStr);
    if($vLength < 3 || $vLength > 50){
        return false;
    }else{
        return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $vStr);
    }
}

function randomNum($c = 8) {
    $m = min(mt_getrandmax(), pow(10, $c));
    return str_pad(mt_rand(0, $m - 1), $c, '0', STR_PAD_LEFT);
}

function sendEmail($mail_address,$title,$content){
    import('Common.Lib.Mail');
    $is_send = SendMail($mail_address,$title,$content);
    return $is_send;
}

function Upload() {
    header('Content-Type: text/html; charset=UTF-8');
    $inputname='filedata';      //表单文件域name
    $attachdir='./Public/Uploads';     //上传文件保存路径，结尾不要带/
    $dirtype=1;                 //1:按天存入目录 2:按月存入目录 3:按扩展名存目录  建议使用按天存
    $maxattachsize=1048576 * 300;//最大上传大小，默认是300M
    $upext='pdf,zip,rar,txt,doc,docx,ppt,xls,xlsx,csv,jpg,jpeg,gif,png,bmp,swf,flv,fla,avi,wmv,wma,rm,mov,mpg,rmvb,3gp,mp4,mp3';//上传扩展名
    $msgtype=2;                  //返回上传参数的格式：1，只返回url，2，返回参数数组
    $immediate=isset($_REQUEST['immediate'])?$_REQUEST['immediate']:0;//立即上传模式
    ini_set('date.timezone','Asia/Shanghai');//时区

    if(isset($_SERVER['HTTP_CONTENT_DISPOSITION'])){//HTML5上传
        if(preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',$_SERVER['HTTP_CONTENT_DISPOSITION'],$info)){
            $temp_name=ini_get("upload_tmp_dir").'\\'.date("YmdHis").mt_rand(1000,9999).'.tmp';
            file_put_contents($temp_name,file_get_contents("php://input"));
            $size=filesize($temp_name);
            $_FILES[$info[1]]=array('name'=>$info[2],'tmp_name'=>$temp_name,'size'=>$size,'type'=>'','error'=>0);
        }
    }
     
    $err = "";
    $msg = "''";
     
    $upfile=@$_FILES[$inputname];
    if(!isset($upfile)){
        $err='文件域的name错误';
    }elseif(!empty($upfile['error'])){
        switch($upfile['error']){
            case '1':
                $err = '文件大小超过了php.ini定义的upload_max_filesize值';
                break;
            case '2':
                $err = '文件大小超过了HTML定义的MAX_FILE_SIZE值';
                break;
            case '3':
                $err = '文件上传不完全';
                break;
            case '4':
                $err = '无文件上传';
                break;
            case '6':
                $err = '缺少临时文件夹';
                break;
            case '7':
                $err = '写文件失败';
                break;
            case '8':
                $err = '上传被其它扩展中断';
                break;
            case '999':
            default:
                $err = '无有效错误代码';
        }
    }elseif(empty($upfile['tmp_name']) || $upfile['tmp_name'] == 'none'){
        $err = '无文件上传';
    }else{
        $temppath=$upfile['tmp_name'];
        $fileinfo=pathinfo($upfile['name']);
        $extension=$fileinfo['extension'];
        if(preg_match('/'.str_replace(',','|',$upext).'/i',$extension)){
            $bytes=filesize($temppath);
            if($bytes > $maxattachsize){
                $err='请不要上传大小超过'.$maxattachsize.'的文件';
            }else{
                switch($dirtype){
                    case 1: $attach_subdir = 'day_'.date('ymd'); break;
                    case 2: $attach_subdir = 'month_'.date('ym'); break;
                    case 3: $attach_subdir = 'ext_'.$extension; break;
                }
                $attach_dir = $attachdir.'/'.$attach_subdir;
                if(!is_dir($attach_dir)){
                    @mkdir($attach_dir, 0777);
                    @fclose(fopen($attach_dir.'/index.htm', 'w'));
                }
                PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
                $filename=date("YmdHis").mt_rand(1000,9999).'.'.$extension;
                $target = $attach_dir.'/'.$filename;

                rename($upfile['tmp_name'],$target);
                @chmod($target,0755);
                $target='/html/upload/'.$attach_subdir.'/'.$filename;
                if($immediate=='1'){
                    $target='!'.$target;
                }
                if($msgtype==1){
                    $msg="'$target'";
                }else{
                    $msg="{'url':'".$target."','localname':'".preg_replace("/([\\\\\/'])/",'\\\$1',$upfile['name'])."','id':'1'}";
                }
            }
        }else{
            $err='上传文件扩展名必需为：'.$upext;
        }
        @unlink($temppath);
    }
    echo "{'err':'".preg_replace("/([\\\\\/'])/",'\\\$1',$err)."','msg':".$msg."}";
}


function getos(){
    $otype = 1;//1:PC 2:mobile
    $ua = $_SERVER['HTTP_USER_AGENT'];
    if(preg_match('/ipad/i', $ua) || preg_match('/ipod/i', $ua) || preg_match('/iphone/i', $ua) || preg_match('/IOS/i', $ua) || preg_match('/Android/i', $ua)){
        $otype = 2;
    }
    return $otype;
}

function removeDir($dir,$filter) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir"){
                    removeDir($dir."/".$object,$filter);
                }else{
                    unlink($dir."/".$object);
                }
            }
        }
        if($dir!=$filter) rmdir($dir);
    }
}
function cleanCache($type=0) {
    $path = APP_PATH.'/Runtime/Cache/Site/';
    removeDir($path,$path);
    return count(scandir($path));
}

function ezk_cutstr_html($string){
    $string = strip_tags($string);
    $string = preg_replace ('/\n/is', '', $string);
    $string = preg_replace ('/ |　/is', '', $string);
    $string = preg_replace ('/&nbsp;/is', '', $string);

    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $t_string);

    return $string;
}

function getToIntro($str, $num){
    $intro = '';
    if($str){
        $strArr = array('。','！','？', '~', '，');
        $intro = mb_substr($str, 0, $num, 'utf-8');
        $last_str  = mb_substr($intro, -1, 1, 'utf-8');
        if(!in_array($last_str, $strArr)){
            $intro = $intro.'...';
        }
    }
    return $intro;
}

function checkWxbrowser(){
    $os_agent = $_SERVER['HTTP_USER_AGENT'];
    $wx_browser = (bool) stripos($os_agent,'MicroMessenger');
    $is_wx = 0;
    if($wx_browser){
        $is_wx = 1;
    }
    return $is_wx;
}

/**
 * 验证码检查
 */
function check_verify($code, $id = ""){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}
/**
 * get方式获取接口数据
 * $url 请求地址
 * $formate 返回格式 xml|json
 * $timeout 超时时间
 */
function get_remote_data($url, $formate = 'xml', $timeout = 1)
{
    $result = '';
    $opts = array(
        'http' => array(
            'timeout' => $timeout,
            'method' => "GET",
            'header' => "Content-Type: text/html; charset=utf-8",
        )
    );
    $result = file_get_contents($url, false, stream_context_create($opts));
    if (empty($result)) {
        return $result;
    }
    return $formate == 'xml' ? (array)simplexml_load_string($result) : $result;
}

function getfirstchar($s0)
{
    $fchar = ord($s0{0});
    if ($fchar >= ord("A") and $fchar <= ord("z")) {
        return strtoupper($s0{0});
    }
    $s1 = iconv("UTF-8", "gb2312", $s0);
    $s2 = iconv("gb2312", "UTF-8", $s1);
    if ($s2 == $s0) {
        $s = $s1;
    } else {
        $s = $s0;
    }
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if ($asc == -5736) {
        return "A";
    }
    if ($asc >= -20319 and $asc <= -20284) {
        return "A";
    }
    if ($asc >= -20283 and $asc <= -19776) {
        return "B";
    }
    if ($asc >= -19775 and $asc <= -19219) {
        return "C";
    }
    if ($asc >= -19218 and $asc <= -18711) {
        return "D";
    }
    if ($asc >= -18710 and $asc <= -18527) {
        return "E";
    }
    if ($asc >= -18526 and $asc <= -18240) {
        return "F";
    }
    if ($asc >= -18239 and $asc <= -17923) {
        return "G";
    }
    if ($asc >= -17922 and $asc <= -17418) {
        return "I";
    }
    if ($asc >= -17417 and $asc <= -16475) {
        return "J";
    }
    if ($asc >= -16474 and $asc <= -16213) {
        return "K";
    }
    if ($asc >= -16212 and $asc <= -15641) {
        return "L";
    }
    if ($asc >= -15640 and $asc <= -15166) {
        return "M";
    }
    if ($asc >= -15165 and $asc <= -14923) {
        return "N";
    }
    if ($asc >= -14922 and $asc <= -14915) {
        return "O";
    }
    if ($asc >= -14914 and $asc <= -14631) {
        return "P";
    }
    if ($asc >= -14630 and $asc <= -14150) {
        return "Q";
    }
    if ($asc >= -14149 and $asc <= -14091) {
        return "R";
    }
    if ($asc >= -14090 and $asc <= -13319) {
        return "S";
    }
    if ($asc >= -13318 and $asc <= -12839) {
        return "T";
    }
    if ($asc >= -12838 and $asc <= -12557) {
        return "W";
    }
    if ($asc >= -12556 and $asc <= -11848) {
        return "X";
    }
    if ($asc >= -11847 and $asc <= -11056) {
        return "Y";
    }
    if ($asc >= -11055 and $asc <= -10247) {
        return "Z";
    }
    return null;
}

function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    if (function_exists("mb_substr")) {
        if ($suffix) {
            return mb_substr($str, $start, $length, $charset) . "...";
        } else {
            return mb_substr($str, $start, $length, $charset);
        }
    } elseif (function_exists('iconv_substr')) {
        if ($suffix) {
            return iconv_substr($str, $start, $length, $charset) . "...";
        } else {
            return iconv_substr($str, $start, $length, $charset);
        }
    }
    $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef]
                      [x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    if ($suffix) {
        return $slice . "…";
    }
    return $slice;
}
function changeTimeType($seconds){
    if ($seconds > 3600){
        $hours = intval($seconds/3600);
        $minutes = $seconds % 3600;
        $time = $hours.":".gmstrftime('%M:%S', $minutes);
    }else{
        $time = gmstrftime('%H:%M:%S', $seconds);
    }
    return $time;
}
function get_oss_host(){
    $oss_host = 'http://'.C('OSS_HOST_NEW').'/';
    return $oss_host;
}
/**
 * 接口请求参数加密
 * @param str $data
 * @return Ambigous <boolean, mixed>
 */
function encrypt_data($data, $key = '')
{
    if (empty($key)) {
        $key = C('SECRET_KEY');
    }
    $crypt = new Crypt3Des($key);
    $result = $crypt->encrypt($data);
    return $result;
}

/**
 * 接口返回数据解密
 * @param str $data
 * @return Ambigous <boolean, mixed>
 */
function decrypt_data($data, $dejson = true, $key = '')
{
    if (empty($key)) {
        $key = C('SECRET_KEY');
    }
    $crypt = new Crypt3Des($key);
    $result = $crypt->decrypt($data);
    if ($dejson) {
        $res_data = array();
        if ($result) {
            $res_data = json_decode($result, true);
        }
    } else {
        $res_data = $result;
    }
    return $res_data;
}
/**
 * @author: vfhky 20130304 20:10
 * @description: PHP调用新浪短网址API接口
 *    * @param string $type: 非零整数代表长网址转短网址,0表示短网址转长网址
 */
function shortUrlAPI($type,$url){
    /* 这是我申请的APPKEY，大家可以测试使用 */
    $key = C('SHOW_URL_APP_KEY');
    //'1562966081';
    if($type)
        $baseurl = 'http://api.t.sina.com.cn/short_url/shorten.json?source='.$key.'&url_long='.$url;
    else
        $baseurl = 'http://api.t.sina.com.cn/short_url/expand.json?source='.$key.'&url_short='.$url;
    /* $ch=curl_init();
     curl_setopt($ch, CURLOPT_URL,$baseurl);
     curl_setopt($ch, CURLOPT_HEADER, 0);
     curl_setopt($ch, CURLOPT_TIMEOUT, 15);
     curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
     $strRes=curl_exec($ch);
     curl_close($ch);  */
     $strRes = file_get_contents($baseurl);
    $arrResponse=json_decode($strRes,true);
    if (isset($arrResponse->error) || !isset($arrResponse[0]['url_long']) || $arrResponse[0]['url_long'] == '')
        return 0;
            if($type)
                return $arrResponse[0]['url_short'];
            else
                return $arrResponse[0]['url_long'];
}
function myTrim($str)
{
    $search = array(" ","　","\n","\r","\t");
    $replace = array("","","","","");
    return str_replace($search, $replace, $str);
}
function getgeoByip($ip){
    $ak = C('BAIDU_GEO_KEY');
    $url = "http://api.map.baidu.com/location/ip?ak=".$ak."&coor=bd09ll&ip=".$ip;
    $result = file_get_contents($url);
    $re = json_decode($result,true);
    if($re && $re['status'] == 0){
        $geoArr = $re['content']['point'];
    }else {
        $geoArr['x'] = '';
        $geoArr['y'] = '';
    }
    return $geoArr;

}
//获取首字母
function getFirstCharter($str){
    if(empty($str)){return '';}
    $fchar=ord($str{0});
    if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
    $s1=iconv('UTF-8','gb2312',$str);
    $s2=iconv('gb2312','UTF-8',$s1);
    $s=$s2==$str?$s1:$str;
    $asc=ord($s{0})*256+ord($s{1})-65536;
    if($asc =='-9004') return 'M';
    if($asc =='-6993' || $asc=='-5734') return 'L';
    if($asc =='-7431' || $asc=='-5714') return 'Y';
    if($asc =='-5711') return 'X';
    if($asc =='-2072') return 'Q';
    if($asc =='-4189') return 'Z';
    if($asc>=-20319&&$asc<=-20284) return 'A';
    if($asc>=-20283&&$asc<=-19776) return 'B';
    if($asc>=-19775&&$asc<=-19219) return 'C';
    if($asc>=-19218&&$asc<=-18711) return 'D';
    if($asc>=-18710&&$asc<=-18527) return 'E';
    if($asc>=-18526&&$asc<=-18240) return 'F';
    if($asc>=-18239&&$asc<=-17923) return 'G';
    if($asc>=-17922&&$asc<=-17418) return 'H';
    if($asc>=-17417&&$asc<=-16475) return 'J';
    if($asc>=-16474&&$asc<=-16213) return 'K';
    if($asc>=-16212&&$asc<=-15641) return 'L';
    if($asc>=-15640&&$asc<=-15166) return 'M';
    if($asc>=-15165&&$asc<=-14923) return 'N';
    if($asc>=-14922&&$asc<=-14915) return 'O';
    if($asc>=-14914&&$asc<=-14631) return 'P';
    if($asc>=-14630&&$asc<=-14150) return 'Q';
    if($asc>=-14149&&$asc<=-14091) return 'R';
    if($asc>=-14090&&$asc<=-13319) return 'S';
    if($asc>=-13318&&$asc<=-12839) return 'T';
    if($asc>=-12838&&$asc<=-12557) return 'W';
    if($asc>=-12556&&$asc<=-11848) return 'X';
    if($asc>=-11847&&$asc<=-11056) return 'Y';
    if($asc>=-11055&&$asc<=-10247) return 'Z';
    return rare_words($asc);
}

function rare_words($asc=''){
    $rare_arr = array(
        -3652=>array('word'=>"窦",'first_char'=>'D'),
        -8503=>array('word'=>"奚",'first_char'=>'X'),
        -9286=>array('word'=>"酆",'first_char'=>'F'),
        -7761=>array('word'=>"岑",'first_char'=>'C'),
        -5128=>array('word'=>"滕",'first_char'=>'T'),
        -9479=>array('word'=>"邬",'first_char'=>'W'),
        -5456=>array('word'=>"臧",'first_char'=>'Z'),
        -7223=>array('word'=>"闵",'first_char'=>'M'),
        -2877=>array('word'=>"裘",'first_char'=>'Q'),
        -6191=>array('word'=>"缪",'first_char'=>'M'),
        -5414=>array('word'=>"贲",'first_char'=>'B'),
        -4102=>array('word'=>"嵇",'first_char'=>'J'),
        -8969=>array('word'=>"荀",'first_char'=>'X'),
        -4938=>array('word'=>"於",'first_char'=>'Y'),
        -9017=>array('word'=>"芮",'first_char'=>'R'),
        -2848=>array('word'=>"羿",'first_char'=>'Y'),
        -9477=>array('word'=>"邴",'first_char'=>'B'),
        -9485=>array('word'=>"隗",'first_char'=>'K'),
        -6731=>array('word'=>"宓",'first_char'=>'M'),
        -9299=>array('word'=>"郗",'first_char'=>'X'),
        -5905=>array('word'=>"栾",'first_char'=>'L'),
        -4393=>array('word'=>"钭",'first_char'=>'T'),
        -9300=>array('word'=>"郜",'first_char'=>'G'),
        -8706=>array('word'=>"蔺",'first_char'=>'L'),
        -3613=>array('word'=>"胥",'first_char'=>'X'),
        -8777=>array('word'=>"莘",'first_char'=>'S'),
        -6708=>array('word'=>"逄",'first_char'=>'P'),
        -9302=>array('word'=>"郦",'first_char'=>'L'),
        -5965=>array('word'=>"璩",'first_char'=>'Q'),
        -6745=>array('word'=>"濮",'first_char'=>'P'),
        -4888=>array('word'=>"扈",'first_char'=>'H'),
        -9309=>array('word'=>"郏",'first_char'=>'J'),
        -5428=>array('word'=>"晏",'first_char'=>'Y'),
        -2849=>array('word'=>"暨",'first_char'=>'J'),
        -7206=>array('word'=>"阙",'first_char'=>'Q'),
        -4945=>array('word'=>"殳",'first_char'=>'S'),
        -9753=>array('word'=>"夔",'first_char'=>'K'),
        -10041=>array('word'=>"厍",'first_char'=>'S'),
        -5429=>array('word'=>"晁",'first_char'=>'C'),
        -2396=>array('word'=>"訾",'first_char'=>'Z'),
        -7205=>array('word'=>"阚",'first_char'=>'K'),
        -10049=>array('word'=>"乜",'first_char'=>'N'),
        -10015=>array('word'=>"蒯",'first_char'=>'K'),
        -3133=>array('word'=>"竺",'first_char'=>'Z'),
        -6698=>array('word'=>"逯",'first_char'=>'L'),
        -9799=>array('word'=>"俟",'first_char'=>'Q'),
        -6749=>array('word'=>"澹",'first_char'=>'T'),
        -7220=>array('word'=>"闾",'first_char'=>'L'),
        -10047=>array('word'=>"亓",'first_char'=>'Q'),
        -10005=>array('word'=>"仉",'first_char'=>'Z'),
        -3417=>array('word'=>"颛",'first_char'=>'Z'),
        -6431=>array('word'=>"驷",'first_char'=>'S'),
        -7226=>array('word'=>"闫",'first_char'=>'Y'),
        -9293=>array('word'=>"鄢",'first_char'=>'Y'),
        -6205=>array('word'=>"缑",'first_char'=>'G'),
        -9764=>array('word'=>"佘",'first_char'=>'S'),
        -9818=>array('word'=>"佴",'first_char'=>'N'),
        -9509=>array('word'=>"谯",'first_char'=>'Q'),
        -3122=>array('word'=>"笪",'first_char'=>'D'),
        -9823=>array('word'=>"佟",'first_char'=>'T'),
        -6234=>array('word'=>"禤",'first_char'=>'X'),
        -3589=>array('word'=>"覃",'first_char'=>'Q'),
        -9481=>array('word'=>"邝",'first_char'=>'K'),
    );
    if(array_key_exists($asc, $rare_arr) && $rare_arr[$asc]['first_char']){
        return $rare_arr[$asc]['first_char'] ;
    }else{
        return null;
    }
}
//二维数组去重
function  assoc_unique($arr, $key)
{
    $rAr = array();
    for ($i = 0; $i<count($arr); $i++)
    {
        if (!isset($rAr[$arr[$i][$key]]))
        {
            $rAr[$arr[$i][$key]] = $arr[$i];
        }
    }
    return $rAr;
}
//随机生成一个N位数
function generate_code($length = 4) {
    $min = pow(10 , ($length - 1));
    $max = pow(10, $length) - 1;
    return rand($min, $max);
}
function sortArrByOneField(&$array, $field, $desc = false){
    $fieldArr = array();
    foreach ($array as $k => $v) {
        $fieldArr[$k] = $v[$field];
    }
    $sort = $desc == false ? SORT_ASC : SORT_DESC;
    array_multisort($fieldArr, $sort, $array);
}
function assoc_unique_new(&$arr, $key)
{
    $rAr=array();
    for($i=0;$i<count($arr);$i++)
    {
    if(!isset($rAr[$arr[$i][$key]]))
    {
    $rAr[$arr[$i][$key]]=$arr[$i];
}
}
$arr=array_values($rAr);
}
/**
 * @desc 获取网络版酒楼的类型id
 * @param number $type  1:数组  2:字符串
 */
function getHeartBoXtypeIds($type=1){
    $heart_hotel_box_type = C('heart_hotel_box_type');
    if($type==1){
       $heart_hotel_box_arr = array_keys($heart_hotel_box_type);
       return  $heart_hotel_box_arr; 
    }else {
        $heart_hotel_box_str = '';
        foreach($heart_hotel_box_type as $key=>$v){
            $heart_hotel_box_str .= $space . $key;
            $space = ',';
        }
        return $heart_hotel_box_str;
    }
}
function secsToStr($secs) {
    if($secs>=86400){$days=floor($secs/86400);
    $secs=$secs%86400;
    $r=$days.' 天';
    
    if($secs>0){$r.=', ';}}
    if($secs>=3600){$hours=floor($secs/3600);
    $secs=$secs%3600;
    $r.=$hours.' 小时';
    
    if($secs>0){$r.=', ';}}
    if($secs>=60){$minutes=floor($secs/60);
    $secs=$secs%60;
    $r.=$minutes.' 分';
    
    if($secs>0){$r.=', ';}}
    $r.=$secs.' 秒';
    
    return $r;
}
/**
 * 中文截取
 * @param str $str		需要截取的文字
 * @param int $len		截取长度
 * @param str $encoding	编码
 * @param bool$CUT_LEFT	是否从左侧开始截取
 */
function cut_str($str,$len,$CUT_LEFT='3',$encoding='UTF-8'){
    if(mb_strlen($str,$encoding)>$len){
        if($CUT_LEFT==1){
            return '…'.mb_substr($str,-$len,$len,$encoding);
        }else if($CUT_LEFT ==2){
            return mb_substr($str,0,$len,$encoding).'…';
        }else if($CUT_LEFT ==3){
            return mb_substr($str,0,$len,$encoding);
        }

    }else{
        return $str;
    }
}
function iconv_arr($data){
    if(is_array($data)){
        foreach($data as $k=>$v){
            $data[$k] = iconv_arr($v);
        }
    }else{
        $data = mb_convert_encoding($data, "html-entities","utf8" );
    }
    return $data;
}
//获取文件大小
function remote_filesize($uri,$unit = 1,$user='',$pw='')
{
    // start output buffering
    ob_start();
    // initialize curl with given uri
    $ch = curl_init($uri);
    // make sure we get the header
    curl_setopt($ch, CURLOPT_HEADER, 1);
    // make it a http HEAD request
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    // if auth is needed, do it here
    if (!empty($user) && !empty($pw))
    {
        $headers = array('Authorization: Basic ' . base64_encode($user.':'.$pw));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $okay = curl_exec($ch);
    curl_close($ch);
    // get the output buffer
    $head = ob_get_contents();
    // clean the output buffer and return to previous
    // buffer settings
    ob_end_clean();

    //echo '<br>head-->'.$head.'<----end <br>';

    // gets you the numeric value from the Content-Length
    // field in the http header
    $regex = '/Content-Length:\s([0-9].+?)\s/';
    $count = preg_match($regex, $head, $matches);

    // if there was a Content-Length field, its value
    // will now be in $matches[1]
    if (isset($matches[1]))
    {
        $size = $matches[1];
    }
    else
    {
        $size = 'unknown';
    }
    if($unit==1){
        $last=round($size/(1024*1024),2);
        return $last.' MB';
    }  else {
        $last=round($size/1024,2);
        return $last.' KB';
    }

    //return $size;
}
//kb mb  gb tb
function formatBytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}
function getMillisecond() {
    list($s1, $s2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}
function getVsmallHotelList(){
    $redis = SavorRedis::getInstance();
    $redis->select(12);
    $vm_hotel_key = C('VM_HOTEL_LIST');
    $rts = $redis->get($vm_hotel_key);
    $vm_small_hotel_list = json_decode($rts,true);
    $tmp_hotel_arr = array();
    foreach($vm_small_hotel_list as $key=>$v){
        $tmp_hotel_arr[]= $v['hotel_id'];
    }
    return $tmp_hotel_arr;
}

function getWxAccessToken($app_config){
    $key_token = $app_config['cache_key'];
    $redis = SavorRedis::getInstance();
    $redis->select(5);
    $token = $redis->get($key_token);
    if(empty($token)){
        $url_access_token = 'https://api.weixin.qq.com/cgi-bin/token';
        $appid = $app_config['appid'];
        $appsecret = $app_config['appsecret'];
        $url = $url_access_token."?grant_type=client_credential&appid=$appid&secret=$appsecret";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $re = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($re,true);
        if(isset($result['access_token'])){
            $redis->set($key_token,$result['access_token'],360);
            $token = $result['access_token'];
        }
    }
    return $token;
}
function curlPost($url = '',  $post_data = ''){
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $post_data,
    CURLOPT_HTTPHEADER => array(
    "Content-Type: application/x-www-form-urlencoded",
    ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return 0;
    } else {

        return $response;
    }
}
function curlGet($url)
{
    //初始化
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL,$url);
    // 执行后不直接打印出来
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // 不从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    //执行并获取HTML文档内容
    $output = curl_exec($ch);
    
    //释放curl句柄
    curl_close($ch);
    
    return $output;
}
function rad($d){
    return $d * 0.017453292519943; //$d * 3.1415926535898 / 180.0;
}

function mile($dist){
    return $dist * 0.0006214;
}
/**
 * @param 起点维度 $lat1
 * @param 起点经度 $lng1
 * @param 终点维度 $lat2
 * @param 终点经度 $lng2
 * @param 1米 2英里 $type
 * @return number
 */
function geo_distance($lat1, $lng1, $lat2, $lng2, $type = 1){
    $r = 6378137; // m 为单位
    $radLat1 = rad($lat1);
    $radLat2 = rad($lat2);
    $a = $radLat1 - $radLat2;
    $b = rad($lng1) - rad($lng2);
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $s = $s * $r;
    $distance = round($s * 10000) / 10000;
    if ($type == 2) {
        $distance = mile($distance);
    }
    return $distance;
}

/**
 * @desc 获取客户端的ip地址
 */
function get_client_ipaddr()
{
    if (!empty($_SERVER ['HTTP_CLIENT_IP']) && filter_valid_ip($_SERVER ['HTTP_CLIENT_IP'])) {
        return $_SERVER ['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
        $iplist = explode(',', $_SERVER ['HTTP_X_FORWARDED_FOR']);
        foreach ($iplist as $ip) {
            if (filter_valid_ip($ip)) {
                return $ip;
            }
        }
    }
    if (!empty($_SERVER ['HTTP_X_FORWARDED']) && filter_valid_ip($_SERVER ['HTTP_X_FORWARDED'])) {
        return $_SERVER ['HTTP_X_FORWARDED'];
    }
    if (!empty($_SERVER ['HTTP_X_CLUSTER_CLIENT_IP']) && filter_valid_ip($_SERVER ['HTTP_X_CLUSTER_CLIENT_IP'])) {
        return $_SERVER ['HTTP_X_CLUSTER_CLIENT_IP'];
    }
    if (!empty($_SERVER ['HTTP_FORWARDED_FOR']) && filter_valid_ip($_SERVER ['HTTP_FORWARDED_FOR'])) {
        return $_SERVER ['HTTP_FORWARDED_FOR'];
    }
    if (!empty($_SERVER ['HTTP_FORWARDED']) && filter_valid_ip($_SERVER ['HTTP_FORWARDED'])) {
        return $_SERVER ['HTTP_FORWARDED'];
    }
    return $_SERVER ['REMOTE_ADDR'];
}

function filter_valid_ip($ip)
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 |
            FILTER_FLAG_IPV6 |
            FILTER_FLAG_NO_PRIV_RANGE |
            FILTER_FLAG_NO_RES_RANGE) === false
    ) {
        return false;
    }
    return true;
}
?>
