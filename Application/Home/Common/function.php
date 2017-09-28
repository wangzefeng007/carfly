<?php
use QL\QueryList;
header('content-type:text/html;charset=utf-8');
/*
 * 竞猜格式检测
 * */
function check_format($message)
{
    $data['error'] = 1;
    //车号大小单双(20-20,000)
    // 双/100 = 1~5车道买双各$100 = 总$500
//    if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(大|双|小|单){1}+\/{1}+\d+$/', $message)) {
//        $info = explode('/', $message);
//        if ($info[2] >= 10 && $info[2] <= 20000) {
//            $data['start'] = serialize(str_split($info[0]));
//            $data['points'] = $info[2] * strlen($info[0]);
//            $data['type'] = 1;
//        } else {
//            $data['error'] = 0;
//            $data['money'] = '20-20,000';
//        }
//    }
    if (preg_match('/^(大|双|小|单){1}+\/{1}+\d+$/', $message)){
        $info = explode('/', $message);
        if ($info[1] >= 10 && $info[1] <= 20000) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 1;
        } else {
            $data['error'] = 0;
            $data['money'] = '20-20,000';
        }
    }
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

    //龙虎(20-20,000)
    // 123/龙/100 = 1~3车道买龙各$100=总$300
    if (preg_match('/^(?![1-5]*?([1-5])[1-5]*?\1)[1-5]{1,5}+\/{1}+(龙|虎){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        if ($info[2] >= 10 && $info[2] <= 20000) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 4;
        } else {
            $data['error'] = 0;
            $data['money'] = '20-20,000';
        }
    }

    //冠亚庄闲(20-20,000)
    // 庄/200 = 冠军大于亚军即中奖
    if (preg_match('/^(庄|闲){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        if ($info[1] >= C('jinezx') && $info[1] <= 20000) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[1];
            $data['type'] = 5;
        } else {
            $data['error'] = 0;
            $data['money'] = '20-20,000';
        }
    }

    //冠亚号码(20-5,000)
    // 组/5-6/50 = 5号.6号车在冠亚军(顺序不限) = 总$50
    // 组/1-9.3-7/100 = 1.9号车或3.7号车在冠亚军(顺序不限) = 总$200
    if (preg_match('/^组\/{1}+([0-9]{1}-[0-9]{1}.)*([0-9]{1}-[0-9]{1})+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        if ($info[2] >= 10 && $info[2] <= 5000) {
            if (strlen($info[1]) > 3) {
                $info2 = explode('.', $info[1]);
                for ($i = 0; $i < count($info2); $i++) {
                    $info3[$i] = explode('-', $info2[$i]);
                    if ($info3[$i][0] == $info3[$i][1]) {
                        $res = 0;
                        return false;
                    } else {
                        $res = 1;
                    }
                    for ($a = 0; $a < $i - 1; $a++) {
                        if ($info2[$i] == $info2[$a]) {
                            $res = 0;
                            return false;
                        } else {
                            $res = 1;
                        }
                        $info3 = explode('-', $info2[$a]);
                        $info4 = $info3[1] . '-' . $info3[0];
                        if ($info2[$i] == $info4) {
                            $res = 0;
                            return false;
                        } else {
                            $res = 1;
                        }
                    }
                }
                if ($res == 1) {
                    $data['start'] = serialize($info2);
                    $data['points'] = $info[2] * count($info2);
                    $data['type'] = 6;
                }
            } else {
                $info1 = explode('-', $info[1]);
                if ($info1[0] != $info1[1]) {
                    $data['start'] = serialize(array('0' => $info[1]));
                    $data['points'] = $info[2];
                    $data['type'] = 6;
                }
            }
        } else {
            $data['error'] = 0;
            $data['money'] = '20-5,000';
        }
    }

    //特码大小单双(20-20,000)
    // 和双100 = 「冠亚和」的双$100
    if (preg_match('/^(和|特){1}(大|小|单|双){1}+\d+$/', $message)) {
        $info = substr($message, 6);
        if ($info >= 10 && $info <= 20000) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info;
            $data['type'] = 7;
        } else {
            $data['error'] = 0;
            $data['money'] = '20-20,000';
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

    return $data;
}

