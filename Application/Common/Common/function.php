<?php

/*
 * 删除缓存方法
 */
function delFileByDir($dir) {
	$dh = opendir($dir);
	while ($file = readdir($dh)) {
		if ($file != "." && $file != "..") {

			$fullpath = $dir . "/" . $file;
			if (is_dir($fullpath)) {
				delFileByDir($fullpath);
			} else {
				unlink($fullpath);
			}
		}
	}
	closedir($dh);
}


/*根据id获取头像
 * */
function get_nickname($userid){
	$userinfo = M('user')->where("id = $userid")->find();
	if($userinfo['nickname']){
		return $userinfo['nickname'];
	}else{
		return false;
	}
}

/*post请求获取数据*/
function curlPost($url, $timeout = 5) {
	if (function_exists('file_get_contents')) {
		$optionget = array('http' => array('method' => "GET", 'header' => "User-Agent:Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.5.21022; .NET CLR 3.0.04506; CIBA)\r\nAccept:*/*\r\nReferer:https://kyfw.12306.cn/otn/lcxxcx/init"));
		$file_contents = file_get_contents($url, false, stream_context_create($optionget));
	} else {
		$ch = curl_init();
		$header = array('Accept:*/*', 'Accept-Charset:GBK,utf-8;q=0.7,*;q=0.3', 'Accept-Encoding:gzip,deflate,sdch', 'Accept-Language:zh-CN,zh;q=0.8,ja;q=0.6,en;q=0.4', 'Connection:keep-alive', 'Host:kyfw.12306.cn', 'Referer:https://kyfw.12306.cn/otn/lcxxcx/init', );
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);
	}
	$file_contents = json_decode($file_contents, true);
	return $file_contents;
}
/*get请求获取数据*/
function curlGet($url){
	$ch = curl_init();
	//设置选项，包括URL
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT,2);
	//执行并获取HTML文档内容
	$output = curl_exec($ch);
	//释放curl句柄
	curl_close($ch);
	return $output;

}


