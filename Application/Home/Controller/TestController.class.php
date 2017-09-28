<?php
namespace Home\Controller;

use QL\QueryList;
use Think\Controller;
header('content-type:text/html;charset=utf-8');

class TestController extends Controller
{
    public function index()
    {
        $money = M('order')->where(array('number'=>843820,'userid'=>72))->sum('del_points');var_dump($money);exit;
        $data = QueryList::Query('http://www.pc6777.com/jnd28/', array(
            'time' => array('script:eq(2)', 'text'),
            "currentqihao" => array('.kj_white_line:eq(1)', 'text'),
        ))->data;
        //获取时间
        $str = $data[0]['time'];
        $result = array();
        preg_match_all("/(?:\()(.*)(?:\))/i", $str, $result);
        $allnum = $result[1][0];
        $resdata = explode(',', $allnum);
        $all['time'] = $resdata[0];
        //当前期号
        $nowqihaoalldata = explode("u00a0", $data[0]['currentqihao']);
        $nowqihao1 = explode(' ', $nowqihaoalldata[0]);
        $nowqihao2 = explode("期", $nowqihao1[0]);
        dump($nowqihao2[1]);

        //计算下一期的开奖时间
        $qianmian = explode(']', $nowqihao2[1]);
        $riqi = explode('[', $qianmian[0]);
        $fariqi = $riqi[1];
        $awar = date("Y-m-d");
        $shijianchuo = strtotime("$awar" . "$fariqi") + 200;
        $zhuanhuaderiqi = date('Y:m:d H:i:s', $shijianchuo);

        $all['currentqihao'] = $nowqihao2[0];
        //当前号码
        $nowhaoma = explode("]", $nowqihao1[0]);
        $nowhaomaarr = explode('+', $nowhaoma[1]);
        $testes = json_encode($nowhaomaarr[0]);
        $afa = explode('u00a0', $testes);
        $jjkd = preg_replace('/\D/s', '', $afa[2]);
        $nowhaomaarr1 = $jjkd;
        $nowhaomaarr2 = $nowhaomaarr[1];
        $nowhaomaarr3 = $nowhaomaarr[2];
        $all['currentnumber'] = $nowhaomaarr1 . ',' . $nowhaomaarr2 . ',' . $nowhaomaarr3;
        //下一期
        $all['nextqihao'] = $resdata[1];
        $all['kaijiangshijain'] = 11;
        $all = json_encode($all);
//        return $all;
        echo $all;


    }

    public function number($str)
    {
        return preg_replace('/\D/s', '', $str);
    }

    public function dd($jj)
    {
        echo $jj;
    }

    public function f3($str)
    {
        $result = array();
        preg_match_all("/(?:\()(.*)(?:\))/i", $str, $result);

        return $result[1][0];
    }

    public function test()
    {
        $time = time();
        $olddate = strtotime('-1 days');
        $map['time'] = array('between', "$olddate,$time");
        $res = M('order')->where($map)->field('sum(add_points),userid,sum(type = 2)as zuhetype,sum(type = 2)/count(userid) as zuhebili,sum(del_points),count(userid) as count,sum(del_points)-sum(add_points) as del_data')->group('userid')->select();
        $mycount = count($res);
        dump($res);
        for ($i = 0; $i < $mycount; $i++) {
            //判断是否为大于是十把，&&判断组合比例要大于20%，//小单，大双组合超过75%没有返水。
            if ($res[$i]['count'] >= 10 && $res[$i]['zuhebili'] > 0.2) {
                $data['userid'] = $res[$i]['userid'];
                $data['shuying'] = $res[$i]['del_data'];
                $data['time'] = time();
                $headurldata = M('user')->where(array("id" => $res[$i]['userid']))->select();
                $data['headimgurl'] = $headurldata[0]['headimgurl'];
                $data['nickname'] = $headurldata[0]['nickname'];
                if ($res[$i]['del_data'] >= 2000 && $res[$i]['del_data'] <= 10000) {
                    $fanshuidata = $res[$i]['del_data'] * 0.1;
                    $data['fanshui'] = $fanshuidata;
                    $adduserdata = M('user')->where(array("id" => $res[$i]['userid']))->setInc('points', $fanshuidata);
                    if ($adduserdata) {
                        M('order_day')->add($data);
                    }
                }
                if ($res[$i]['del_data'] >= 10001 && $res[$i]['del_data'] <= 30000) {
                    $fanshuidata = $res[$i]['del_data'] * 0.12;
                    $data['fanshui'] = $fanshuidata;
                    $adduserdata = M('user')->where(array("id" => $res[$i]['userid']))->setInc('points', $fanshuidata);
                    if ($adduserdata) {
                        M('order_day')->add($data);
                    }
                }
                if ($res[$i]['del_data'] >= 30001) {
                    $fanshuidata = $res[$i]['del_data'] * 0.15;
                    $data['fanshui'] = $fanshuidata;
                    $adduserdata = M('user')->where(array("id" => $res[$i]['userid']))->setInc('points', $fanshuidata);
                    if ($adduserdata) {
                        M('order_day')->add($data);
                    }
                }
            }
        }
    }