//-----------------------------------------------蛋28验证--------------------------------------------------------
//-----------------------------------------------蛋28验证--------------------------------------------------------
//-----------------------------------------------蛋28验证--------------------------------------------------------

function check_format_dan($message,$id,$room)
{   //查询档期的开奖期号
    $dankaijiangdata = getBj28();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    $where =array(
        'number' =>$dankaijiangqihao,
        'type'=>1,
        'state'=>1,
        'userid'=>$id,
    );
    $alljine =M('order')->where($where)->sum('del_points');
    $data['error'] = 1;
    //单、双、玩法  金额10~~20000     1：2   单/20
    if (preg_match('/^(大|小|双|单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];/*$info[0] 取不到大小单双的值 条件用不了  所以本期总金额取不到值*/
        $where  = array(
//            'number'=>'847399',
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype1 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        $jndxiazhujinetype1 = $jndxiazhujinetype1[0]['sum_points'];
        if ($info[1] >= C('jnd_jinezx'.$room) && $info[1]+$jndxiazhujinetype1 <= C('jnd_check_dx'.$room)&&$info[1]+$alljine < C('jnd_all_jine'.$room)) {
            $data['points'] = $info[1];
            $data['type'] = 1;
        } else {
            $data['error'] = 0;
            if (empty($jndxiazhujinetype1)){
                $jndxiazhujinetype1 =0;
            }
            $data['money'] = C('jnd_jinezx'.$room).'-'.C('jnd_check_dx'.$room).'本期压注总金额：'.$jndxiazhujinetype1.' 单局最高'.C('jnd_all_jine'.$room);
        }
    }
    //大单大双 小单小双   1：4    大单/20---------------------------------------------------------------------
    if (preg_match('/^(大双|大单|小双|小单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>2,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype2 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        if ($info[1] >= C('jnd_jinezx'.$room) && $info[1]+$jndxiazhujinetype2 <= C('jnd_check_zh'.$room)&&$info[1]+$alljine < C('jnd_all_jine'.$room)) {
            $data['points'] = $info[1];
            $data['type'] = 2;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx'.$room).'-'.C('jnd_check_zh'.$room).'本期压注总金额：'.$jndxiazhujinetype2;
        }
    }
    //极大 极小 特码 1：12     极小/20---------------------------------------------------
    if (preg_match('/^(极大|极小){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype3 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        if ($info[1] >= C('jnd_jinezx'.$room) && $info[1]+$jndxiazhujinetype3 <= C('jnd_check_tm'.$room)&&$info[1]+$alljine < C('jnd_all_jine'.$room)) {
            $data['points'] = $info[1];
            $data['type'] = 3;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx'.$room).'-'.C('jnd_check_tm'.$room).'本期压注总金额：'.$jndxiazhujinetype3.'单局最高'.C('jnd_all_jine'.$room);
        }
    }
    //和
    if (preg_match('/^\d{1,2}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>4,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype4 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        $alln = $info[0];
        if ($alln <= 27) {
            if ($info[1] >= C('jnd_jinezx'.$room) && $info[1]+$jndxiazhujinetype4 <= C('jnd_check_tm'.$room)&&$info[1]+$alljine < C('jnd_all_jine'.$room)) {
                $data['points'] = $info[1];
                $data['type'] = 4;
            }else{
                $data['error'] = 0;
                $data['money'] =  C('jnd_jinezx'.$room).'-'.C('jnd_check_tm'.$room).'本期压注总金额：'.$jndxiazhujinetype4.'单局最高'.C('jnd_all_jine'.$room);
            }

        } else {
            $data['error'] = 0;
            $data['money'] =  C('jnd_jinezx'.$room).'-'.C('jnd_check_tm'.$room).'本期压注总金额：'.$jndxiazhujinetype4.'单局最高'.C('jnd_all_jine');
        }

    }

    //豹子判断   999/70
    if (preg_match('/^(豹子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>5,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype5 <= C('jnd_check_bz')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 5;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_bz').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C('jnd_all_jine');
        }
    }
    // 顺子     123/20
    if (preg_match('/^(顺子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>6,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype6 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype6 <= C('jnd_check_sz')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 6;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_sz').'本期压注总金额：'.$jndxiazhujinetype6.'单局最高'.C('jnd_all_jine');
        }
    }
//    if (preg_match('/^(大|小){1}+\/{1}+\d+$/', $message)) {
//        $info = explode('/', $message);
//        $chaxuntiaojian = $info[0];
//        $where  = array(
//            'number'=>$dankaijiangqihao,
//            'type'=>7,
//            'state'=>1,
//            'userid'=>$id,
//            'jincai'=> array('like', "%$chaxuntiaojian%"),
//        );
//        $jndxiazhujinetype1 =M('order')->where($where)->sum('del_points');
//        if ($info[1] >= C('jnd_jinezx'.$room) && $info[1]+$jndxiazhujinetype1 <= C('jnd_check_dx'.$room)&&$info[1]+$alljine < C('jnd_all_jine'.$room)) {
//            $data['points'] = $info[1];
//            $data['type'] = 7;
//        } else {
//            $data['error'] = 0;
//            $data['money'] = C('jnd_jinezx'.$room).'-'.C('jnd_check_dx'.$room).'本期压注总金额：'.$jndxiazhujinetype1.'单局最高'.C('jnd_all_jine'.$room);
//        }
//    }
    return $data;
}







//-----------------------------------------------jnd28验证--------------------------------------------------------
//-----------------------------------------------jnd28验证--------------------------------------------------------
//-----------------------------------------------jnd28验证--------------------------------------------------------


function check_format_jnd($message,$id,$room)
{
    $dankaijiangdata = getJnd28();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    //当局总金额
    $wheres =array(
        'number' =>$dankaijiangqihao,
        'type'=>1,
        'state'=>1,
        'userid'=>$id,
    );
    $alljine =M('order')->where($wheres)->sum('del_points');
    $data['error'] = 1;
    //单、双、玩法  金额10~~20000     1：2   单/20
    if (preg_match('/^(大|小|双|单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype1 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        if ($info[1] >= C('jnd_jinezx'.$room) && $info[1]+$jndxiazhujinetype1 <= C('jnd_check_dx'.$room)&&$info[1]+$alljine < C('jnd_all_jine'.$room)) {
            $data['points'] = $info[1];
            $data['type'] = 1;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx'.$room).'-'.C('jnd_check_dx'.$room).'本期压注总金额：'.$jndxiazhujinetype1.'单局最高'.C('jnd_all_jine'.$room);
        }
    }
    //大单大双 小单小双   1：4    大单/20---------------------------------------------------------------------
    if (preg_match('/^(大双|大单|小双|小单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>2,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype2 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        if ($info[1] >= C('jnd_jinezx'.$room) && $info[1]+$jndxiazhujinetype2 <= C('jnd_check_zh'.$room)&&$info[1]+$alljine < C('jnd_all_jine'.$room)) {
            $data['points'] = $info[1];
            $data['type'] = 2;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx'.$room).'-'.C('jnd_check_zh'.$room).'本期压注总金额：'.$jndxiazhujinetype2;
        }
    }
    //极大 极小 特码 1：12     极小/20---------------------------------------------------
    if (preg_match('/^(极大|极小){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype3 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        if ($info[1] >= C('jnd_jinezx'.$room) && $info[1]+$jndxiazhujinetype3 <= C('jnd_check_tm'.$room)&&$info[1]+$alljine < C('jnd_all_jine'.$room)) {
            $data['points'] = $info[1];
            $data['type'] = 3;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx'.$room).'-'.C('jnd_check_tm'.$room).'本期压注总金额：'.$jndxiazhujinetype3.'单局最高'.C('jnd_all_jine'.$room);
        }
    }
    //和
    if (preg_match('/^\d{1,2}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>4,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype4 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        $alln = $info[0];
        if ($alln <= 27) {
            if ($info[1] >= C('jnd_jinezx'.$room) && $info[1]+$jndxiazhujinetype4 <= C('jnd_check_tm'.$room)&&$info[1]+$alljine < C('jnd_all_jine'.$room)) {
                $data['points'] = $info[1];
                $data['type'] = 4;
            }else{
                $data['error'] = 0;
                $data['money'] =  C('jnd_jinezx'.$room).'-'.C('jnd_check_tm'.$room).'本期压注总金额：'.$jndxiazhujinetype4.'单局最高'.C('jnd_all_jine'.$room);
            }

        } else {
            $data['error'] = 0;
            $data['money'] =  C('jnd_jinezx'.$room).'-'.C('jnd_check_tm'.$room).'本期压注总金额：'.$jndxiazhujinetype4.'单局最高'.C('jnd_all_jine');
        }

    }

    //豹子判断   999/70
    if (preg_match('/^(豹子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>5,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype5 <= C('jnd_check_bz')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 5;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_bz').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C('jnd_all_jine');
        }
    }
    // 顺子     123/20
    if (preg_match('/^(顺子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>6,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "$chaxuntiaojian%"),
        );
        $jndxiazhujinetype6 =M('order')->query("SELECT SUM(del_points) AS sum_points FROM think_order WHERE  number = $dankaijiangqihao and type= 1 and state = 1 and userid = $id and jincai like '$chaxuntiaojian%'");
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype6 <= C('jnd_check_sz')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 6;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_sz').'本期压注总金额：'.$jndxiazhujinetype6.'单局最高'.C('jnd_all_jine');
        }
    }
//    if (preg_match('/^(大|小){1}+\/{1}+\d+$/', $message)) {
//        $info = explode('/', $message);
//        $chaxuntiaojian = $info[0];
//        $where  = array(
//            'number'=>$dankaijiangqihao,
//            'type'=>7,
//            'state'=>1,
//            'userid'=>$id,
//            'jincai'=> array('like', "%$chaxuntiaojian%"),
//        );
//        $jndxiazhujinetype1 =M('order')->where($where)->sum('del_points');
//        if ($info[1] >= C('jnd_jinezx'.$room) && $info[1]+$jndxiazhujinetype1 <= C('jnd_check_dx'.$room)&&$info[1]+$alljine < C('jnd_all_jine'.$room)) {
//            $data['points'] = $info[1];
//            $data['type'] = 7;
//        } else {
//            $data['error'] = 0;
//            $data['money'] = C('jnd_jinezx'.$room).'-'.C('jnd_check_dx'.$room).'本期压注总金额：'.$jndxiazhujinetype1.'单局最高'.C('jnd_all_jine'.$room);
//        }
//    }
    return $data;
}











