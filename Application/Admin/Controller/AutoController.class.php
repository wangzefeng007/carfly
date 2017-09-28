<?php

namespace Admin\Controller;
use Think\Controller;
class AutoController extends Controller
{
    public function index()
    {
        set_time_limit(0);
        if (IS_AJAX){
            $this->kuai3();
            $this->jnd28js();
            $this->Bj28js();
            $this->Bjpk10();
            $this->ssc();
        }else{
            $data = $_SERVER["SERVER_NAME"] ;
            $this->assign('severname',$data);
            $this->display();
        }
    }
    public function update(){
        if (IS_AJAX){
        getkuai3('update');
        getssc('update');
        getJnd28('update');
        getBj28('update');
        getPK10('update');
        }else{
        $this->display();
        $data = $_SERVER["SERVER_NAME"] ;
        $this->assign('severname',$data);
        }
    }
    public function jnd28js()
    {
        $value = S('jiesuan');

        if (empty($value)) {
            S('jiesuan', "1", 10);
        } else {
            return false;
        }
        //自动结算之前没结算的//查找状态为 is_add = 0 的标识没有记录的，is_add为存入，但是没有处理的。
        $list = M('order')->where(array("state" => 1, "is_add" => 0))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('dannumber')->where(array("game" => 'Jnd28', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                //获取当前的开奖号码
                $number1 = explode(',', $current_number['awardnumbers']);
                //获取当前号码开奖的单双情况
                $number['bz'] = $current_number['bz'];
                $number['dx'] = $current_number['dx'];
                $number['ds'] = $current_number['danshuang'];
                $number['dxds'] = $current_number['dxds'];
                $number['jz'] = $current_number['jz'];
                $number['zonghe'] = $current_number['zonghe'];
                //当期的总金额
                $dangqianqihao = $current_number['periodnumber'];
                $dangqianalldata = M('order')->where('number',$dangqianqihao)->where('userid',$userid)->sum('del_points');
                //当期的赔付的总金额，是在结算这每一单的时候前的总金额
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                switch ($list[$i]['type']) {
                    //第一种为单双判断     单/20    如果判断正确   *   倍数；  ---------- 测试成功-------------------------------------------
                    //第一种为单双判断     单/20    如果判断正确
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '单' || $start1[0] == '双') {
                            //如果这局不是等于13 ， 14 那么就按照正常的程序去走
                            if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                //如果是豹子
                                if ($number['bz'] == "豹子") {
                                    if ($number['bz'] == "豹子") {
                                        $points1 = $start1[1] * 1;
                                        $res1 = $this->add_points($id, $userid, $points1);
                                    } else {
                                        $res1 = $this->del_points($id);
                                    }
                                } else {
                                    //如果不是豹子
                                    if ($number['ds'] == $start1[0]) {
                                        $points1 = $start1[1] * C('jnd_dx');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                    } else {
                                        $res1 = $this->del_points($id);
                                    }
                                }
                                //如果这局开的是13 或者14 那么就按照13 ，14 处理
                            } else {
                                if ($number['ds'] == $start1[0]) {
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    //当局的总金额

                                    if ($dangqianalldata <= C('jnd_ds_jq_1')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_x1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);

                                    }
                                    if ($dangqianalldata > C('jnd_ds_jq_1') && $dangqianalldata < C('jnd_ds_jq_2')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {
                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_2') && $dangqianalldata < C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_2_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_3_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }

                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            }
                        }
                        break;
                    //type = 2 时 ， 判断大小单双。 // ------------------测试成功------------------------------------
                    case 2:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '大单' || $start1[0] == '大双' || $start1[0] == '小单' || $start1[0] == '小双') {
                            if ($number['dxds'] == $start1[0]) {
                                //判断是不是13 ， 或者14
                                //分算法
                                switch (C('jnd_dxds_swich')) {
                                    case 1:
                                        //同一算法 13 14 特别
                                        if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                            //如果开奖为豹子
                                            if($number['bz'] == "豹子"){
                                                $points1 = $start1[1] * 1;
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }else{
                                                //如果不是为豹子.
                                                $points1 = $start1[1] * C('jnd_dxds');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }

                                        } else {
                                            //如果用户投的是  大小单双正确，且是综合为13,14的就按照特殊情况处理
                                            $points1 = $start1[1] * C('jnd_dxds_13_14');
                                            $res1 = $this->add_points($id, $userid, $points1);
                                            if ($res1) {

                                            }
                                        }
                                        break;
                                    case 2:
                                            //第二种算法，看金额的大小去计算倍率
                                        if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                            if($start1[0] == '大双' || $start1[0] == '小单'){
                                                $points1 = $start1[1] * C('jnd_dxds_dsxd');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }
                                            if($start1[0] == '小双'|| $start1[0] == '大单'){
                                                $points1 = $start1[1] * C('jnd_dxds_xsdd');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }
                                        } else {
                                            //如果开的值为13,14的时候，后台设置，赔率为多少。
                                            if ($dangqianalldata <= C('jnd_dxds_jq_1')) {
                                                $points1 = $start1[1] * C('jnd_dxds_jq_x1_bl');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }
                                            if ($dangqianalldata > C('jnd_dxds_jq_1') && $dangqianalldata < C('jnd_dxds_jq_2')) {
                                                $points1 = $start1[1] * C('jnd_dxds_jq_1_bl');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }
                                            if ($dangqianalldata > C('jnd_dxds_jq_2') && $dangqianalldata < C('jnd_dxds_jq_3')) {
                                                $points1 = $start1[1] * C('jnd_dxds_jq_3_bl');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }
                                            if ($dangqianalldata > C('jnd_dxds_jq_3')) {
                                                $points1 = $start1[1] * C('jnd_dxds_jq_3_bl');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }
                                            if ($res1) {

                                            }
                                        }
                                        break;
                                }

                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //type  = 3 极大值，极小值，判断 -----------------------------测试成功----------------------------
                    case 3:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '极大' || $start1[0] == '极小') {
                            if ($number['jz'] == $start1[0]) {
                                $points1 = $start1[1] * C('jnd_jz');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    // type= 4 和值的判断 9/10 --------------------------- 测试成功-------------------
                    case 4:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if (0 <= $start1[0] || $start1[0] <= 27) {
                            if ($number['zonghe'] == $start1[0]) {
                                $data = explode(',', C('jnd_hezhi_bv'));
                                $touzhushuzi = $start1[0];
                                $dd = $data[$touzhushuzi];
                                $chaifendeshuzi = explode('=', $dd);
                                $he_res = $chaifendeshuzi[1];
                                //乘配置文件的数据
                                $points1 = $start1[1] * $he_res;
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //豹子  type = 5  999/20    -----------------------------测试成功-----------------------------
                    case 5:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '豹子') {
                            if ($number['bz'] == $start1[0]) {
                                $points1 = $start1[1] * C('jnd_bz');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //  顺子判断   type = 6    顺子 ---------------------------------测试成功---------------------------
                    case 6:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '顺子') {
                            if ($number['sz'] == $start1[0]) {
                                $points1 = $start1[1] * C('jnd_sz');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //判断大小，-------------------------------未测试------------------------------
                    case 7:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '大' || $start1[0] == '小') {
                            //如果输入的值不是13 ， 14 那么走正常的程序
                            if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                //如果这期是豹子
                                if ($number['dz'] == "豹子") {
                                    if ($number['dz'] == "豹子") {
                                        $points1 = $start1[1] * 1;
                                        $res1 = $this->add_points($id, $userid, $points1);
                                    } else {
                                        $res1 = $this->del_points($id);
                                    }
                                } else {
                                    //这期不是豹子
                                    if ($number['dx'] == $start1[0]) {
                                        $points1 = $start1[1] * C('jnd_dx');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                    } else {
                                        $res1 = $this->del_points($id);
                                    }
                                }
                            } else {
                                if ($number['dx'] == $start1[0]) {
                                    if ($dangqianalldata < C('jnd_ds_jq_1')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_x1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {
                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_1') && $dangqianalldata < C('jnd_ds_jq_2')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_2') && $dangqianalldata < C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_2_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_3_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //否者如果玩者输入的不对，删除投注的金额。
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            }

                        }
                        break;
                    case 8:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '对子') {
                            if ($number['dz'] == $start1[0]) {
                                $points1 = $start1[1] * C('jnd_dz');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;

                }
            }
        }

        //检测是否到了赔付限额
        //检测是否到了赔付限额
        $alloderlist = M('order')->where(array("state" => 1, "is_add" => 1,'all_add_order'=>0))->group('number')->order("time ASC")->select();
        $countjc = count($alloderlist);
        $res  = array();
        for($i = 0; $i<$countjc; $i++){
            $res[$i] = M()->query("select userid,type,id,sum(add_points)as add_poionts from (select * from `think_order` order by `id` desc) `think_order` where `number` = '".$alloderlist[$i]['number']."' group by userid order by `id` desc");
            for($a = 0;$a<count($res[$i]);$a++){
                if($res[$i][$a]['add_poionts'] > 1000000){
                    $money = $res[$i][$a]['add_poionts'] - 100000000;
                    M('order')->where(array("number" => $alloderlist[$i]['number'],"userid"=>$res[$i][$a]['userid']))->setField(array('all_add_order' => '1'));
                    M("user")->where(array("id"=>$res[$i][$a]['userid']))->setDec("points",$money);
                }
            }
        }



    }
    public function Bj28js()
    {
        $value = S('bjjiesuan');

        if (empty($value)) {
            S('bjjiesuan', "1", 10);
        } else {
            return false;
        }

        //自动结算之前没结算的//查找状态为 is_add = 0 的标识没有记录的，is_add为存入，但是没有处理的。
        $list = M('order')->where(array("state" => 1, "is_add" => 0))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('dannumber')->where(array("game" => 'Bj28', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                //获取当前的开奖号码
                $number1 = explode(',', $current_number['awardnumbers']);
                //获取当前号码开奖的单双情况
                $number['bz'] = $current_number['bz'];
                $number['dx'] = $current_number['dx'];
                $number['ds'] = $current_number['danshuang'];
                $number['dxds'] = $current_number['dxds'];
                $number['jz'] = $current_number['jz'];
                $number['zonghe'] = $current_number['zonghe'];
                //当期的总金额
                $dangqianqihao = $current_number['periodnumber'];
                $dangqianalldata = M('order')->where('number',$dangqianqihao)->where('userid',$userid)->sum('del_points');
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                switch ($list[$i]['type']) {
                    //第一种为单双判断     单/20    如果判断正确   *   倍数；  ---------- 测试成功-------------------------------------------
                    //第一种为单双判断     单/20    如果判断正确
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '单' || $start1[0] == '双') {
                            //如果这局不是等于13 ， 14 那么就按照正常的程序去走
                            if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                if ($number['ds'] == $start1[0]) {
                                    $points1 = $start1[1] * C('dan_dx');
                                    $res1 = $this->add_points($id, $userid, $points1);
                                    if ($res1) {

                                    }
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                                //如果这局开的是13 或者14 那么就按照13 ，14 处理
                            } else {
                                if ($number['ds'] == $start1[0]) {
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ( $dangqianalldata <= C('dan_ds_jq_1')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_x1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    if ( $dangqianalldata> C('dan_ds_jq_1') &&  $dangqianalldata< C('dan_ds_jq_2')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ( $dangqianalldata> C('dan_ds_jq_2') &&  $dangqianalldata < C('dan_ds_jq_3')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_2_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ( $dangqianalldata> C('dan_ds_jq_3')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_3_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }

                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            }
                        }
                        break;
                    //type = 2 时 ， 判断大小单双。 // ------------------测试成功------------------------------------
                    case 2:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '大单' || $start1[0] == '大双' || $start1[0] == '小单' || $start1[0] == '小双') {
                            if ($number['dxds'] == $start1[0]) {
                                //判断是不是13 ， 或者14
                                //分算法
                                switch (C('dan_dxds_swich')) {
                                    case 1:
                                        //同一算法 13 14 特别
                                        if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                            $points1 = $start1[1] * C('dan_dxds');
                                            $res1 = $this->add_points($id, $userid, $points1);
                                            if ($res1) {
                                            }
                                        } else {
                                            //如果用户投的是  大小单双正确，且是综合为13,14的就按照特殊情况处理
                                            $points1 = $start1[1] * C('dan_dxds_13_14');
                                            $res1 = $this->add_points($id, $userid, $points1);
                                            if ($res1) {

                                            }
                                        }
                                        break;
                                    case 2:


                                        //第二种算法，看金额的大小去计算倍率
                                        if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                            if($start1[0] == '大双' || $start1[0] == '小单'){
                                                $points1 = $start1[1] * C('dan_dxds_dsxd');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }
                                            if($start1[0] == '小双'|| $start1[0] == '大单'){
                                                $points1 = $start1[1] * C('dan_dxds_xsdd');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }


                                        } else {
                                            //如果开的值为13,14的时候，后台设置，赔率为多少。
                                            if ( $dangqianalldata <= C('dan_dxds_jq_1')) {
                                                $points1 = $start1[1] * C('dan_dxds_jq_x1_bl');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }
                                            if ( $dangqianalldata > C('dan_dxds_jq_1') && $start1[1] < C('dan_dxds_jq_2')) {
                                                $points1 = $start1[1] * C('dan_dxds_jq_1_bl');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }
                                            if ( $dangqianalldata > C('dan_dxds_jq_2') && $start1[1] < C('dan_dxds_jq_3')) {
                                                $points1 = $start1[1] * C('dan_dxds_jq_3_bl');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }
                                            if ( $dangqianalldata > C('dan_dxds_jq_3')) {
                                                $points1 = $start1[1] * C('dan_dxds_jq_3_bl');
                                                $res1 = $this->add_points($id, $userid, $points1);
                                            }
                                            if ($res1) {

                                            }
                                        }
                                        break;
                                }

                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //type  = 3 极大值，极小值，判断 -----------------------------测试成功----------------------------
                    case 3:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '极大' || $start1[0] == '极小') {
                            if ($number['jz'] == $start1[0]) {
                                $points1 = $start1[1] * C('dan_jz');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    // type= 4 和值的判断 9/10 --------------------------- 测试成功-------------------
                    case 4:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if (0 <= $start1[0] || $start1[0] <= 27) {
                            if ($number['zonghe'] == $start1[0]) {
                                $data = explode(',', C('hezhi_bv'));
                                $touzhushuzi = $start1[0];
                                $dd = $data[$touzhushuzi];
                                $chaifendeshuzi = explode('=', $dd);
                                $he_res = $chaifendeshuzi[1];
                                //乘配置文件的数据
                                $points1 = $start1[1] * $he_res;
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //豹子  type = 5  999/20    -----------------------------测试成功-----------------------------
                    case 5:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '豹子') {
                            if ($number['bz'] == $start1[0]) {
                                $points1 = $start1[1] * C('dan_bz');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //  顺子判断   type = 6    顺子 ---------------------------------测试成功---------------------------
                    case 6:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '顺子') {
                            if ($number['sz'] == $start1[0]) {
                                $points1 = $start1[1] * C('dan_sz');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //判断大小，-------------------------------未测试------------------------------
                    case 7:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '大' || $start1[0] == '小') {
                            //如果输入的值不是13 ， 14 那么走正常的程序
                            if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                if ($number['dx'] == $start1[0]) {
                                    $points1 = $start1[1] * C('dan_dx');
                                    $res1 = $this->add_points($id, $userid, $points1);
                                    if ($res1) {

                                    }
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            } else {
                                if ($number['dx'] == $start1[0]) {
                                    if ( $dangqianalldata <= C('dan_ds_jq_1')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_x1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ( $dangqianalldata > C('dan_ds_jq_1') &&  $dangqianalldata < C('dan_ds_jq_2')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ( $dangqianalldata > C('dan_ds_jq_2') &&  $dangqianalldata < C('dan_ds_jq_3')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_2_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ( $dangqianalldata > C('dan_ds_jq_3')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_3_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //否者如果玩者输入的不对，删除投注的金额。
                                } else {
                                    $res1 = $this->del_points($id);
                                }

                            }

                        }
                        break;
                    case 8:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '对子') {
                            if ($number['dz'] == $start1[0]) {
                                $points1 = $start1[1] * C('dan_dz');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;

                }

            }
        }
        //检测是否到了赔付限额
        //检测是否到了赔付限额
        $alloderlist = M('order')->where(array("state" => 1, "is_add" => 1,'all_add_order'=>0))->group('number')->order("time ASC")->select();
        $countjc = count($alloderlist);
        $res  = array();
        for($i = 0; $i<$countjc; $i++){
            $res[$i] = M()->query("select userid,type,id,sum(add_points)as add_poionts from (select * from `think_order` order by `id` desc) `think_order` where `number` = '".$alloderlist[$i]['number']."' group by userid order by `id` desc");
            for($a = 0;$a<count($res[$i]);$a++){
                if($res[$i][$a]['add_poionts'] > 1000000){
                    $money = $res[$i][$a]['add_poionts'] - 1000000;
                    M('order')->where(array("number" => $alloderlist[$i]['number'],"userid"=>$res[$i][$a]['userid']))->setField(array('all_add_order' => '1'));
                    M("user")->where(array("id"=>$res[$i][$a]['userid']))->setDec("points",$money);
                }
            }
        }
    }
    public function Bjpk10()
    {
        $value = S('pkjiesuan');
        if (empty($value)) {
            S('pkjiesuan', "1", 10);
        } else {
            return false;
        }
        //自动结算之前没结算的
        $list = M('order')->where(array("state" => 1, "is_add" => 0))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                switch ($list[$i]['type']) {
                    case "pk10": {

                    }
                }
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('number')->where(array("game" => 'pk10', "periodnumber" => $list[$i]['number']))->find();

                if (!$current_number) {
                    continue;
                }

                $number1 = explode(',', $current_number['awardnumbers']);

                $lh = unserialize($current_number['lh']);
                for ($y = 0; $y < count($number1); $y++) {
                    if ($number1[$y] % 2 == 0) {
                        $number[$y]['ds'] = '双';
                        if ($number1[$y] >= 6) {
                            $number[$y]['zuhe'] = '大双';
                        } else {
                            $number[$y]['zuhe'] = '小双';
                        }
                    } else {
                        $number[$y]['ds'] = '单';
                        if ($number1[$y] >= 6) {
                            $number[$y]['zuhe'] = '大单';
                        } else {
                            $number[$y]['zuhe'] = '小单';
                        }
                    }
                    if ($number1[$y] >= 6) {
                        $number[$y]['dx'] = '大';
                    } else {
                        $number[$y]['dx'] = '小';
                    }
                }

                //分类
                switch ($list[$i]['type']) {
                    //车号大小单双(12345/双/100)
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        $num1 = 0;
                        $starts1 = str_split($start1[0]);
                        if ($start1[1] == '单' || $start1[1] == '双') {
                            for ($a = 0; $a < count($starts1); $a++) {
                                if ($starts1[$a] == 0) {
                                    $hao1 = '9';
                                } else {
                                    $hao1 = $starts1[$a] - 1;
                                }
                                if ($number[$hao1]['ds'] == $start1[1]) {
                                    $num1++;
                                }
                            }
                        } else {
                            for ($a = 0; $a < count($starts1); $a++) {
                                if ($starts1[$a] == 0) {
                                    $hao1 = '9';
                                } else {
                                    $hao1 = $starts1[$a] - 1;
                                }
                                if ($number[$hao1]['dx'] == $start1[1]) {
                                    $num1++;
                                }
                            }
                        }
                        if ($num1 > 0) {
                            $points1 = $num1 * $start1[2] * C('dxds');
                            $res1 = $this->add_points($id, $userid, $points1);
                            if ($res1) {


                            }
                        } else {

                            $res1 = $this->del_points($id);
                        }
                        break;

                    //车号(12345/89/20)
                    case 3:
                        $start2 = explode('/', $list[$i]['jincai']);
                        $chehao2 = str_split($start2[1]);
                        $starts2 = str_split($start2[0]);
                        $num2 = 0;
                        for ($s = 0; $s < count($chehao2); $s++) {
                            for ($a = 0; $a < count($starts2); $a++) {
                                if ($starts2[$a] == 0) {
                                    $hao2 = '9';
                                } else {
                                    $hao2 = $starts2[$a] - 1;
                                }
                                if ($chehao2[$s] == 0) {
                                    $chehao2[$s] = 10;
                                }
                                if ($chehao2[$s] == $number1[$hao2]) {
                                    $num2++;
                                }
                            }
                        }
                        if ($num2 > 0) {
                            $points2 = $num2 * $start2[2] * C('chehao');
                            $res2 = $this->add_points($id, $userid, $points2);
                            if ($res2) {
                                $this->send_msg('pointsadd', $points2, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //组合(890/大单/50)
                    case 2:
                        $start3 = explode('/', $list[$i]['jincai']);
                        $starts3 = str_split($start3[0]);
                        $num3 = 0;
                        for ($a = 0; $a < count($starts3); $a++) {
                            if ($starts3[$a] == 0) {
                                $hao3 = '9';
                            } else {
                                $hao3 = $starts3[$a] - 1;
                            }
                            if ($number[$hao3]['zuhe'] == $start3[1]) {
                                $num3++;
                            }
                        }
                        if ($num3 > 0) {
                            if ($start3[1] == '大单' || $start3[1] == '小双') {
                                $points3 = $num3 * $start3[2] * C('zuhe_1');
                            } else {
                                $points3 = $num3 * $start3[2] * C('zuhe_2');
                            }
                            $res3 = $this->add_points($id, $userid, $points3);
                            if ($res3) {
                                $this->send_msg('pointsadd', $points3, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //龙虎(123/龙/100)
                    case 4:
                        $start4 = explode('/', $list[$i]['jincai']);
                        $starts4 = str_split($start4[0]);
                        $num4 = 0;
                        for ($a = 0; $a < count($starts4); $a++) {
                            if ($starts4[$a] == 0) {
                                $hao4 = '9';
                            } else {
                                $hao4 = $starts4[$a] - 1;
                            }
                            if ($lh[$hao4] == $start4[1]) {
                                $num4++;
                            }
                        }
                        if ($num4 > 0) {
                            $points4 = $num4 * $start4[2] * C('lh');
                            $res4 = $this->add_points($id, $userid, $points4);
                            if ($res4) {
                                $this->send_msg('pointsadd', $points4, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //冠亚庄闲(庄/200)
                    case 5:
                        $start5 = explode('/', $list[$i]['jincai']);
                        if ($current_number['zx'] == $start5[0]) {
                            $points5 = $start5[1] * C('zx');
                            $res5 = $this->add_points($id, $userid, $points5);
                            if ($res5) {
                                $this->send_msg('pointsadd', $points5, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //冠亚号码(组/1-9.3-7/100)
                    case 6:
                        $start6 = explode('/', $list[$i]['jincai']);
                        if (strlen($start6[1]) > 3) {
                            $zu = explode('.', $start6[1]);
                            for ($a = 0; $a < count($zu); $a++) {
                                $gy = explode('-', $zu[$a]);
                                if ($gy[0] == 0) {
                                    $gy[0] = 10;
                                }
                                if ($gy[1] == 0) {
                                    $gy[1] = 10;
                                }
                                if ($gy[0] == $number1[0] && $gy[1] == $number1[1] || $gy[0] == $number1[1] && $gy[1] == $number1[0]) {
                                    $num6 = 1;
                                }
                            }
                        } else {
                            $gy = explode('-', $start6[1]);
                            if ($gy[0] == 0) {
                                $gy[0] = 10;
                            }
                            if ($gy[1] == 0) {
                                $gy[1] = 10;
                            }
                            if ($gy[0] == $number1[0] && $gy[1] == $number1[1] || $gy[0] == $number1[1] && $gy[1] == $number1[0]) {
                                $num6 = 1;
                            }
                        }
                        if ($num6 > 0) {
                            $points6 = $num6 * $start6[2] * C('gy');
                            $res6 = $this->add_points($id, $userid, $points6);
                            if ($res6) {
                                $this->send_msg('pointsadd', $points6, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码大小单双(和双100)
                    case 7:
                        $start7 = substr($list[$i]['jincai'], 3, 3);
                        $starts7 = substr($list[$i]['jincai'], 6);
                        $num7 = 0;
                        if ($start7 == '大' || $start7 == '小') {
                            if ($current_number['tema_dx'] == $start7) {
                                $num7 = 1;
                            }
                        } else {
                            if ($current_number['tema_ds'] == $start7) {
                                $num7 = 1;
                            }
                        }
                        if ($num7 > 0) {
                            if ($start7 == '大' || $start7 == '双') {
                                $points7 = $starts7 * C('tema_1');
                            } else {
                                $points7 = $starts7 * C('tema_2');
                            }
                            $res7 = $this->add_points($id, $userid, $points7);
                            if ($res7) {
                                $this->send_msg('pointsadd', $points7, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码数字(和5.6.7/100)
                    case 8:
                        $tema1 = array('03', '04', '18', '19');
                        $tema2 = array('5', '6', '16', '17');
                        $tema3 = array('7', '8', '14', '15');
                        $tema4 = array('9', '10', '12', '13');
                        $tema5 = array('11');

                        $start8 = explode('/', $list[$i]['jincai']);
                        $starts8 = substr($start8[0], 3);
                        $num8 = 0;
                        if (strlen($starts8) > 1) {
                            $tlist = explode('.', $starts8);
                            for ($a = 0; $a < count($tlist); $a++) {
                                if ($current_number['tema'] == $tlist[$a]) {
                                    if (in_array($tlist[$a], $tema1)) {
                                        $points8 = $start8[1] * C('tema_sz_1');
                                    }
                                    if (in_array($tlist[$a], $tema2)) {
                                        $points8 = $start8[1] * C('tema_sz_2');
                                    }
                                    if (in_array($tlist[$a], $tema3)) {
                                        $points8 = $start8[1] * C('tema_sz_3');
                                    }
                                    if (in_array($tlist[$a], $tema4)) {
                                        $points8 = $start8[1] * C('tema_sz_4');
                                    }
                                    if (in_array($tlist[$a], $tema5)) {
                                        $points8 = $start8[1] * C('tema_sz_5');
                                    }
                                    $num8 = 1;
                                }
                            }
                        } else {
                            if ($current_number['tema'] == $starts8) {
                                if (in_array($starts8, $tema1)) {
                                    $points8 = $start8[1] * C('tema_sz_1');
                                }
                                if (in_array($starts8, $tema2)) {
                                    $points8 = $start8[1] * C('tema_sz_2');
                                }
                                if (in_array($starts8, $tema3)) {
                                    $points8 = $start8[1] * C('tema_sz_3');
                                }
                                if (in_array($starts8, $tema4)) {
                                    $points8 = $start8[1] * C('tema_sz_4');
                                }
                                if (in_array($starts8, $tema5)) {
                                    $points8 = $start8[1] * C('tema_sz_5');
                                }
                                $num8 = 1;
                            }
                        }
                        if ($num8 > 0) {
                            $res8 = $this->add_points($id, $userid, $points8);
                            if ($res8) {
                                $this->send_msg('pointsadd', $points8, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码区段(BC/100)
                    case 9:
                        $start9 = explode('/', $list[$i]['jincai']);
                        $num9 = 0;
                        if (strlen($start9[0]) > 1) {
                            $starts9 = str_split($start9[0]);
                            for ($a = 0; $a < count($starts9); $a++) {
                                if ($current_number['tema_dw'] == $starts9[$a]) {
                                    if ($starts9[$a] == 'A' || $starts9[$a] == 'C') {
                                        $points9 = $start9[1] * C('tema_qd_1');
                                    } else {
                                        $points9 = $start9[1] * C('tema_qd_2');
                                    }
                                    $num9 = 1;
                                }
                            }
                        } else {
                            if ($current_number['tema_dw'] == $start9[0]) {
                                if ($start9[0] == 'A' || $start9[0] == 'C') {
                                    $points9 = $start9[1] * C('tema_qd_1');
                                } else {
                                    $points9 = $start9[1] * C('tema_qd_2');
                                }
                                $num9 = 1;
                            }
                        }
                        if ($num9 > 0 && $points9) {
                            $res9 = $this->add_points($id, $userid, $points9);
                            if ($res9) {
                                $this->send_msg('pointsadd', $points9, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                }
            }
        }
    }
    public function ssc(){
        header("Content-type: text/html; charset=utf-8");
        $value = S('sscjiesuan');
        if (empty($value)) {
            S('sscjiesuan', "1", 1);
        } else {
            return false;
        }
        //自动结算之前没结算的//查找状态为 is_add = 0 的标识没有记录的，is_add为存入，但是没有处理的。
        $list = M('order')->where(array("state" => 1, "is_add" => 0))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的

                $current_number = M('sscnumber')->where(array("game" => 'Ssc', "periodnumber" => $list[$i]['number']))->find();

                if (!$current_number) {
                    continue;
                }

                //获取当前的开奖号码
                //获取当前的开奖号码
                $number1 = explode(',', $current_number['awardnumbers']);
                //获取当前号码开奖的单双情况
                $number['awardnumbers'] = $current_number['awardnumbers'];
                $number['ds'] = $current_number['ds'];
                $number['dx'] = $current_number['dx'];
                $number['zuhe'] = $current_number['zuhe'];
                $number['dxds'] = $current_number['dxds'];
                $number['jz'] = $current_number['jz'];
                $number['zonghe'] = $current_number['zonghe'];
                //当期的总金额
                $dangqianqihao = $current_number['periodnumber'];
                $dangqianalldata = M('order')->where('number',$dangqianqihao)->where('userid',$userid)->sum('del_points');

                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                switch ($list[$i]['type']) {
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        //再次判断是否为单双正确的，和数据库里ds 判断

                        if ($start1[1] == '单' || $start1[1] == '双') {
                            //判断第几个数字
                            $selectsum = $start1[0] -1;
                            //拆分ds字段的數組。
                            $ds = explode('/', $number['ds']);
                            if ($ds[$selectsum] == $start1[1]) {
                                $points1 = $start1[2] * C('jnd_dx');
                                $res1 = $this->add_points($id, $userid, $points1);
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        //如果为大小的单独和数据库字段dx 的判断
                        if ($start1[1] == '大' || $start1[1] == '小') {
                            //判断第几个数字
                            $selectsum = $start1[0] -1;
                            //拆分ds字段的數組。
                            $dx = explode('/', $number['dx']);
                            if ($dx[$selectsum] == $start1[1]) {
                                $points1 = $start1[2] * C('dan_dx');
                                $res1 = $this->add_points($id, $userid, $points1);

                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }

                        break;
                    //type = 2 时 ， 判断大小单双。 // ------------------测试成功---------------------------------------
                    case 2:
                        $start1 = explode('/', $list[$i]['jincai']);
                        //再次判断是否为单双正确的，和数据库里ds 判断
                        if ($start1[1] == '大单' || $start1[1] == '大双'|| $start1[1] == '小双'|| $start1[1] == '小单') {
                            //判断第几个数字
                            $selectsum = $start1[0] -1;
                            //拆分ds字段的數組。
                            $zuhe = explode('/', $number['zuhe']);

                            if ($zuhe[$selectsum] == $start1[1]) {
                                $points1 = $start1[2] * C('jnd_dx');
                                $res1 = $this->add_points($id, $userid, $points1);
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                        //type  = 3 时候， 判断数字的大小。
                    case 3:
                        //获取用户下注的数字。
                        $start1 = explode('/', $list[$i]['jincai']);
                        //判断第几个数字
                        $selectsum = $start1[0] -1;
                        //拆分ds字段的數組。
                        $zuhe = explode(',', $number['awardnumbers']);
                        //判断下注是否正确
                        if ($zuhe[$selectsum] == $start1[1]) {
                            $points1 = $start1[2] * C('jnd_dx');
                            $res1 = $this->add_points($id, $userid, $points1);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                }
            }
        }
        //检测是否到了赔付限额
        //检测是否到了赔付限额
        $alloderlist = M('order')->where(array("state" => 1, "is_add" => 1,'all_add_order'=>0))->group('number')->order("time ASC")->select();
        $countjc = count($alloderlist);
        $res  = array();
        for($i = 0; $i<$countjc; $i++){
            $res[$i] = M()->query("select userid,type,id,sum(add_points)as add_poionts from (select * from `think_order` order by `id` desc) `think_order` where `number` = '".$alloderlist[$i]['number']."' group by userid order by `id` desc");
            for($a = 0;$a<count($res[$i]);$a++){
                if($res[$i][$a]['add_poionts'] > 1000000){
                    $money = $res[$i][$a]['add_poionts'] - 1000000;
                    M('order')->where(array("number" => $alloderlist[$i]['number'],"userid"=>$res[$i][$a]['userid']))->setField(array('all_add_order' => '1'));
                    M("user")->where(array("id"=>$res[$i][$a]['userid']))->setDec("points",$money);
                }
            }
        }
    }
    public function kuai3(){
        $value = S('jiesuan');

        if (empty($value)) {
            S('jiesuan', "1", 10);
        } else {
            return false;
        }
        //自动结算之前没结算的//查找状态为 is_add = 0 的标识没有记录的，is_add为存入，但是没有处理的。
        $list = M('order')->where(array("state" => 1, "is_add" => 0))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('kuainumber')->where(array("game" => 'kuai3', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                //获取当前的开奖号码
                $number1 = explode(',', $current_number['awardnumbers']);
                //获取当前号码开奖的单双情况
                $number['bz'] = $current_number['bz'];
                $number['dx'] = $current_number['dx'];
                $number['sz'] = $current_number['sz'];
                $number['sz'] = $current_number['sz'];
                $number['santonghaotong'] = $current_number['santonghaotong'];
                $number['zonghe'] = $current_number['zonghe'];
                //当期的总金额
                $dangqianqihao = $current_number['periodnumber'];
                $dangqianalldata = M('order')->where('number',$dangqianqihao)->where('userid',$userid)->sum('del_points');
                //当期的赔付的总金额，是在结算这每一单的时候前的总金额
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                switch ($list[$i]['type']) {
                    //和值
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if (0 <= $start1[0] || $start1[0] <= 18) {
                            if ($number['zonghe'] == $start1[0]) {
                                $data = explode(',', C('kuai3_hezhi_bv'));
                                $touzhushuzi = $start1[0];
                                $dd = $data[$touzhushuzi];
                                $chaifendeshuzi = explode('=', $dd);
                                $he_res = $chaifendeshuzi[1];
                                //乘配置文件的数据
                                $points1 = $start1[1] * $he_res;
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {
                                }
                            }else {
                                $res1 = $this->del_points($id);
                            }
                        }else{
                            $res1 = $this->del_points($id);
                        }
                        break;
                    //三不同通选
                    case 2:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '三不同') {
                            if ($number['santonghaotong'] == $start1[0]) {
                                $points1 = $start1[1] * C('kuai3_sbt');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {
                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //三同号
                    case 3:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '三同号') {
                            if ($number['santonghaotong'] == $start1[0]) {
                                $points1 = $start1[1] * C('kuai3_sth');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {
                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                   //三连号：
                    case 4:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '三连号') {
                            if ($number['sz'] == $start1[0]) {
                                $points1 = $start1[1] * C('kuai3_sz_bv');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {
                                }
                            } else {
                               $this->del_points($id);
                            }
                        }else{
                             $this->del_points($id);
                        }
                        break;
                  //二相同
                    case 5:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '豹子') {
                            if ($number['bz'] == $start1[0]) {
                                $points1 = $start1[1] * C('jnd_bz');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //  顺子判断   type = 6    顺子 ---------------------------------测试成功---------------------------
                    case 6:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '顺子') {
                            if ($number['sz'] == $start1[0]) {
                                $points1 = $start1[1] * C('jnd_sz');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //判断大小，-------------------------------未测试------------------------------
                    case 7:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '大' || $start1[0] == '小') {
                            //如果输入的值不是13 ， 14 那么走正常的程序
                            if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                //如果这期是豹子
                                if ($number['dz'] == "豹子") {
                                    if ($number['dz'] == "豹子") {
                                        $points1 = $start1[1] * 1;
                                        $res1 = $this->add_points($id, $userid, $points1);
                                    } else {
                                        $res1 = $this->del_points($id);
                                    }
                                } else {
                                    //这期不是豹子
                                    if ($number['dx'] == $start1[0]) {
                                        $points1 = $start1[1] * C('jnd_dx');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                    } else {
                                        $res1 = $this->del_points($id);
                                    }
                                }
                            } else {
                                if ($number['dx'] == $start1[0]) {
                                    if ($dangqianalldata < C('jnd_ds_jq_1')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_x1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {
                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_1') && $dangqianalldata < C('jnd_ds_jq_2')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_2') && $dangqianalldata < C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_2_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_3_bl');
                                        $res1 = $this->add_points($id, $userid, $points1);
                                        if ($res1) {

                                        }
                                    }
                                    //否者如果玩者输入的不对，删除投注的金额。
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            }

                        }
                        break;
                    case 8:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '对子') {
                            if ($number['dz'] == $start1[0]) {
                                $points1 = $start1[1] * C('jnd_dz');
                                $res1 = $this->add_points($id, $userid, $points1);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;

                }
            }
        }

        //检测是否到了赔付限额
        //检测是否到了赔付限额
        $alloderlist = M('order')->where(array("state" => 1, "is_add" => 1,'all_add_order'=>0))->group('number')->order("time ASC")->select();
        $countjc = count($alloderlist);
        $res  = array();
        for($i = 0; $i<$countjc; $i++){
            $res[$i] = M()->query("select userid,type,id,sum(add_points)as add_poionts from (select * from `think_order` order by `id` desc) `think_order` where `number` = '".$alloderlist[$i]['number']."' group by userid order by `id` desc");
            for($a = 0;$a<count($res[$i]);$a++){
                if($res[$i][$a]['add_poionts'] > 1000000){
                    $money = $res[$i][$a]['add_poionts'] - 100000000;
                    M('order')->where(array("number" => $alloderlist[$i]['number'],"userid"=>$res[$i][$a]['userid']))->setField(array('all_add_order' => '1'));
                    M("user")->where(array("id"=>$res[$i][$a]['userid']))->setDec("points",$money);
                }
            }
        }


    }
    /**
     * 竞猜成功  加分
     * */
    public function add_points($order_id, $userid, $points)
    {

        if (empty($userid)) {
            return 0;
        }
        if (!M('order')->where(array("id" => $order_id, "is_add" => 0, "userid" => $userid))->find()) {
            return 0;
        }
        $res = M('user')->where(array("id" => $userid))->setInc('points', $points);

        if ($res) {
            $res1 = M('order')->where(array("id" => $order_id))->setField(array('is_add' => '1', 'add_points' => $points));
        }
        if ($res && $res1) {
            return 1;
        }
    }

    /**
     * 竞猜成功  加分
     * */
    public function del_points($order_id)
    {
        $res = M('order')->where(array("id" => $order_id))->setField(array('is_add' => '1'));
        if ($res) {
            return 1;
        }
    }

//    public function jnd28(){
//        //加拿大28模拟机器人
//        $this->display();
//    }
//    public function pk10(){
//        //pk10模拟机器人
//        $this->display();
//    }
//    public function Bj28(){
//        //北京28模拟机器人
//        $this->display();
//    }

}