    public function test2()
    {
        //昨天凌晨的时间戳
        $ago = strtotime('-1 day 00:00:00');
        echo date('Y:m:d H:i:s', $ago);
        echo "<br>";
        //今天凌晨的时间戳
        $today = strtotime(date("Y-m-d"));
        echo date("Y:m:d H:i:s", $today);
    }

    public function caipiaokong()
    {
        $result = S('jjdata');
        if (empty($result)) {
            $url = "http://api.kaijiangtong.com/lottery/?name=jndklb&format=json3&uid=789423&token=1cd714ebb2c93a811fba7533a30d28fed7ccb7e1&num=1";
            $result = curlGet($url);
            S('jjdata', $result, 5);
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
        dump($jnddata);
    }

    public function kuaishizhuanhua()
    {
        $url = 'https://www.1399klc.com/lottery/ajax?lotterycode=canada';
        $data = array('name' => 'fdipzone');
        $header = array();
        $response = curl_https($url, $data, $header, 5);
        $result = S('Jnddata');
        if (empty($result)) {
            $result = $response;
            S('Jnddata', $result, 5);
        }
        $data = json_decode($result, true);
        //获取开奖号码：
        $haoma = explode(',', $data['result']);
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
        $jnddata['current']['periodNumber'] = $data['period'];
        $jnddata['current']['awardTime'] = $data['awardTime'];
        $jnddata['current']['awardNumbers'] = $number1 . ',' . $number2 . ',' . $number3;
        $jnddata['next']['periodNumber'] = $data['next_period'];
        $jnddata['next']['awardTime'] = $data['next_awardTime'];
        $jnddata['next']['awardTimeInterval'] = (strtotime($data['next_awardTime']) - time()) * 1000;
        $jnddata['next']['delayTimeInterval'] = 0;
        //返回api接口。
        return $jnddata;
    }

    public function test3()
    {
        $ago = strtotime('-1 day 00:00:00');
        $data['order_time'] = $ago;
        M('order_day')->add($data);
    }

    public function getbjdata()
    {

        $url = "http://api.1680210.com/LuckTwenty/getBaseLuckTewnty.do?issue=&lotCode=10014";
        $result = curlGet($url);
        S('klbjdata', $result, 5);
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
       return $klbj28data;
//            $n1 = $haoma['1'] + $haoma['4'] + $haoma['7']+$haoma['10']+$haoma['13']+$haoma['16'];
//            $n2 = $haoma['2'] + $haoma['5'] + $haoma['8']+$haoma['11']+$haoma['14']+$haoma['17'];
//            $n3 = $haoma['3'] + $haoma['6'] + $haoma['9']+$haoma['12']+$haoma['15']+$haoma['18'];
//            $num1 = str_split($n1);
//            $num2 = str_split($n2);
//            $num3 = str_split($n3);
//            $number1 =  $num1[count($num1)-1];
//            $number2 =  $num2[count($num2)-1];
//            $number3 =  $num3[count($num3)-1];
//            //传输的数据名称：
//            $jnddata['time'] = time();
//            $jnddata['game'] = 'jnd28';
//            $jnddata['current']['periodNumber'] = $data['period'];
//            $jnddata['current']['awardTime'] = $data['awardTime'];
//            $jnddata['current']['awardNumbers'] = $number1.','.$number2.','.$number3;
//            $jnddata['next']['periodNumber'] = $data['next_period'];
//            $jnddata['next']['awardTime'] = $data['next_awardTime'];
//            $jnddata['next']['awardTimeInterval'] = (strtotime($data['next_awardTime']) - time()) * 1000;
//            $jnddata['next']['delayTimeInterval'] = 0;
//            //返回api接口。
//            print_r($jnddata);
    }
    public function duizi(){
        $n1 = 6;
        $n2 = 6;
        $n3 = 6;
        $duizinum = 0;
        if ($n1 ==$n2){
            $duizinum=$duizinum+1;
        }
        if ($n1 ==$n3){
            $duizinum=$duizinum+1;
        }
        if($n2 ==$n3){
            $duizinum =$duizinum+1;
        }
        if($duizinum ==0){
            echo '非豹子，非顺子';
        }
       if($duizinum == 1){
            echo "对子";
       }
       if($duizinum ==3){
           echo '豹子';
       }
    }
    public function ceshi(){
//        $type='update';
//        $bj28_datas = getBj28($type);
//        $res = M('dannumber')->where("periodnumber = {$bj28_datas['current']['periodNumber']}")->find();
//var_dump($res);




//        北京28
        $c=S('newcachebj28');
//        var_dump($c);
        $type='update';
        $pkdata = getBj28($type);
        $type = 'update';
        $bj28_datas = getBj28($type);
//        var_dump($pkdata);
//var_dump( F('get28data',null));
//var_dump( F('get28data',S('newcachebj8')));
var_dump( S('newcachebj28'));
//var_dump( F('get28data'));
//var_dump( getBj28($type));
//        加拿大28
//        $b=F('get28data',null);
//        $b=F('get28data');
//        var_dump($b);
        $f=F('id_dansend');
        //        加拿大28
        $bb= F('get28data');
//        var_dump($bb);
//        $a = S('klbjdata',NULL);
//       $a = S('jnd28data');var_dump($a);

        $cc=F('cachejnd');//1506584190 加拿大期数  差距较大
//        dump($cc);
        echo '<br>';
        $e=F('bj28_periodNumber');//843019 北京期数  差距较大
//        var_dump($e);
        echo '<br>';

        $d=F('getbj28data');//错误的北京28开奖信息 差距100多期
//        var_dump($d);


        exit;






        echo '<br>';














        $res_points = M('user')->query("SELECT SUM(points) FROM think_user WHERE id = 72;");var_dump($res_points);
        $message ='大/123';
        $room=1;
        if (preg_match('/^(大|小|双|单){1}+\/{1}+\d+$/', $message)) {
            $info = explode('/', $message);
            $chaxuntiaojian = $info[0];/*$info[0] 取不到大小单双的值 条件用不了  所以本期总金额取不到值*/
            $where  = array(
            'number'=>'847399',
//                'number'=>$dankaijiangqihao,
                'type'=>1,
                'state'=>1,
                'userid'=>72,
                'jincai'=> array('like', "$chaxuntiaojian%"),
            );
            $jndxiazhujinetype1 =M('order')->where($where)->sum('del_points');
            if ($info[1] >= C('jnd_jinezx'.$room) && $info[1]+$jndxiazhujinetype1 <= C('jnd_check_dx'.$room)&&$info[1]+$alljine < C('jnd_all_jine'.$room)) {
                $data['points'] = $info[1];
                $data['type'] = 1;
            } else {
                $data['error'] = 0;
                $data['money'] = C('jnd_jinezx'.$room).'-'.C('jnd_check_dx'.$room).'本期北京28压注总金额：'.$jndxiazhujinetype1.'单局最高'.C('jnd_all_jine'.$room);
            }
        }
    }
    public function group(){
//        $map['time'] = array('between', "$olddate,$time");
        $res = M('order')->field('sum(add_points),userid,sum(type = 2)as zuhetype,sum(type = 2)/count(userid) as zuhebili,sum(del_points),count(userid) as count,sum(del_points)-sum(add_points) as del_data')->group('userid')->select();
        dump($res);
    }
    public function cache(){
        dump(F('kuai3_status'));
    }
    public function test1(){
        $sum = '7,6,7';
        $arr = explode(',',$sum);
        $n1 = $arr[0];
        $n2 = $arr[1];
        $n3 = $arr[2];
        if($n1 ==$n2 &&$n1 !==$n3){
            echo $n1 .'='.$n2;
        }elseif ($n1 ==$n3 &&$n1 !==$n2 ){
            echo $n1 .'='.$n3;
        }elseif ($n1 !==$n2 && $n1!==$n3 &&$n2 ==$n3){
            echo $n2 .'='.$n3;
        }
    }
    public function test5(){
   F('kuai3_periodNumber',0);
    }
    public function showtest1(){
    }
    public function test4(){
//      dump(F('fff'));
        echo "k0";
    }


//测试金额检查
    public function check_format()
    {
        header("Content-type: text/html; charset=utf-8");
        $message = '双/100';
        $data['error'] = 1;
        //车号大小单双(20-20,000)
        // 双/100 = 1~5车道买双各$100 = 总$500
        if (preg_match('/^(大|双|小|单){1}+\/{1}+\d+$/', $message)){
            $info = explode('/', $message);var_dump($info);
            if ($info[1] >= 10 && $info[1] <= 20000) {
                $data['start'] = serialize(str_split($info[0]));
                $data['points'] = $info[2] * strlen($info[0]);
                $data['type'] = 1;
            } else {
                $data['error'] = 0;
                $data['money'] = '20-20,000';
            }
        }
        var_dump($data);
        //车号(20-20,000)
        // 12345/89/20 = 1~5车道的8号、9号各买$20 = 总$200
        if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+\d+$/', $message)) {
            $info = explode('/', $message);
            if ($info[2] >= 10 && $info[2] <= 20000) {
                $data['start'] = serialize(str_split($info[0]));
                $data['points'] = $info[2] * strlen($info[0]) * strlen($info[1]);
                $data['type'] = 3;
            } else {
                $data['error'] = 0;
                $data['money'] = '20-20,000';
            }
        }

        //组合(20-10,000)
        // 890/大单/50 = 8.9.10车道大单各买$50 = 总$150
        if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(大单|小双|小单|大双){1}+\/{1}+\d+$/', $message)) {
            $info = explode('/', $message);
            if ($info[2] >= 10 && $info[2] <= 10000) {
                $data['start'] = serialize(str_split($info[0]));
                $data['points'] = $info[2] * strlen($info[0]);
                $data['type'] = 2;
            } else {
                $data['error'] = 0;
                $data['money'] = '20-10,000';
            }
        }