//-----------------------------------------------时时彩验证--------------------------------------------------------
function  check_format_ssc($message,$id){

    $dankaijiangdata = getssc();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    //当局总金额
    $wheres =array(
        'number' =>$dankaijiangqihao,
        'type'=>1,
        'state'=>1,
        'userid'=>$id,
    );
    //当期的总金额
    $alljine =M('order')->where($wheres)->sum('del_points');
    //------------时时彩的开始------------------------------------------------------------------------------------------
    $data['error'] = 1;
    //1/单/600
    if (preg_match('/^\d{1}+\/{1}+(大|双|小|单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
        if ($info[0]<=5){
        if ($info[2] >= C('ssc_zuidi') && $info[2]+$sscxiazhujinetype1 <= C('ssc_fd_dxds') &&$info[2]+$sscxiazhujinetype1<=C('ssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2];
            $data['type'] = 1;
        } else {
            $data['error'] = 0;
            $data['money'] = C('ssc_zuidi').'-'.C('ssc_fd_dxds').'本期压注总金额：'.$sscxiazhujinetype1.'单局最高'.C('ssc_djzg');
         }
        }else{
            $data['error'] = 1;
        }
    }
    //1/大单/600
    if (preg_match('/^\d{1}+\/{1}+(大单|小单|大双|小双){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>2,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype2 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
        if ($info[0]<=5){
        if ($info[2] >= C('ssc_zuidi') &&  $info[2]+$sscxiazhujinetype2 <= C('ssc_fd_zuhe') &&$info[2]+$sscxiazhujinetype2<=C('ssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 2;
        } else {
            $data['error'] = 0;
            $data['money'] = C('ssc_zuidi').'-'.C('ssc_bl_zuhe').'本期压注总金额：'.$sscxiazhujinetype1.'单局最高'.C('ssc_djzg');
        }
        }else{
            $data['error'] = 1;
        }
    }
    //1/3/600
    if (preg_match('/^\d{1}+\/{1}+\d{1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);

        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
        );
        $sscxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
        if ($info[0]<=5){
            if ($info[2] >= C('ssc_zuidi') &&$info[2]+$sscxiazhujinetype3 <= C('ssc_fd_sum') &&$info[2]+$sscxiazhujinetype3<C('ssc_djzg')) {
                $data['start'] = serialize(str_split($info[0]));
                $data['points'] = $info[2] * strlen($info[0]);
                $data['type'] = 3;
            } else {
                $data['error'] = 0;
                $data['money'] = C('ssc_zuidi').'-'.C('ssc_fd_sum').'本期压注总金额：'.$sscxiazhujinetype1.'单局最高'.C('ssc_djzg');
            }
        }else{
            $data['error'] = 1;
        }
    }
    return $data;
}
//--------------------------------快3验证------------------------
function check_format_kuai3($message,$id)
{
    $dankaijiangdata = getkuai3();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    //当局总金额
    $wheres =array(
        'number' =>$dankaijiangqihao,
        'type'=>1,
        'state'=>1,
        'userid'=>$id,
    );
    $alljine =M('order')->where($wheres)->sum('del_points');
    $data['error'] = 1;

    // 数字的和
    if (preg_match('/^\d{1,2}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype4 =M('order')->where($where)->sum('del_points');
        $alln = $info[0];
        if ($alln <= 18 && $alln>3) {
            if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype4 <= C('jnd_check_tm')&&$info[1]+$alljine < C('jnd_all_jine')) {
                $data['points'] = $info[1];
                $data['type'] = 1;
            }else{
                $data['error'] = 0;
                $data['money'] =  C('jnd_jinezx').'-'.C('jnd_check_tm').'本期压注总金额：'.$jndxiazhujinetype4.'单局最高'.C('jnd_all_jine');
            }

        } else {
            $data['error'] = 1;

        }

    }
    //三不同
    if (preg_match('/^(三不同){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>2,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype5 <= C('jnd_check_bz')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 2;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_bz').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C('jnd_all_jine');
        }
    }
    //三同号
    if (preg_match('/^(三同号){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype5 <= C('jnd_check_bz')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 3;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_bz').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C('jnd_all_jine');
        }
    }
    //三连号
    if (preg_match('/^(三连号){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>4,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype5 <= C('jnd_check_bz')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 4;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_bz').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C('jnd_all_jine');
        }
    }
    //二相同
    if (preg_match('/^(二相同){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>5,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype5 <= C('jnd_check_bz')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 5;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_bz').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C('jnd_all_jine');
        }
    }
    //二不同
    if (preg_match('/^(二不同){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>6,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype5 <= C('jnd_check_bz')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 6;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_bz').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C('jnd_all_jine');
        }
    }
    return $data;
}

