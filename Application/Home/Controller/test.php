<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/23
 * Time: 16:22
 */
function check_format_jnd1($message,$id)
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
    if (preg_match('/^(双|单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype1 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype1 <= C('jnd_check_dx')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 1;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_dx').'本期压注总金额：'.$jndxiazhujinetype1.'单局最高'.C('jnd_all_jine');
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
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype2 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype2 <= C('jnd_check_dxds')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 2;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_dxds').'本期压注总金额：'.$jndxiazhujinetype2;
        }
    }
    //极大 极小  1：12     极小/20---------------------------------------------------
    if (preg_match('/^(极大|极小){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype3 <= C('jnd_check_jz')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 3;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_jz').'本期压注总金额：'.$jndxiazhujinetype3.'单局最高'.C('jnd_all_jine');
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
            'jincai'=> array('like', "%$chaxuntiaojian/%"),
        );
        $jndxiazhujinetype4 =M('order')->where($where)->sum('del_points');
        $alln = $info[0];
        if ($alln <= 27) {
            if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype4 <= C('jnd_check_hezhi')&&$info[1]+$alljine < C('jnd_all_jine')) {
                $data['points'] = $info[1];
                $data['type'] = 4;
            }else{
                $data['error'] = 0;
                $data['money'] =  C('jnd_jinezx').'-'.C('jnd_check_hezhi').'本期压注总金额：'.$jndxiazhujinetype4.'单局最高'.C('jnd_all_jine');
            }

        } else {
            $data['error'] = 0;
            $data['money'] =  C('jnd_jinezx').'-'.C('jnd_check_hezhi').'本期压注总金额：'.$jndxiazhujinetype4.'单局最高'.C('jnd_all_jine');
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
    // 顺子     123/20
    if (preg_match('/^(顺子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>6,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype6 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype6 <= C('jnd_check_sz')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 6;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_sz').'本期压注总金额：'.$jndxiazhujinetype6.'单局最高'.C('jnd_all_jine');
        }
    }
    if (preg_match('/^(大|小){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>7,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype1 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C('jnd_jinezx') && $info[1]+$jndxiazhujinetype1 <= C('jnd_check_dx')&&$info[1]+$alljine < C('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 7;
        } else {
            $data['error'] = 0;
            $data['money'] = C('jnd_jinezx').'-'.C('jnd_check_dx').'本期压注总金额：'.$jndxiazhujinetype1.'单局最高'.C('jnd_all_jine');
        }
    }
    return $data;
}