/*
	中国国情下的判断浏览器类型，简直就是五代十国，乱七八糟，对博主的收集表示感谢

	参考：
	http://www.cnblogs.com/wangchao928/p/4166805.html
	http://www.useragentstring.com/pages/Internet%20Explorer/
	https://github.com/serbanghita/Mobile-Detect/blob/master/Mobile_Detect.php

	Mozilla/4.0 (compatible; MSIE 5.0; Windows NT)
	Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2)
	Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)

	Win7+ie9：
	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; Tablet PC 2.0; .NET4.0E)

	win7+ie11，模拟 78910 头是一样的
	mozilla/5.0 (windows nt 6.1; wow64; trident/7.0; rv:11.0) like gecko

	Win7+ie8：
	Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.3)

	WinXP+ie8：
	Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; GTB7.0)

	WinXP+ie7：
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)

	WinXP+ie6：
	Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)

	傲游3.1.7在Win7+ie9,高速模式:
	Mozilla/5.0 (Windows; U; Windows NT 6.1; ) AppleWebKit/534.12 (KHTML, like Gecko) Maxthon/3.0 Safari/534.12

	傲游3.1.7在Win7+ie9,IE内核兼容模式:
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E)

	搜狗
	搜狗3.0在Win7+ie9,IE内核兼容模式:
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E; SE 2.X MetaSr 1.0)

	搜狗3.0在Win7+ie9,高速模式:
	Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.33 Safari/534.3 SE 2.X MetaSr 1.0

	360
	360浏览器3.0在Win7+ie9:
	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E)

	QQ 浏览器
	QQ 浏览器6.9(11079)在Win7+ie9,极速模式:
	Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.41 Safari/535.1 QQBrowser/6.9.11079.201

	QQ浏览器6.9(11079)在Win7+ie9,IE内核兼容模式:
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E) QQBrowser/6.9.11079.201

	阿云浏览器
	阿云浏览器 1.3.0.1724 Beta 在Win7+ie9:
	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)

	MIUI V5
	Mozilla/5.0 (Linux; U; Android <android-version>; <location>; <MODEL> Build/<ProductLine>) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 XiaoMi/MiuiBrowser/1.0
*/
function get__browser() {
	// 默认为 chrome 标准浏览器
	$browser = array(
		'device'=>'pc', // pc|mobile|pad
		'name'=>'chrome', // chrome|firefox|ie|opera
		'version'=>30,
	);
	$agent = $_SERVER['HTTP_USER_AGENT'];
	// 主要判断是否为垃圾 IE6789
	if(strpos($agent, 'msie') !== FALSE || stripos($agent, 'trident') !== FALSE) {
		$browser['name'] = 'ie';
		$browser['version'] = 8;
		preg_match('#msie\s*([\d\.]+)#is', $agent, $m);
		if(!empty($m[1])) {
			if(strpos($agent, 'compatible; msie 7.0;') !== FALSE) {
				$browser['version'] = 8;
			} else {
				$browser['version'] = intval($m[1]);
			}
		} else {
			// 匹配兼容模式 Trident/7.0，兼容模式下会有此标志 $trident = 7;
			preg_match('#Trident/([\d\.]+)#is', $agent, $m);
			if(!empty($m[1])) {
				$trident = intval($m[1]);
				$trident == 4 AND $browser['version'] = 8;
				$trident == 5 AND $browser['version'] = 9;
				$trident > 5 AND $browser['version'] = 10;
			}
		}
	}

	if(isset($_SERVER['HTTP_X_WAP_PROFILE']) || (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap") || stripos($agent, 'phone')  || stripos($agent, 'mobile') || strpos($agent, 'ipod'))) {
		$browser['device'] = 'mobile';
	} elseif(strpos($agent, 'pad') !== FALSE) {
		$browser['device'] = 'pad';
		$browser['name'] = '';
		$browser['version'] = '';
	/*
	} elseif(strpos($agent, 'miui') !== FALSE) {
		$browser['device'] = 'mobile';
		$browser['name'] = 'xiaomi';
		$browser['version'] = '';
	*/
	} else {
		$robots = array('bot', 'spider', 'slurp');
		foreach($robots as $robot) {
			if(strpos($agent, $robot) !== FALSE) {
				$browser['name'] = 'robot';
				return $browser;
			}
		}
	}
	return $browser;
}


/*
 * 生成二维码
 * */
function qrcode($url,$level=3,$size=4){
	Vendor('phpqrcode.phpqrcode');
	$errorCorrectionLevel =intval($level) ;//容错级别
	$matrixPointSize = intval($size);//生成图片大小
		//生成二维码图片
	echo $_SERVER['REQUEST_URI'];
	$object = new \QRcode();

	$date = date('Y-m-d');
	$path = "Uploads/qrcode/".$date.'/';
	if (!file_exists($path)) {
        mkdir ("$path", 0777, true);
	}
	$name = time().'_'.mt_rand();
    //生成的文件名
    $fileName = $path.$name.'.png';
	$res = $object->png($url, $fileName, $errorCorrectionLevel, $matrixPointSize, 2);
	return $fileName;
 }

/**
 * 获取微信操作对象
 * @staticvar array $wechat
 * @param type $type
 * @return WechatReceive
 */
function & load_wechat($type = '') {
	!class_exists('Wechat\Loader', FALSE) && Vendor('Wechat.Loader');
	static $wechat = array();
	$index = md5(strtolower($type));
	if (!isset($wechat[$index])) {
		// 从数据库查询配置参数
		$res = C('WEIXINPAY_CONFIG');
		$config['appid'] = $res['APPID'];
		$config['appsecret'] = $res['APPSECRET'];
		$config['encodingaeskey'] = '';
		$config['mch_id'] = $res['MCHID'];
		$config['partnerkey'] = $res['KEY'];
		$config['ssl_cer'] = '';
		$config['ssl_key'] = '';
		$config['cachepath'] = '';

		// 设置SDK的缓存路径
		$config['cachepath'] = CACHE_PATH . 'Data/';
		$wechat[$index] = &\Wechat\Loader::get_instance($type, $config);
	}
	return $wechat[$index];
}
/*
 * 数据采集
 */
function getPK10($type)
{
    if($type =='update'){
            $result = S('pkdata');
    if (empty($result)) {
        $url = "http://api.1680210.com/pks/getLotteryPksInfo.do?issue=615652&lotCode=10001";

        $result = curlGet($url);
        S('pkdata', $result, 5);
    }
        $data = json_decode($result, true);
        $data = $data['result']['data'];
        $pkdata['time'] = time();
        $pkdata['game'] = 'bjpks';
        $pkdata['current']['periodNumber'] = $data['preDrawIssue'];
        $pkdata['current']['awardTime'] = $data['preDrawTime'];
        $pkdata['current']['awardNumbers'] = $data['preDrawCode'];
        $pkdata['next']['periodNumber'] = $data['drawIssue'];
        $pkdata['next']['awardTime'] = $data['drawTime'];
        $pkdata['next']['awardTimeInterval'] = (strtotime($data['drawTime']) - time()) * 1000;
        $pkdata['next']['delayTimeInterval'] = 0;
        //防止偶然性没有获取到的错误
        if($result){
            F('cachepk10',$pkdata);
        }else{
            $pkdata = F('cachepk10');
        }
        S('newcachepk10',$pkdata);
    }else{
        $pk10data = S('newcachepk10');
        $pk10data['next']['awardTimeInterval'] = (strtotime($pk10data['next']['awardTime']) - time()) * 1000;
        return $pk10data;
    }
}
//北京28
function getBj28($type)
{
    $begin = strtotime('00:00:00');
    $end = strtotime("08:59:59");
    if($begin<time() && time()<$end){
        //加拿大
        getJnd28();
    }else{
        if ($type =='update'){
            $result = S('klbjdata');
           if (empty($result)) {
                $url = "http://api.1680210.com/LuckTwenty/getBaseLuckTewnty.do?issue=&lotCode=10014";
                $result = curlGet($url);
               S('klbjdata', $result, 5);
            }
            $data = json_decode($result, true);
            $data = $data['result']['data'];
            //获取开奖号码
            $haoma = explode(',', $data['preDrawCode']);
            $caisan = array_chunk($haoma, 6);
            $num1all = array_sum($caisan[0]);
            $num2all = array_sum($caisan[1]);
            $num3all = array_sum($caisan[2]);
            $num1 = str_split($num1all);
            $num2 = str_split($num2all);
            $num3 = str_split($num3all);
            $number1 = $num1[count($num1) - 1];
            $number2 = $num2[count($num2) - 1];
            $number3 = $num3[count($num3) - 1];
            //当前开奖时间

            //数组合并
            $jnddata['time'] = time();
            $klbj28data['game'] = 'Bj28';
            $klbj28data['current']['periodNumber'] = $data['preDrawIssue'];
            $klbj28data['current']['awardTime'] = $data['preDrawTime'];
            $klbj28data['current']['awardNumbers'] = $number1 . ',' . $number2 . ',' . $number3;
            $klbj28data['next']['periodNumber'] = $data['drawIssue'];
            $klbj28data['next']['awardTime'] = $data['drawTime'];
            $klbj28data['next']['awardTimeInterval'] = (strtotime($data['drawTime']) - time()) * 1000;
            $klbj28data['next']['delayTimeInterval'] = 0;
            if ($result) {
               F('cachebj28',$klbj28data);
            } else {
               $klbj28data = F('cachebj28');
            }
            S('newcachebj28',$klbj28data);
        }else{
            $bj28data = S('newcachebj28');
            $bj28data['next']['awardTimeInterval'] = (strtotime($bj28data['next']['awardTime']) - time()) * 1000;
            return $bj28data;
        }
    }

//    北京28直接运用款-----------------------↓--------------------------↓
//    $result = S('dandata');
//    $begin = strtotime('11:58:00');
//    $end = strtotime("11:59:59");
//    if($begin<time() && time()<$end){
//        if (empty($result)) {
//        $url = "http://api.1680210.com/LuckTwenty/getPcLucky28.do?issue=";
//        $result = curlGet($url);
//        S('dandata', $result, 1);
//        }
//    }else{
//        if (empty($result)) {
//            $url = "http://api.1680210.com/LuckTwenty/getPcLucky28.do?issue=";
//            $result = curlGet($url);
//            S('dandata', $result, 5);
//        }
//    }
//    $data = json_decode($result, true);
//    $data = $data['result']['data'];
//    $dandata['time'] = time();
//    $dandata['game'] = 'pc28';
//    $dandata['current']['periodNumber'] = $data['preDrawIssue'];
//    $dandata['current']['awardTime'] = $data['preDrawTime'];
//    $dandata['current']['awardNumbers'] = $data['preDrawCode'];
//    $dandata['next']['periodNumber'] = $data['drawIssue'];
//    $dandata['next']['awardTime'] = $data['drawTime'];
//    $dandata['next']['awardTimeInterval'] = (strtotime($data['drawTime']) - time()) * 1000;
//    $dandata['next']['delayTimeInterval'] = 0;
//    $dandata['test'] = strtotime("2017-06-21 15:05:00") - time();
//    //防止偶然性没有获取到的错误
//    if($dandata['current']['periodNumber']){
//        session("bjse",$dandata);
//    }else{
//        $jnddata = session("bjse");
//    }
//    return $dandata;
}
//加拿大28
function getJnd28($type)
{
    //以下的为querylist 获取的数据------------------------------------------------
//    $result = S('jnd28data');
//    if (empty($result)) {
//        $data = QueryList::Query('http://www.pc6777.com/jnd28/', array(
//            'time' => array('script:eq(2)', 'text'),
//            "currentqihao" => array('.kj_white_line:eq(1)', 'text'),
//        ))->data;
//        //获取时间
//        $str = $data[0]['time'];
//        $result = array();
//        preg_match_all("/(?:\()(.*)(?:\))/i", $str, $result);
//        $allnum = $result[1][0];
//        $resdata = explode(',', $allnum);
////    $all['time'] = $resdata[0];
//        //当前期号
//        $nowqihaoalldata = explode("u00a0", $data[0]['currentqihao']);
//        $nowqihao1 = explode(' ', $nowqihaoalldata[0]);
//        $nowqihao2 = explode("期", $nowqihao1[0]);
//
//        //计算下一期的开奖时间
//        $qianmian = explode(']', $nowqihao2[1]);
//        $riqi = explode('[', $qianmian[0]);
//        $fariqi = $riqi[1];
//        $awar = date("Y-m-d");
//        $shijianchuo = strtotime("$awar" . "$fariqi") + 200;
//        $zhuanhuaderiqi = date('Y:m:d H:i:s', $shijianchuo);
////    $all['currentqihao'] = $nowqihao2[0];
//        //当前号码
//        $dangqiankaijiangshijian = date('Y-m-d H:i:s', strtotime("$awar" . "$fariqi"));
//        $nowhaoma = explode("]", $nowqihao1[0]);
//        $nowhaomaarr = explode('+', $nowhaoma[1]);
//        $testes = json_encode($nowhaomaarr[0]);
//        $afa = explode('u00a0', $testes);
//        $jjkd = preg_replace('/\D/s', '', $afa[2]);
//        $nowhaomaarr1 = $jjkd;
//        $nowhaomaarr2 = $nowhaomaarr[1];
//        $nowhaomaarr3 = $nowhaomaarr[2];
////    $all['currentnumber'] = $nowhaomaarr1 . ',' . $nowhaomaarr2 . ',' . $nowhaomaarr3;
//        //下一期
////    $all['nextqihao'] = $resdata[1];
//        $all['kaijiangshijain'] = 11;
//        //数据储存
//        $jnddata['time'] = time();
//        $jnddata['game'] = 'jnd28';
//        $jnddata['dangqianshijian'] = $dangqiankaijiangshijian;
//        $jnddata['dangqianqihao'] = $nowqihao2[0];
////    $jnddata['current']['awardTime'] = $data[0]['cTermDT'];
//        $jnddata['dangqianhaoma'] = $nowhaomaarr1 . ',' . $nowhaomaarr2 . ',' . $nowhaomaarr3;
//        $jnddata['nextqihao'] = $resdata[1];
//        $jnddata['xiayiqikaijiang'] = date("Y-m-d H:i:s", (time() + $resdata[0]));
////            $jnddata['shengxiashijian'] = ((time() + $resdata[0]) - time()) * 1000;
//        $jnddata['next']['delayTimeInterval'] = 0;
//        S('jnd28data', $jnddata, 5);
//    }
//    $datapp = $result;
//    $jnddatas['time'] = time();
//    $jnddatas['game'] = 'jnd28';
//    $jnddatas['current']['periodNumber'] = $datapp['dangqianqihao'];
//    $jnddatas['current']['awardTime'] = $datapp['dangqianshijian'];
//    $jnddatas['current']['awardNumbers'] = $datapp['dangqianhaoma'];
//    $jnddatas['next']['periodNumber'] = $datapp['nextqihao'];
//    $jnddatas['next']['awardTime'] = $datapp['xiayiqikaijiang'];
//    $jnddatas['next']['awardTimeInterval'] = (strtotime($datapp['xiayiqikaijiang']) - time()) * 1000;
//    $jnddatas['next']['delayTimeInterval'] = 0;
//    if($jnddatas['next']['periodNumber'] == null){
//        $jnddatas = session('jndsession');
//    }else{
//        session('jndsession',$jnddatas);
//    }
//    return $jnddatas;

//下面是获取彩票控的---------------------------------------------------------------------------------------
    $result = S('jnd28data');
    if($type == 'update'){
            if (empty($result)) {
        $url = "http://api.kaijiangtong.com/lottery/?name=jndklb&format=json3&uid=789423&token=1cd714ebb2c93a811fba7533a30d28fed7ccb7e1&num=1";
        $result = curlGet($url);
       S('jnd28data', $result, 5);
   }
        $data = json_decode($result, true);
        $haoma = explode(',', $data[0]['cTermResult']);
        $n1 = $haoma['1'] + $haoma['4'] + $haoma['7'] + $haoma['10'] + $haoma['13'] + $haoma['16'];
        $n2 = $haoma['2'] + $haoma['5'] + $haoma['8'] + $haoma['11'] + $haoma['14'] + $haoma['17'];
        $n3 = $haoma['3'] + $haoma['6'] + $haoma['9'] + $haoma['12'] + $haoma['15'] + $haoma['18'];
        $num1 = str_split($n1);
        $num2 = str_split($n2);
        $num3 = str_split($n3);
        $number1 = $num1[count($num1) - 1];
        $number2 = $num2[count($num2) - 1];
        $number3 = $num3[count($num3) - 1];
        //传输的数据名称：
        $jnddata['time'] = time();
        $jnddata['game'] = 'jnd28';
        $jnddata['current']['periodNumber'] = $data[0]['cTerm'];
        $jnddata['current']['awardTime'] = $data[0]['cTermDT'];
        $jnddata['current']['awardNumbers'] = $number1 . ',' . $number2 . ',' . $number3;
        $jnddata['next']['periodNumber'] = $data[0]['cTerm'] + 1;
        $jnddata['next']['awardTime'] = date("Y-m-d H:i:s", (strtotime($data[0]['cTermDT']) + 210));
        $jnddata['next']['awardTimeInterval'] = ((strtotime($data[0]['cTermDT']) + 210) - time()) * 1000;
        $jnddata['next']['delayTimeInterval'] = 0;

        if ($result){
            F('cachejnd',$jnddata);
        }else{
            $jnddata = F('cachejnd');
        }
        S('newcachejnd',$jnddata);
    }else{
        $jnddata = S('newcachejnd');
        $jnddata['next']['awardTimeInterval'] = (strtotime($jnddata['next']['awardTime']) - time()) * 1000;
        return $jnddata;
    }
}
//時時彩
function getssc($type)
{
    if ($type == 'update'){
            $result = S('sscdata');
   if (empty($result)) {
        $url = "http://api.1680210.com/CQShiCai/getBaseCQShiCai.do?issue=&lotCode=10002";
        $result = curlGet($url);
        S('sscdata', $result, 5);
    }
        $data = json_decode($result, true);
        $data = $data['result']['data'];
        $pkdata['time'] = time();
        $pkdata['game'] = 'ssc';
        $pkdata['current']['periodNumber'] = $data['preDrawIssue'];
        $pkdata['current']['awardTime'] = $data['preDrawTime'];
        $pkdata['current']['awardNumbers'] = $data['preDrawCode'];
        $pkdata['next']['periodNumber'] = $data['drawIssue'];
        $pkdata['next']['awardTime'] = $data['drawTime'];
        $pkdata['next']['awardTimeInterval'] = (strtotime($data['drawTime']) - time()) * 1000;
        $pkdata['next']['delayTimeInterval'] = 0;
        if ($result){
            F('cachessc',$pkdata);
        }else{
            $pkdata = F('cachessc');
        }
        S('newcachessc',$pkdata);
    }else{
        $getssc = S('newcachessc');
        $getssc['next']['awardTimeInterval'] = (strtotime($getssc['next']['awardTime']) - time()) * 1000;
        return $getssc;
    }
}


function getkuai3($type)
{
    if ($type == 'update') {
        $result = S('kuai3');
        if (empty($result)) {
            $url = "http://api.1680210.com/lotteryJSFastThree/getBaseJSFastThree.do?issue=&lotCode=10033";
            $result = curlGet($url);
            S('kuai3', $result, 5);
        }
        $data = json_decode($result, true);
        $data = $data['result']['data'];
        $pkdata['time'] = time();
        $pkdata['game'] = 'kuai3';
        $pkdata['current']['periodNumber'] = $data['preDrawIssue'];
        $pkdata['current']['awardTime'] = $data['preDrawTime'];
        $pkdata['current']['awardNumbers'] = $data['preDrawCode'];
        $pkdata['next']['periodNumber'] = $data['drawIssue'];
        $pkdata['next']['awardTime'] = $data['drawTime'];
        $pkdata['next']['awardTimeInterval'] = (strtotime($data['drawTime']) - time()) * 1000;
        $pkdata['next']['delayTimeInterval'] = 0;
        if ($pkdata['current']['periodNumber']) {
            F('cachekuai3', $pkdata);
        } else {
            $pkdata = F('cachekuai3');
            $pkdata['next']['awardTimeInterval'] = (strtotime($pkdata['next']['awardTime']) - time()) * 1000;
        }
        S('newcachekuai3', $pkdata);
    } else {
        $getkuai3 = S('newcachekuai3');
        $getkuai3['next']['awardTimeInterval'] = (strtotime($getkuai3['next']['awardTime']) - time()) * 1000;
        return $getkuai3;
    }
}


?>