function curl_https($url, $data = array(), $header = array(), $timeout = 30)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $response = curl_exec($ch);
    if ($error = curl_error($ch)) {
        die($error);
    }
    curl_close($ch);
    return $response;

}
//php异步操作不关心返回值，直接进行下一步
function _sock($url) {
    $host = parse_url($url,PHP_URL_HOST);
    $port = parse_url($url,PHP_URL_PORT);
    $port = $port ? $port : 80;
    $scheme = parse_url($url,PHP_URL_SCHEME);
    $path = parse_url($url,PHP_URL_PATH);
    $query = parse_url($url,PHP_URL_QUERY);
    if($query) $path .= '?'.$query;
    if($scheme == 'https') {
        $host = 'ssl://'.$host;
    }
    $fp = fsockopen($host,$port,$error_code,$error_msg,1);
    if(!$fp) {
        return array('error_code' => $error_code,'error_msg' => $error_msg);
    }
    else {
        stream_set_blocking($fp,true);//开启了手册上说的非阻塞模式
        stream_set_timeout($fp,1);//设置超时
        $header = "GET $path HTTP/1.1\r\n";
        $header.="Host: $host\r\n";
        $header.="Connection: close\r\n\r\n";//长连接关闭
        fwrite($fp, $header);
        usleep(1000); // 这一句也是关键，如果没有这延时，可能在nginx服务器上就无法执行成功
        fclose($fp);
        return array('error_code' => 0);
    }
}
function updata(){
    _sock('http://car.com/home/api/update');
}
?>