        //特码数字
        // 3.4.18.19，含本42倍，限额20-1,000
        // 5.6.16.17，含本21倍，限额20-2,000
        // 7.8.14.15，含本14倍，限额20-3,000
        // 9.10.12.13，含本10倍，限额20-4,000
        // 11，含本8倍，限额20-5,000
        // 和5.6.7/100 = 竞猜「冠亚和」的值为5或6或7各$100 = 总$300
        if (preg_match('/^(和|特){1}(([3-9]|1[0-9]).)*([3-9]|1[0-9])+\/{1}+\d+$/', $message)) {
            $info = explode('/', $message);
            $start = substr($info[0], 3);
            if ($info[1] >= C('jinezx')) {
                if (strlen($start) > 1) {
                    $res = explode('.', $start);
                    if (count($res) == count(array_unique($res))) {
                        $data['start'] = serialize(str_split(substr($info[0], 3)));
                        $data['points'] = $info[1] * count($res);
                        $data['type'] = 8;
                    }
                } else {
                    $data['start'] = serialize(str_split(substr($info[0], 3)));
                    $data['points'] = $info[1];
                    $data['type'] = 8;
                }
            } else {
                $data['error'] = 0;
                $data['money'] = '20-5,000';
            }
        }

       var_dump($data);
    }



}