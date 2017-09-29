<?php

namespace Home\Controller;

use Think\Server;

header('content-type:text/html;charset=utf-8');

class WorkerJnd28Controller extends Server
{
    protected $socket = 'websocket://0.0.0.0:7274';
    protected $processes = 1000;
    /**
     * 添加定时器
     *监控连接状态
     * */

    public function onWorkerStart()
    {

        $beginToday = strtotime('09:00:00');
        $endToday = strtotime("23:59:59");
        //初始化

        $time_interval = 1;//每几秒钟触发php一次
        //1.获取加拿大初始化数据
        $jnddata = getJnd28();

        //1。获取时时彩初始化数据
        $sscdata = getSsc();

        //1.获取快3初始化数据
        $kuai3data = getkuai3();
        echo "33";
        //1。获取北京28初始化数据
        $jianadabegin = strtotime('00:00:00');
        $jianadaend= strtotime("09:00:00");
//        if($jianadabegin<time() && time()<$jianadaend){

        $bj28data = getBj28();
        echo "BEIJING_START__=";
        echo $bj28data['next']['periodNumber'];
        echo "__BEIJING_END";
        echo "JND_START__=";
        echo $jnddata['next']['periodNumber'];
        echo "__JND_END";
        //1。获取北京赛车pk10初始化数据
        $pkdata = getPK10();
        //2.快3距离下一场的时间的值
        $kuai3_nexttime = $kuai3data['next']['delayTimeInterval'] + strtotime($kuai3data['next']['awardTime']);
        //2.加拿大28距离下一场的时间的值
        $jnd_nexttime = $jnddata['next']['delayTimeInterval'] + strtotime($jnddata['next']['awardTime']);
        //2.北京28距离下一场的时间的值
        $bj28_nexttime = $bj28data['next']['delayTimeInterval'] + strtotime($bj28data['next']['awardTime']);
        //2.时时彩距离下一场的时间的值
        $ssc_nexttime = $sscdata['next']['delayTimeInterval'] + strtotime($sscdata['next']['awardTime']);
        //2.北京赛车pk10距离下一场的值
        $pk10_nexttime = $pkdata['next']['delayTimeInterval'] + strtotime($pkdata['next']['awardTime']);
        //3.块3初始状态是否开盘
        if ($kuai3_nexttime- time() > 20&& $kuai3_nexttime - time() < 600) {
            F('kuai3_status', 1);//开盘
        } else {
            F('kuai3_status', 0);
        }
        //3.加拿大初始化状态状态是或开盘
        if ($jnd_nexttime - time() > 20&& $jnd_nexttime - time() < 200) {
            F('jndstatus', 1);//开盘
        } else {
            F('jndstatus', 0);
        }
        //3.北京28初始化状态状态是或开盘
        if ($bj28_nexttime - time() > 20 && $bj28_nexttime - time() < 300) {
            F('bj28_status', 1);//开盘
        } else {
            F('bj28_status', 0);
        }
        echo "bj28_status_START__";
        echo F('bj28_status');
        echo "__bj28_status_END";
        //3.时时彩初始化状态状态是或开盘
        if ($ssc_nexttime - time() > 20 && $ssc_nexttime - time() < 600/* && time() > $beginToday && time() < $endToday*/) {
            F('ssc_status', 1);//开盘
        } else {
            F('ssc_status', 0);
        }
        //3.北京赛车pk10初始化状态状态是或开盘
        if ($pk10_nexttime - time() > 8 && $pk10_nexttime - time() < 300 && time() > $beginToday && time() < $endToday) {
            F('pk10_status', 1);//开盘
        } else {
            F('pk10_status', 0);
        }
        //4.快3把api获取的值缓存到服务器.
        if (!F('getkuai3data')) {
            F('getkuai3data', $kuai3data);
        }
        //4.加拿大把api获取的值缓存到服务器.
        if (!F('getjnd28data')) {
            F('getjnd28data', $jnddata);
        }
        //4.北京28把api获取的值缓存到服务器.
        if (!F('getbj28data')) {
            F('getbj28data', $bj28data);
        }
        //4.北京pk10把api获取的值缓存到服务器.
        if (!F('getpk10data')) {
            F('getpk10data', $pkdata);
        }
        //4.时时彩把api获取的值缓存到服务器.
        if (!F('getssc')) {
            F('getssc', $sscdata);
        }
        //4.5 加拿大机器人的随机数字
        if (!F('jnd28_robo')) {
            F('jnd28_robo', rand(0,C('jnd28_robo_suiji')));
        }
        //4.5 北京28 机器人的随机数字
        if (!F('bj28_robo')) {
            F('bj28_robo', rand(0,C('bj28_robo_suiji')));
        }
        //4.5 北京赛车机器人的随机数字
        if (!F('pk10_robo')) {
            F('pk10_robo', rand(0,C('pk10_robo_suiji')));
        }
        //4.5快3机器人的随机数字
        if (!F('kuai3_robo')) {
            F('kuai3_robo', rand(0,C('kuai3_robo_suiji')));
        }
        //4.5 时时彩机器人的随机数字
        if (!F('ssc_robo')) {
            F('ssc_robo', rand(0,C('ssc_robo_suiji')));
        }

        /*开奖time*/
        \Workerman\Lib\Timer::add($time_interval, function () {
            //php异步操作，执行让缓存更新，防止获取信息停顿为空，等待时间超时问题
//            updata();
            //5.获取快3的缓存
            $getkuai3data = F('getkuai3data');
            //5.获取加拿大的缓存
            $getjnd28data = F('getjnd28data');
            //5.获取北京的缓存
            $getbj28data = F('getbj28data');
            //5.获取pk10的缓存
            $getpk10data = F('getpk10data');
            //5.获取时时彩的缓存
            $getssc = F('getssc');
            //6.每秒钟判断快3的状态.
            $kuai3_next_time = $getkuai3data['next']['delayTimeInterval'] + strtotime($getkuai3data['next']['awardTime']);
            //6.每秒钟判断加拿大的状态.
            $jnd_next_time = $getjnd28data['next']['delayTimeInterval'] + strtotime($getjnd28data['next']['awardTime']);
//            if ($jnd_next_time - time() > 20 && $jnd_next_time - time() < 300) {
//                F('jndstatus', 1);
//            } else {
//                F('jndstatus', 0);
//            }
            //6.每秒钟判断北京28的状态.
            $bj28_next_time = $getbj28data['next']['delayTimeInterval'] + strtotime($getbj28data['next']['awardTime']);
            //6.每秒钟判断北京赛车pk10的状态.
            $pk10_next_time = $getpk10data['next']['delayTimeInterval'] + strtotime($getpk10data['next']['awardTime']);
            //6.每秒钟判断时时彩的状态.
            $ssc_nexttime = $getssc['next']['delayTimeInterval'] + strtotime($getssc['next']['awardTime']);
//            if ($ssc_nexttime - time() > 8 && $ssc_nexttime - time() < 600/* && time() > $beginToday && time() < $endToday*/) {
//                F('ssc_status', 1);
//            } else {
//                F('ssc_status', 0);
//            }
            //7.快3在距离38s的时候，提醒一次
            if ($jnd_next_time - time() == 38) {
                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'kuai3',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getkuai3data['next']['periodNumber'] . '<br/>' . '--距离封盘还有30秒--'.'<br/>'.'最高中奖100万积分，最低下注'.C('kuai3_jinezx').'积分',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="kuai3";
                $this->add_message($new_message,$type);
            }
            //7.加拿大在距离38s的时候，提醒一次
            if ($jnd_next_time - time() == 38) {
                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'jnd28',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getjnd28data['next']['periodNumber'] . '<br/>' . '--距离封盘还有30秒--'.'<br/>'.'最高中奖100万积分，最低下注'.C('jnd_jinezx').'积分',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="jnd28";
                $this->add_message($new_message,$type);
            }
            //7.北京28在距离38s的时候，提醒一次
            if ($bj28_next_time - time() == 38) {
                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'bj28',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getbj28data['next']['periodNumber'] . '<br/>' . '--距离封盘还有30秒--'.'<br/>'.'最高中奖100万积分，最低下注'.C('jinezx').'积分',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="bj28";
                $this->add_message($new_message,$type);
                $this->add_message($new_message,$type);
            }
            //7.北京赛车pk10在距离38s的时候，提醒一次
            if ($pk10_next_time - time() == 38) {
                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'pk10',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getpk10data['next']['periodNumber'] . '<br/>' . '--距离封盘还有30秒--'.'<br/>'.'最高中奖100万积分，最低下注'.C('jinezx').'积分',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="pk10";
                $this->add_message($new_message,$type);
            }
            //7.时时彩在距离38s的时候，提醒一次
            if ($ssc_nexttime - time() == 38) {
                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'ssc',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getssc['next']['periodNumber'] . '<br/>' . '--距离封盘还有30秒--'.'<br/>'.'最高中奖100万积分，最低下注'.C('ssc_jinezx').'积分',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="ssc";
                $this->add_message($new_message,$type);
            }
            //----------------------------------------------------------------------------------------------------------------------
            //8.如果快3时间小于20秒的时候，提醒投注并关闭
            if ($kuai3_next_time - time() <= C('kuai3_status_off') && F('kuai3_status') ==1  || $kuai3_next_time - time()>600) {
                F('kuai3_status', 0);
                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'kuai3',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getkuai3data['next']['periodNumber'] . '--关闭，请耐心等待--' . '单局最高中奖100万积分，最低下注50积分',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="kuai3";
                $this->add_message($new_message,$type);
            }
            //8.如果加拿大时间小于20秒的时候，提醒投注并关闭
            if ($jnd_next_time - time() <= C('jnd28_status_off') && F('jndstatus') ==1) {
                F('jndstatus', 0);
                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'jnd28',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getjnd28data['next']['periodNumber'] . '<br/>' . '--关闭，请耐心等待--' . '单局最高中奖100万积分，最低下注50积分',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="jnd28";
                $this->add_message($new_message,$type);
            }
            //8.如果pk10时间小于20秒的时候，提醒投注并关闭
            if ($pk10_next_time - time() <= C('pk10_status_off')  && F('pk10_status') ==1) {
                F('pk10_status', 0);
                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'pk10',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getpk10data['next']['periodNumber'] . '<br/>' . '--关闭，请耐心等待--' . '单局最高中奖100万积分，最低下注50积分',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="pk10";
                $this->add_message($new_message,$type);
            }
            //8.如果北京28时间小于20秒的时候，提醒投注并关闭
            if ($bj28_next_time - time()  <= C('bj28_status_off') && F('bj28_status') ==1) {
                F('bj28_status', 0);
                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'bj28',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getbj28data['next']['periodNumber'] . '<br/>' . '--关闭，请耐心等待--',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="bj28";
                $this->add_message($new_message,$type);
            }
            //8.如果时时彩时间小于20秒的时候，提醒投注并关闭
            if ($ssc_nexttime - time() <= C('ssc_status_off') && F('ssc_status') ==1 /* $ssc_nexttime - time()>600*/) {
                F('ssc_status', 0);
                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'ssc',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getssc['next']['periodNumber'] . '<br/>' . '--关闭，请耐心等待--',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="ssc";
                $this->add_message($new_message,$type);
            }
            //9.快3把状态开启
            if ($kuai3_next_time - time() < 600 && $kuai3_next_time - time() > C('kuai3_status_off') && F('kuai3_status') == 0 /*|| F('kuai3_status') == 0 && $kuai3_next_time - time() > 600*/) {
                F('kuai3_status', 1);
                $new_message = array(
                    'delay' => '8',
                    'type' => 'admin',
                    'send_type'=>'kuai3',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getkuai3data['next']['periodNumber'] . '开放，祝各位中大奖',
                    'time' => date('H:i:s')
                );
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="kuai3";
                $this->add_message($new_message,$type);
            }
            //9.加拿大把状态开启
            if ($jnd_next_time - time() < 300 && $jnd_next_time - time() > C('jnd28_status_off') && F('jndstatus') == 0 || F('jndstatus') == 0 && $jnd_next_time - time() > 300) {
                F('jndstatus', 1);
                $new_message = array(
                    'delay' => '8',
                    'type' => 'admin',
                    'send_type'=>'jnd28',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getjnd28data['next']['periodNumber'] . '开放，祝各位中大奖',
                    'time' => date('H:i:s')
                );
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="jnd28";
                $this->add_message($new_message,$type);
            }
            //9.北京28把状态开启
            if ($bj28_next_time - time() < 300 && $bj28_next_time - time() > 180 && F('bj28_status') == 0 || F('bj28_status') == 0 && $bj28_next_time - time() > 300) {
                //结算
                F('bj28_status', 1);
                $new_message = array(
                    'delay' => '8',
                    'type' => 'admin',
                    'send_type'=>'bj28',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getbj28data['next']['periodNumber'] . '开放，祝各位中大奖',
                    'time' => date('H:i:s')
                );
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="pk10";
                $this->add_message($new_message,$type);
            }
            //9.北京pk10把状态开启
            if ($pk10_next_time - time() < 300 && $pk10_next_time - time() > C('pk10_status_off') && F('pk10_status') == 0 || F('pk10_status') == 0 && $pk10_next_time - time() > 300) {
                F('pk10_status', 1);
                $new_message = array(
                    'delay' => '8',
                    'type' => 'admin',
                    'send_type'=>'pk10',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getjnd28data['next']['periodNumber'] . '开放，祝各位中大奖',
                    'time' => date('H:i:s')
                );
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="pk10";
                $this->add_message($new_message,$type);
            }
            //9.时时彩把状态开启
            if ($ssc_nexttime - time() < 600 && $ssc_nexttime - time() > C('ssc_status_off') && F('ssc_status') == 0 /*|| F('ssc_status') == 0 && $ssc_nexttime - time() > 300*/) {
                //结算
                F('ssc_status', 1);
                $new_message = array(
                    'delay' => '8',
                    'type' => 'admin',
                    'send_type'=>'ssc',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getssc['next']['periodNumber'] . '开放，祝各位中大奖',
                    'time' => date('H:i:s')
                );
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
                $type="ssc";
                $this->add_message($new_message,$type);
            }
            //10.快3机器人触发
            if (C('robot') == 1) {
                if (F('kuai3_robo') <0){
                    $mess = $this->robot_message($type='jnd28');
                    $robot = $this->robot();
                    $sscstatus = F('kuai3_status');
                    $new_message = array(
                        'type' => 'say',
                        'send_type'=>'kuai3',
                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
                        'from_client_name' => $robot[0]['nickname'],
                        'content' => $mess[0]['content'],
                        'time' => date('H:i:s')
                    );
                    if ($sscstatus == 1) {
                        if (54 == 54) {
                            foreach ($this->worker->connections as $conn) {
                                $conn->send(json_encode($new_message));
                            }
                            $type="kuai3";
                            $this->add_message($new_message,$type);
                        }
                    }
                    F('kuai3_robo', rand(0,C('kuai3_robo_suiji')));
                }else{
                    $jnd_rob_sum =F('kuai3_robo');
                    $jnd_rob_data = $jnd_rob_sum-1;
                    F('kuai3_robo',$jnd_rob_data);
                }
            }
            //10.加拿大28机器人触发
            if (C('robot') == 1) {

                if (F('jnd28_robo') <0){
                    $mess = $this->robot_message($type='jnd28');
                    $robot = $this->robot();
                    $sscstatus = F('jndstatus');
                    $new_message = array(
                        'type' => 'say',
                        'send_type'=>'jnd28',
                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
                        'from_client_name' => $robot[0]['nickname'],
                        'content' => $mess[0]['content'],
                        'time' => date('H:i:s')
                    );
                    if ($sscstatus == 1) {
                        if (54 == 54) {
                            foreach ($this->worker->connections as $conn) {
                                $conn->send(json_encode($new_message));
                            }
                            $type="jnd28";
                            $this->add_message($new_message,$type);
                        }
                    }
                    F('jnd28_robo', rand(0,C('jnd28_robo_suiji')));
                }else{
                    $jnd_rob_sum =F('jnd28_robo');
                    $jnd_rob_data = $jnd_rob_sum-1;
                    F('jnd28_robo',$jnd_rob_data);
                }
            }
            //10.北京28机器人触发
            if (C('robot') == 1) {
                if (F('bj28_robo') <0){
                    $mess = $this->robot_message($type='jnd28');
                    $robot = $this->robot();
                    $bj28status = F('bj28_status');
                    $new_message = array(
                        'type' => 'say',
                        'send_type'=>'bj28',
                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
                        'from_client_name' => $robot[0]['nickname'],
                        'content' => $mess[0]['content'],
                        'time' => date('H:i:s')
                    );
                    if ($bj28status == 1) {
                        if (54 == 54) {
                            foreach ($this->worker->connections as $conn) {
                                $conn->send(json_encode($new_message));
                            }
                            $type="bj28";
                            $this->add_message($new_message,$type);
                        }
                    }
                    F('bj28_robo', rand(0,C('bj28_robo_suiji')));
                }else{
                    $jnd_rob_sum =F('bj28_robo');
                    $jnd_rob_data = $jnd_rob_sum-1;
                    F('bj28_robo',$jnd_rob_data);
                }
            }
            //10.北京pk10机器人触发
            if (C('robot') == 1) {

                if (F('pk10_robo') <0){
                    $mess = $this->robot_message($type='pk10');
                    $robot = $this->robot();
                    $sscstatus = F('pk10_status');
                    $new_message = array(
                        'type' => 'say',
                        'send_type'=>'pk10',
                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
                        'from_client_name' => $robot[0]['nickname'],
                        'content' => $mess[0]['content'],
                        'time' => date('H:i:s')
                    );
                    if ($sscstatus == 1) {
                        if (54 == 54) {
                            foreach ($this->worker->connections as $conn) {
                                $conn->send(json_encode($new_message));
                            }
                            $type="pk10";
                            $this->add_message($new_message,$type);
                        }
                    }
                    F('pk10_robo', rand(0,C('pk10_robo_suiji')));
                }else{
                    $jnd_rob_sum =F('pk10_robo');
                    $jnd_rob_data = $jnd_rob_sum-1;
                    F('pk10_robo',$jnd_rob_data);
                }
            }
            //10.时时彩机器人触发
            if (C('robot') == 1) {
                if (F('ssc_robo') <0){
                    $mess = $this->robot_message($type="ssc");
                    $robot = $this->robot();
                    $jndstatus = F('ssc_status');
                    $new_message = array(
                        'type' => 'say',
                        'send_type'=>'ssc',
                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
                        'from_client_name' => $robot[0]['nickname'],
                        'content' => $mess[0]['content'],
                        'time' => date('H:i:s')
                    );
                    if ($jndstatus == 1) {
                        if (54 == 54) {
                            foreach ($this->worker->connections as $conn) {
                                $conn->send(json_encode($new_message));
                            }
                            $type="ssc";
                            $this->add_message($new_message,$type);
                        }
                    }
                    F('ssc_robo', rand(0,C('ssc_robo_suiji')));
                }else{
                    $jnd_rob_sum =F('ssc_robo');
                    $jnd_rob_data = $jnd_rob_sum-1;
                    F('ssc_robo',$jnd_rob_data);
                }
            }
            echo "ceshi";
        });
        //即将关闭提醒------------------------------------------------------------------------
        //即将关闭提醒------------------------------------------------------------------------
//        \Workerman\Lib\Timer::add($time_interval, function () {
//            $tips = array(
//                'type' => 'system',
//                'send_type'=>'jnd28',
//                'head_img_url' => '/Public/main/img/system.jpg',
//                'from_client_name' => '客服',
//                'content' => '加拿大28即将关闭！',
//                'time' => date('H:i:s')
//            );
//            if (date('H:i:s') == '23:50:00') {
//                foreach ($this->worker->connections as $conn) {
//                    $conn->send(json_encode($tips));
//                }
//            }
//        });
        //统计人数--------------------------------------------------------------------------
        //统计人数--------------------------------------------------------------------------
        \Workerman\Lib\Timer::add($time_interval, function () {
            //ping客户端(获取房间内所有用户列表 )
            $clients_list = $this->worker->connections;
            $num = count($clients_list);
            $new_message = array(
                'type' => 'ping',
                'content' => $num,
                'time' => date('H:i:s')
            );
            //if($num!=F('online')){
            //F('online',$num);
            foreach ($this->worker->connections as $conn) {
                $conn->send(json_encode($new_message));
            }
        });
        //300秒一次的公告------------------------------------------------------------------
        //300秒一次的公告------------------------------------------------------------------
        \Workerman\Lib\Timer::add(300, function () {
            $new_message = array(
                'type' => 'system',
                'head_img_url' => '/Public/main/img/system.jpg',
                'from_client_name' => '客服',
                'content' => '由于各地网络情况不同，开奖动画仅作为参考，可能存在两秒的误差，不影响最终开奖结果！',
                'time' => date('H:i:s')
            );
            foreach ($this->worker->connections as $conn) {
                $conn->send(json_encode($new_message));
            }
        });
//        $jiqiren = rand(0,10);
        //机器人触发-------------------------------------------------------------------------------
        // 机器人触发-------------------------------------------------------------------------------
//        \Workerman\Lib\Timer::add($jiqiren, function () {
//            if (C('robot') == 1) {
//                $mess = $this->robot_message();
//                $robot = $this->robot();
//                $jndstatus = F('jndstatus');
//                $new_message = array(
//                    'type' => 'say',
//                    'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
//                    'from_client_name' => $robot[0]['nickname'],
//                    'content' => $mess[0]['content'],
//                    'time' => date('H:i:s')
//                );
//                if ($jndstatus == 1) {
//                    $new_message1['type'] = 'error';
//                    if (54 == 54) {
//                        foreach ($this->worker->uidConnections as $con) {
//                            $con->send(json_encode($new_message));
//                        }
//                        $this->add_message($new_message);
//                    }
//                }
//            }
//
//        });

        //存储结果----------------------------------------------------------------------------------------
        \Workerman\Lib\Timer::add(10, function () {
            //10.存快3的每期结果
            echo 'getkuai3--';
            $type = 'update';
            $kuai3_datas = getkuai3($type);
            echo $kuai3_datas['current']['periodNumber'];
            echo 'getkuai3--';
            if (F('kuai3_periodNumber')!= $kuai3_datas['current']['periodNumber']) {
                $res = M('kuainumber')->where("periodnumber = {$kuai3_datas['current']['periodNumber']}")->find();
                if (!$res) {
                    $map['awardnumbers'] = $kuai3_datas['current']['awardNumbers'];
                    $map['awardtime'] = $kuai3_datas['current']['awardTime'];
                    $map['time'] = strtotime($kuai3_datas['current']['awardTime']);
                    $map['periodnumber'] = $kuai3_datas['current']['periodNumber'];
                    $info = explode(',', $map['awardnumbers']);
                    $n1 = $info[0];
                    $n2 = $info[1];
                    $n3 = $info[2];
                    //总和，赋值给总和.
                    $alln = $n1+$n2+$n3;
                    //判断是否为顺子
                    $ss = $n1.$n2.$n3;
                    $shunzi = '';
                    if(preg_match('/^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/',$ss)){
                        $shunzi = "三连号";
                    }else{
                        $shunzi = "非三连号";
                    }
                    //判断是否为豹子
                    $bz = '二同号';
                    if($n1 ==$n2 && $n1 ==$n3 && $n2 ==$n3 ){
                        $bz = "三同号";
                    }elseif($n1 !==$n2 && $n1 !==$n3 && $n2 !==$n3 ){
                        $bz = "三不同";
                    }
                    //判断对子
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
                    $erbutongdan = '二不同';
                    $dz = '';
                    if($duizinum == 1){
                        $dz = "二同号";
                        if($n1 ==$n2 &&$n1 !==$n3){
                            $erbutongdan = $n1;
                        }elseif ($n1 ==$n3 &&$n1 !==$n2 ){
                            $erbutongdan = $n1;
                        }elseif ($n1 !==$n2 && $n1!==$n3 &&$n2 ==$n3){
                            $erbutongdan = $n2;
                        }
                    }else{
                        $dz = "二不同";
                    }
                    $map['zonghe'] = $alln;
                    $map['ertonghao'] =$dz;
                    $map['erbutongdan'] = $erbutongdan;
                    $map['santonghaotong'] =$bz;
                    $map['sz'] = $shunzi;
                    $map['game'] = 'kuai3';
                    $res1 = M('kuainumber')->add($map);
                    echo "daole";
                    if ($res1) {
                        echo "daole---------------";
                        F('kuai3_periodNumber', $kuai3_datas['current']['periodNumber']);
                        F('getkuai3data', $kuai3_datas);
                    }
                }
            }
            //10.存储加拿大的每期的结果----------------------------------------------------------------------------------------
            echo "getjnd--";
            $type = 'update';
            $jnd_datas = getJnd28($type);
            echo $jnd_datas['current']['periodNumber'];
            echo "getjnd--";
            if (F('jnd_periodNumber') != $jnd_datas['current']['periodNumber']) {
                $res = M('dannumber')->where("periodnumber = {$jnd_datas['current']['periodNumber']}")->find();
                if (!$res) {
                    $map['awardnumbers'] = $jnd_datas['current']['awardNumbers'];
                    $map['awardtime'] = $jnd_datas['current']['awardTime'];
                    $map['time'] = strtotime($jnd_datas['current']['awardTime']);
                    $map['periodnumber'] = $jnd_datas['current']['periodNumber'];
                    $info = explode(',', $map['awardnumbers']);
                    $n1 = $info[0];
                    $n2 = $info[1];
                    $n3 = $info[2];
                    $alln = $n1+$n2+$n3;
                    //总和，赋值给总和.
                    $map['zonghe'] = $alln;
                    //判断单双
                    if($alln %2 == 0){
                        $jiou = "双";
                    }else{
                        $jiou = "单";
                    }
                    $map['danshuang'] = $jiou;
                    //判断大小单双
                    if($jiou == "双"){
                        if(0<= $alln&&$alln<=13){
                            $daxiaodanshuang ="小双";
                        }else{
                            $daxiaodanshuang = "大双";
                        }
                    }
                    if($jiou =="单"){
                        if(0<= $alln&&$alln<=13){
                            $daxiaodanshuang = "小单";
                        }else{
                            $daxiaodanshuang = "大单";
                        }
                    }
                    //储存大小单双到服务器
                    $map['dxds'] = $daxiaodanshuang;
                    // 判断极值
                    $jizhi = "";
                    if(0<=$alln && $alln<=5){
                        $jizhi = "极小";
                    }
                    if(5<$alln && $alln<22){
                        $jizhi = "非极";
                    }
                    if(22<=$alln && $alln<=27){
                        $jizhi = "极大";
                    }
                    //判断是否为顺子
                    $shunzi = "";
                    $ss = $n1.$n2.$n3;
                    if(preg_match('/^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/',$ss)){
                        $shunzi = "顺子";
                    }else{
                        $shunzi = "非顺子";
                    }
                    //判断是否为豹子
                    $bz = "";
                    if($n1 ==$n2 && $n1 ==$n3 && $n2 ==$n3 ){
                        $bz = "豹子";
                    }else{
                        $bz = "非豹子";
                    }
                    //判断为的大小
                    $dx = "";
                    if($alln<=13){
                        $dx = "小";
                    }else{
                        $dx = "大";
                    }
                    //判断对子
                    $dz = "";
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
                    if($duizinum == 1){
                        $dz = "对子";
                    }else{
                        $dz = "非对子";
                    }
                    $map['dz'] =$dz;
                    $map['dx'] = $dx;
                    $map['bz'] =$bz;
                    $map['sz'] = $shunzi;
                    $map['jz'] =$jizhi;
                    $map['number'] = "55";
                    $map['game'] = 'Jnd28';
                    $res1 = M('dannumber')->add($map);
                    if ($res1) {
                        F('jnd_periodNumber', $jnd_datas['current']['periodNumber']);
                        F('getjnd28data', $jnd_datas);
                    }

                }
//
            }
            //10.存储北京28的每期的结果----------------------------------------------------------------------------------------
            $jianadabegin = strtotime('00:00:00');
            $jianadaend= strtotime("09:00:00");
//            if($jianadabegin<time() && time()<$jianadaend){
//                $type = 'update';
//                $bj28_datas = getJnd28($type);
//            }else{
                echo "getbj28--";
                $type = 'update';
                $bj28_datas = getBj28($type);
                echo $bj28_datas['current']['periodNumber'];
                echo "--getbj28";
//            }
            if (F('bj28_periodNumber') != $bj28_datas['current']['periodNumber']) {
                $res = M('dannumber')->where("periodnumber = {$bj28_datas['current']['periodNumber']}")->find();
                if (!$res) {
                    $map['awardnumbers'] = $bj28_datas['current']['awardNumbers'];
                    $map['awardtime'] = $bj28_datas['current']['awardTime'];
                    $map['time'] = strtotime($bj28_datas['current']['awardTime']);
                    $map['periodnumber'] = $bj28_datas['current']['periodNumber'];
                    $info = explode(',', $map['awardnumbers']);
                    $n1 = $info[0];
                    $n2 = $info[1];
                    $n3 = $info[2];
                    $alln = $n1+$n2+$n3;
                    //总和，赋值给总和.
                    $map['zonghe'] = $alln;
                    //判断单双
                    if($alln %2 == 0){
                        $jiou = "双";
                    }else{
                        $jiou = "单";
                    }
                    $map['danshuang'] = $jiou;
                    //判断大小单双
                    if($jiou == "双"){
                        if(0<= $alln&&$alln<=13){
                            $daxiaodanshuang ="小双";
                        }else{
                            $daxiaodanshuang = "大双";
                        }
                    }
                    if($jiou =="单"){
                        if(0<= $alln&&$alln<=13){
                            $daxiaodanshuang = "小单";
                        }else{
                            $daxiaodanshuang = "大单";
                        }
                    }
                    //储存大小单双到服务器
                    $map['dxds'] = $daxiaodanshuang;
                    // 判断极值
                    $jizhi = "";
                    if(0<=$alln && $alln<=5){
                        $jizhi = "极小";
                    }
                    if(5<$alln && $alln<22){
                        $jizhi = "非极";
                    }
                    if(22<=$alln && $alln<=27){
                        $jizhi = "极大";
                    }
                    //判断是否为顺子
                    $shunzi = "";
                    $ss = $n1.$n2.$n3;
                    if(preg_match('/^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/',$ss)){
                        $shunzi = "顺子";
                    }else{
                        $shunzi = "非顺子";
                    }
                    //判断是否为豹子
                    $bz = "";
                    if($n1 ==$n2 && $n1 ==$n3 && $n2 ==$n3 ){
                        $bz = "豹子";
                    }else{
                        $bz = "非豹子";
                    }
                    //判断为的大小
                    $dx = "";
                    if($alln<=13){
                        $dx = "小";
                    }else{
                        $dx = "大";
                    }
                    //判断对子
                    $dz = "";
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
                    if($duizinum == 1){
                        $dz = "对子";
                    }else{
                        $dz = "非对子";
                    }
                    $map['dz'] =$dz;
                    $map['dx'] = $dx;
                    $map['bz'] =$bz;
                    $map['sz'] = $shunzi;
                    $map['jz'] =$jizhi;
                    $map['number'] = "55";
                    $map['game'] = 'Bj28';
                    $res1 = M('dannumber')->add($map);
                    if ($res1) {
                        F('bj28_periodNumber', $bj28_datas['current']['periodNumber']);
                        F('getbj28data', $bj28_datas);
                    }

                }
//
            }
            //10.存储北京pk10的每期的结果----------------------------------------------------------------------------------------
            echo "getpk10--";
            $type = 'update';
            $pk10_datas = getPK10($type);
            echo $pk10_datas['current']['periodNumber'];
            echo "getpk10--";
            if (F('pk10_periodNumber') != $pk10_datas['current']['periodNumber']) {

                $res = M('number')->where("periodnumber = {$pk10_datas['current']['periodNumber']}")->find();
                if (!$res) {
                    $map['awardnumbers'] = $pk10_datas['current']['awardNumbers'];
                    $map['awardtime'] = $pk10_datas['current']['awardTime'];
                    $map['time'] = strtotime($pk10_datas['current']['awardTime']);
                    $map['periodnumber'] = $pk10_datas['current']['periodNumber'];
                    $info = explode(',', $map['awardnumbers']);
                    for ($i = 0; $i < count($info); $i++) {
                        if ($info[$i] < 10) {
                            $info[$i] = substr($info[$i], 1);
                        }
                    }
                    $map['number'] = serialize($info);
                    if ($info[0] > $info[9]) {
                        $lh[0] = '龙';
                    } else {
                        $lh[0] = '虎';
                    }
                    if ($info[1] > $info[8]) {
                        $lh[1] = '龙';
                    } else {
                        $lh[1] = '虎';
                    }
                    if ($info[2] > $info[7]) {
                        $lh[2] = '龙';
                    } else {
                        $lh[2] = '虎';
                    }
                    if ($info[3] > $info[6]) {
                        $lh[3] = '龙';
                    } else {
                        $lh[3] = '虎';
                    }
                    if ($info[4] > $info[5]) {
                        $lh[4] = '龙';
                    } else {
                        $lh[4] = '虎';
                    }
                    $map['lh'] = serialize($lh);
                    $map['tema'] = $info[0] + $info[1];
                    if ($map['tema'] % 2 == 0) {
                        $map['tema_ds'] = '双';
                    } else {
                        $map['tema_ds'] = '单';
                    }
                    if ($map['tema'] >= 12) {
                        $map['tema_dx'] = '大';
                    } else {
                        $map['tema_dx'] = '小';
                    }
                    if ($map['tema'] >= 3 && $map['tema'] <= 7) {
                        $map['tema_dw'] = 'A';
                    }
                    if ($map['tema'] >= 8 && $map['tema'] <= 14) {
                        $map['tema_dw'] = 'B';
                    }
                    if ($map['tema'] >= 15 && $map['tema'] <= 19) {
                        $map['tema_dw'] = 'C';
                    }
                    if ($info[0] > $info[1]) {
                        $map['zx'] = '庄';
                    } else {
                        $map['zx'] = '闲';
                    }
                    $map['game'] = 'pk10';
                    $res1 = M('number')->add($map);
                    if ($res1) {
                        F('pk10_periodNumber', $pk10_datas['current']['periodNumber']);
                        F('getpk10data', $pk10_datas);
                    }
                }
            }
            //10.存储时时彩的每期的结果----------------------------------------------------------------------------------------
            echo "getssc--";
            $type = 'update';
            $ssc_datas = getSsc($type);
            echo "getssc--";
            if (F('ssc_periodNumber') != $ssc_datas['current']['periodNumber']) {
                $res = M('sscnumber')->where("periodnumber = {$ssc_datas['current']['periodNumber']}")->find();
                if (!$res) {
                    $map['awardnumbers'] = $ssc_datas['current']['awardNumbers'];
                    $map['awardtime'] = $ssc_datas['current']['awardTime'];
                    $map['time'] = strtotime($ssc_datas['current']['awardTime']);
                    $map['periodnumber'] = $ssc_datas['current']['periodNumber'];
                    $info = explode(',', $map['awardnumbers']);

                    $da ="";
                    for ($i = 0; $i<count($info);$i++){
                        if ($info[$i]<=4){
                            $da = $da."小/";
                        }else{
                            $da = $da."大/";
                        }
                    }
                    $dansuan = "";
                    for ($b =0 ;$b<count($info);$b++){
                        if (($info[$b]) % 2 == 0){
                            $dansuan = $dansuan."双/";
                        }else{
                            $dansuan = $dansuan."单/";
                        }
                    }
                    $zuhe = "";
                    for ($i = 0;$i<count($info);$i++){
                        $sum = $info[$i];
                        if($sum <=4){
                            if($sum %2 ==0){
                                $zuhe = $zuhe."小双/";
                            }else{
                                $zuhe = $zuhe."小单/";
                            }
                        }else{
                            if($sum %2 !==0){
                                $zuhe = $zuhe."大单/";
                            }else{
                                $zuhe = $zuhe."大双/";
                            }
                        }
                    }
                    $map['zuhe'] = $zuhe;
                    $map['ds'] = $dansuan;
                    $map['dx'] = $da;
                    $map['game'] = 'Ssc';
                    $res1 = M('sscnumber')->add($map);
                    if ($res1) {
                        F('ssc_periodNumber', $ssc_datas['current']['periodNumber']);
                        F('getssc', $ssc_datas);
                    }

                }
//				$this->zidongjiesuan();//存结果的时候顺便结算
            }
        });
        echo "onWorkerStart";
//        $this->onWorkerStart();
    }
    /**
     * 客户端连接时
     * */
    public function onConnect($connection)
    {
        $connection->onWebSocketConnect = function ($connection, $http_header) {
            // 可以在这里判断连接来源是否合法，不合法就关掉连接
            // $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
            if ($_SERVER['HTTP_ORIGIN'] != 'http://pk.aicaiyou.com') {
                //$connection->close();
            }
        };
    }
    /**
     * onMessage
     * @access public
     * 转发客户端消息
     * @param  void
     * @param  void
     * @return void
     */
    public function onMessage($connection, $data)
    {
        $user = session('user');

        // 客户端传递的是json数据
        $message_data = json_decode($data, true);
        if (!$message_data) {
            return;
        }

        // 1:表示执行登陆操作 2:表示执行说话操作 3:表示执行退出操作
        // 根据类型执行不同的业务
        switch ($message_data['type']) {
            // 客户端回应服务端的心跳
            case 'pong' :
                break;
            case 'login_jnd28' :
                // 把昵称放到session中
                $client_name = htmlspecialchars($message_data['client_name']);

                /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
                * 实现针对特定uid推送数据
                */
                $connection->uid = $message_data['client_id'];
                $this->worker->uidConnections[$connection->uid] = $connection;

                //session($connection->uid,$client_name);

                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'jnd28',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '欢迎莅临加拿大28，祝您竞猜愉快！本平台由皇家科技娱乐重金打造！玩法多样，超高赔率，等你来战！下注格式例  大双/500，大/500、14/500，下注成功机器人会提示，下注以机器人提示成功为准',
                    'time' => date('H:i:s')
                );

                $connection->send(json_encode($new_message));
                break;
            case 'login_ssc' :
                // 把昵称放到session中
                $client_name = htmlspecialchars($message_data['client_name']);

                /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
                * 实现针对特定uid推送数据
                */
                $connection->uid = $message_data['client_id'];
                $this->worker->uidConnections[$connection->uid] = $connection;

                //session($connection->uid,$client_name);

                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'ssc',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '欢迎莅临时时彩，祝您竞猜愉快！本平台由皇家科技娱乐重金打造！玩法多样，超高赔率，等你来战！下注格式例  大双/500，大/500、14/500，下注成功机器人会提示，下注以机器人提示成功为准',
                    'time' => date('H:i:s')
                );
                $connection->send(json_encode($new_message));
                break;
            case 'login_pk10':
                // 把昵称放到session中
                $client_name = htmlspecialchars($message_data['client_name']);

                /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
                * 实现针对特定uid推送数据
                */
                $connection->uid = $message_data['client_id'];
                $this->worker->uidConnections[$connection->uid] = $connection;

                //session($connection->uid,$client_name);

                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'pk10',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '欢迎莅临北京pk10，祝您竞猜愉快！本平台由皇家科技娱乐重金打造！玩法多样，超高赔率，等你来战！下注格式例  大双/500，大/500、14/500，下注成功机器人会提示，下注以机器人提示成功为准',
                    'time' => date('H:i:s')
                );
                $connection->send(json_encode($new_message));
                break;
            case 'login_bj28':
                // 把昵称放到session中
                $client_name = htmlspecialchars($message_data['client_name']);

                /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
                * 实现针对特定uid推送数据
                */
                $connection->uid = $message_data['client_id'];
                $this->worker->uidConnections[$connection->uid] = $connection;

                //session($connection->uid,$client_name);

                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'bj28',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '欢迎莅临北京28，祝您竞猜愉快！本平台由皇家科技娱乐重金打造！玩法多样，超高赔率，等你来战！下注格式例  大双/500，大/500、14/500，下注成功机器人会提示，下注以机器人提示成功为准',
                    'time' => date('H:i:s')
                );
                $connection->send(json_encode($new_message));
                break;
            case 'login_kuai3':
                // 把昵称放到session中
                $client_name = htmlspecialchars($message_data['client_name']);

                /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
                * 实现针对特定uid推送数据
                */
                $connection->uid = $message_data['client_id'];
                $this->worker->uidConnections[$connection->uid] = $connection;

                //session($connection->uid,$client_name);

                $new_message = array(
                    'type' => 'admin',
                    'send_type'=>'kuai3',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '欢迎莅临快3，祝您竞猜愉快！本平台由皇家科技娱乐重金打造！玩法多样，超高赔率，等你来战！下注格式例  大双/500，大/500、14/500，下注成功机器人会提示，下注以机器人提示成功为准',
                    'time' => date('H:i:s')
                );
                $connection->send(json_encode($new_message));
                break;
            case 'say_jnd28':
                $userid = $connection->uid;
                /*是否竞猜时间*/
                $jndstatus = F('jndstatus');
                if ($jndstatus == 0) {
                    $time_error_message = array(
                        'uid' => $connection->uid,
                        'type' => 'say',
                        'room'=>$message_data['room'],
                        'send_type'=>'jnd28',
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_error_message));
                    $time_error_message['type'] = 'say_error';
                    $type='jnd28';
                    $this->add_message($time_error_message,$type);/*添加信息*/

                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'room'=>$message_data['room'],
                        'send_type'=>'jnd28',
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '「' . $message_data['content'] . '」' . '非竞猜时间，竞猜失败',
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));
                    $time_message['type'] = 'error';
                    $type='jnd28';
                    $this->add_message($time_message,$type);/*添加信息*/
                } else {
                    /*检测格式和金额*/
                    $res = check_format_jnd($message_data['content'],$connection->uid,$message_data['room']);
                    if ($res['error'] == 0) {
                        $error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'room'=>$message_data['room'],
                            'send_type'=>'jnd28',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );

                        $connection->send(json_encode($error_message));
                        $error_message['type'] = 'say_error';
                        $type='jnd28';
                        $this->add_message($error_message,$type);/*添加信息*/
                        $new_message = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'room'=>$message_data['room'],
                            'send_type'=>'jnd28',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '竞猜金额为'.$res['money'].',竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message));
                        $new_message['type'] = 'error';
                        $type='jnd28';
                        $this->add_message($new_message,$type);/*添加信息*/
                    } else if ($res['type']) {
                        /*查询积分*/
                        $jifen = M('user')->where("id = $userid")->find();
                        if ($jifen['points'] < $res['points']) {
                            $points_error = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'room'=>$message_data['room'],
                                'send_type'=>'jnd28',
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_error));
                            $points_error['type'] = 'say_error';
                            $type='jnd28';
                            $this->add_message($points_error,$type);/*添加信息*/

                            $points_tips = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'room'=>$message_data['room'],
                                'send_type'=>'jnd28',
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' => '「' . $message_data['content'] . '」' . '点数不足，竞猜失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_tips));
                            $points_tips['type'] = 'error';
                            $type='jnd28';
                            $this->add_message($points_tips,$type);/*添加信息*/
                        } else {
                            $user = M('user')->where("id = $userid")->find();
                            $getjnd28data = F('getjnd28data');
                            $map['userid'] = $userid;
                            $map['room'] = $message_data['room'];
                            $map['type'] = $res['type'];
                            $map['state'] = 1;
                            $map['is_add'] = 0;
                            $map['time'] = time();
                            $map['number'] = $getjnd28data['next']['periodNumber'];
                            $map['jincai'] = $message_data['content'];
                            $map['del_points'] = $res['points'];
                            $map['balance'] = $user['points'] - $map['del_points'];
                            $map['nickname'] = $message_data['client_name'];
                            $map['game'] = 'Jnd28';
                            /*添加竞猜*/
                            $return = $this->add_order($map);
                            if ($return) {
                                /*减分*/
                                $res_points = M('user')->where("id = $userid")->setDec('points', $map['del_points']);

                                if ($res_points) {
                                    $points_del = array(
                                        'type' => 'points',
                                        'content' => $res['points'],
                                        'time' => date('H:i:s')
                                    );
                                    $connection->send(json_encode($points_del));
                                }

                                $new_message2 = array(
                                    'uid' => $connection->uid,
                                    'room'=>$message_data['room'],
                                    'type' => 'say',
                                    'send_type'=>'jnd28',
                                    'head_img_url' => $message_data['headimgurl'],
                                    'from_client_name' => $message_data['client_name'],
                                    'content' => $message_data['content'],
                                    'time' => date('H:i:s')
                                );
                                foreach ($this->worker->uidConnections as $con) {
                                    $con->send(json_encode($new_message2));
                                }
                                $type='jnd28';
                                $add_return = $this->add_message($new_message2,$type);/*添加信息*/

                                if ($add_return) {
                                    /*成功通知*/
                                    $new_message1 = array(
                                        'uid' => $connection->uid,
                                        'type' => 'admin',
                                        'room'=>$message_data['room'],
                                        'send_type'=>'jnd28',
                                        'head_img_url' => '/Public/main/img/kefu.jpg',
                                        'from_client_name' => 'GM管理员',
                                        'content' => '@'.$user['nickname'].'「' . $message_data['content'] . '」' . ',竞猜成功',
                                        'time' => date('H:i:s')
                                    );
                                    $connection->send(json_encode($new_message1));
                                    $new_message1['type'] = 'error';
                                    $type='jnd28';
                                    $this->add_message($new_message1,$type);/*添加信息*/
                                }
                            }
                        }
                    } else {
                        $format_error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'room'=>$message_data['room'],
                            'send_type'=>'jnd28',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($format_error_message));
                        $format_error_message['type'] = 'say_error';
                        $type='jnd28';
                        $this->add_message($format_error_message,$type);/*添加信息*/
                        $new_message3 = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'room'=>$message_data['room'],
                            'send_type'=>'jnd28',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '格式不正确,竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message3));
                        $new_message3['type'] = 'error';
                        $type='jnd28';
                        $this->add_message($new_message3,$type);/*添加信息*/
                    }
                }
                break;
            case 'say_ssc':
                $userid = $connection->uid;
                /*是否竞猜时间*/
                $ssc_status = F('ssc_status');
                if ($ssc_status == 0) {
                    $time_error_message = array(
                        'uid' => $connection->uid,
                        'type' => 'say',
                        'send_type'=>'ssc',
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_error_message));
                    $time_error_message['type'] = 'say_error';
                    $type='ssc';
                    $this->add_message($time_error_message,$type);/*添加信息*/

                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type'=>'ssc',
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '「' . $message_data['content'] . '」' . '非竞猜时间，竞猜失败',
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));
                    $time_message['type'] = 'error';
                    $type='ssc';
                    $this->add_message($time_message,$type);/*添加信息*/
                } else {
                    /*检测格式和金额*/
                    $res = check_format_ssc($message_data['content'],$connection->uid);
                    if ($res['error'] == 0) {
                        $error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'send_type'=>'ssc',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );

                        $connection->send(json_encode($error_message));
                        $error_message['type'] = 'say_error';
                        $type='ssc';
                        $this->add_message($error_message,$type);/*添加信息*/
                        $new_message = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'send_type'=>'ssc',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '单笔点数'.$res['money'].'竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message));
                        $new_message['type'] = 'error';
                        $type='ssc';
                        $this->add_message($new_message,$type);/*添加信息*/
                    } else if ($res['type']) {
                        /*查询积分*/
                        $jifen = M('user')->where("id = $userid")->find();
                        if ($jifen['points'] < $res['points']) {
                            $points_error = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type'=>'ssc',
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_error));
                            $points_error['type'] = 'say_error';
                            $type='ssc';
                            $this->add_message($points_error,$type);/*添加信息*/

                            $points_tips = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type'=>'ssc',
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' => '「' . $message_data['content'] . '」' . '点数不足，竞猜失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_tips));
                            $points_tips['type'] = 'error';
                            $type='ssc';
                            $this->add_message($points_tips,$type);/*添加信息*/
                        } else {
                            $user = M('user')->where("id = $userid")->find();
                            $getssc = F('getssc');
                            $map['userid'] = $userid;
                            $map['type'] = $res['type'];
                            $map['state'] = 1;
                            $map['is_add'] = 0;
                            $map['time'] = time();
                            $map['number'] = $getssc['next']['periodNumber'];
                            $map['jincai'] = $message_data['content'];
                            $map['del_points'] = $res['points'];
                            $map['balance'] = $user['points'] - $map['del_points'];
                            $map['nickname'] = $message_data['client_name'];
                            $map['game'] = 'Ssc';
                            /*添加竞猜*/
                            $return = $this->add_order($map);
                            if ($return) {
                                /*减分*/
                                $res_points = M('user')->where("id = $userid")->setDec('points', $map['del_points']);

                                if ($res_points) {
                                    $points_del = array(
                                        'type' => 'points',
                                        'content' => $res['points'],
                                        'time' => date('H:i:s')
                                    );
                                    $connection->send(json_encode($points_del));
                                }

                                $new_message2 = array(
                                    'uid' => $connection->uid,
                                    'type' => 'say',
                                    'send_type'=>'ssc',
                                    'head_img_url' => $message_data['headimgurl'],
                                    'from_client_name' => $message_data['client_name'],
                                    'content' => $message_data['content'],
                                    'time' => date('H:i:s')
                                );
                                foreach ($this->worker->uidConnections as $con) {
                                    $con->send(json_encode($new_message2));
                                }
                                $type='ssc';
                                $add_return = $this->add_message($new_message2,$type);/*添加信息*/

                                if ($add_return) {
                                    /*成功通知*/
                                    $new_message1 = array(
                                        'uid' => $connection->uid,
                                        'type' => 'admin',
                                        'send_type'=>'ssc',
                                        'head_img_url' => '/Public/main/img/kefu.jpg',
                                        'from_client_name' => 'GM管理员',
                                        'content' => '@'.$user['nickname'].'「' . $message_data['content'] . '」' . ',竞猜成功',
                                        'time' => date('H:i:s')
                                    );
                                    $connection->send(json_encode($new_message1));
                                    $new_message1['type'] = 'error';
                                    $type='ssc';
                                    $this->add_message($new_message1,$type);/*添加信息*/
                                }
                            }
                        }
                    } else {
                        $format_error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'send_type'=>'ssc',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($format_error_message));
                        $format_error_message['type'] = 'say_error';
                        $type='ssc';
                        $this->add_message($format_error_message,$type);/*添加信息*/

                        $new_message3 = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'send_type'=>'ssc',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '格式不正确,竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message3));
                        $new_message3['type'] = 'error';
                        $type='ssc';
                        $this->add_message($new_message3,$type);/*添加信息*/
                    }
                }
                break;
            case 'say_bj28':
                $userid = $connection->uid;
                /*是否竞猜时间*/
                $jndstatus = F('bj28_status');
                if ($jndstatus == 0) {
                    $time_error_message = array(
                        'uid' => $connection->uid,
                        'room'=>$message_data['room'],
                        'type' => 'say',
                        'send_type'=>'bj28',
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_error_message));
                    $time_error_message['type'] = 'say_error';
                    $type='bj28';
                    $this->add_message($time_error_message,$type);/*添加信息*/

                    $time_message = array(
                        'uid' => $connection->uid,
                        'room'=>$message_data['room'],
                        'type' => 'admin',
                        'send_type'=>'bj28',
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '「' . $message_data['content'] . '」' . '非竞猜时间，竞猜失败',
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));
                    $time_message['type'] = 'error';
                    $type='bj28';
                    $this->add_message($time_message,$type);/*添加信息*/
                } else {
                    /*检测格式和金额*/
                    $res = check_format_jnd($message_data['content'],$connection->uid,$message_data['room']);
                    if ($res['error'] == 0) {
                        $error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'room'=>$message_data['room'],
                            'send_type'=>'bj28',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );

                        $connection->send(json_encode($error_message));
                        $error_message['type'] = 'say_error';
                        $type='bj28';
                        $this->add_message($error_message,$type);/*添加信息*/
                        $new_message = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'room'=>$message_data['room'],
                            'send_type'=>'bj28',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '竞猜金额为'.$res['money'].',竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message));
                        $new_message['type'] = 'error';
                        $type='bj28';
                        $this->add_message($new_message,$type);/*添加信息*/
                    } else if ($res['type']) {
                        /*查询积分*/
                        $jifen = M('user')->where("id = $userid")->find();
                        if ($jifen['points'] < $res['points']) {
                            $points_error = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'room'=>$message_data['room'],
                                'send_type'=>'bj28',
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_error));
                            $points_error['type'] = 'say_error';
                            $type='bj28';
                            $this->add_message($points_error,$type);/*添加信息*/

                            $points_tips = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'room'=>$message_data['room'],
                                'send_type'=>'bj28',
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' => '「' . $message_data['content'] . '」' . '点数不足，竞猜失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_tips));
                            $points_tips['type'] = 'error';
                            $type='bj28';
                            $this->add_message($points_tips,$type);/*添加信息*/
                        } else {
                            $user = M('user')->where("id = $userid")->find();
                            $getbj28data = F('getbj28data');
                            $map['userid'] = $userid;
                            $map['type'] = $res['type'];
                            $map['room'] = $message_data['room'];
                            $map['state'] = 1;
                            $map['is_add'] = 0;
                            $map['time'] = time();
                            $map['number'] = $getbj28data['next']['periodNumber'];
                            $map['jincai'] = $message_data['content'];
                            $map['del_points'] = $res['points'];
                            $map['balance'] = $user['points'] - $map['del_points'];
                            $map['nickname'] = $message_data['client_name'];
                            $map['game'] = 'Bj28';
                            /*添加竞猜*/
                            $return = $this->add_order($map);
                            if ($return) {
                                /*减分*/
                                $res_points = M('user')->where("id = $userid")->setDec('points', $map['del_points']);

                                if ($res_points) {
                                    $points_del = array(
                                        'type' => 'points',
                                        'content' => $res['points'],
                                        'time' => date('H:i:s')
                                    );
                                    $connection->send(json_encode($points_del));
                                }

                                $new_message2 = array(
                                    'uid' => $connection->uid,
                                    'type' => 'say',
                                    'room'=>$message_data['room'],
                                    'send_type'=>'bj28',
                                    'head_img_url' => $message_data['headimgurl'],
                                    'from_client_name' => $message_data['client_name'],
                                    'content' => $message_data['content'],
                                    'time' => date('H:i:s')
                                );
                                foreach ($this->worker->uidConnections as $con) {
                                    $con->send(json_encode($new_message2));
                                }
                                $type='bj28';
                                $add_return = $this->add_message($new_message2,$type);/*添加信息*/

                                if ($add_return) {
                                    /*成功通知*/
                                    $new_message1 = array(
                                        'uid' => $connection->uid,
                                        'type' => 'admin',
                                        'room'=>$message_data['room'],
                                        'send_type'=>'bj28',
                                        'head_img_url' => '/Public/main/img/kefu.jpg',
                                        'from_client_name' => 'GM管理员',
                                        'content' => '@'.$user['nickname'].'「' . $message_data['content'] . '」' . ',竞猜成功',
                                        'time' => date('H:i:s')
                                    );
                                    $connection->send(json_encode($new_message1));
                                    $new_message1['type'] = 'error';
                                    $type='bj28';
                                    $this->add_message($new_message1,$type);/*添加信息*/
                                }
                            }
                        }
                    } else {
                        $format_error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'room'=>$message_data['room'],
                            'send_type'=>'bj28',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($format_error_message));
                        $format_error_message['type'] = 'say_error';
                        $type='bj28';
                        $this->add_message($format_error_message,$type);/*添加信息*/
                        $new_message3 = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'room'=>$message_data['room'],
                            'send_type'=>'bj28',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '格式不正确,竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message3));
                        $new_message3['type'] = 'error';
                        $type='bj28';
                        $this->add_message($new_message3,$type);/*添加信息*/
                    }
                }
                break;
            case 'say_pk10':
                $userid = $connection->uid;
                /*是否竞猜时间*/
                $state = F('pk10_status');
                if ($state == 0) {
                    $time_error_message = array(
                        'uid' => $connection->uid,
                        'type' => 'say',
                        'send_type'=>'pk10',
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_error_message));
                    $time_error_message['type'] = 'say_error';
                    $this->add_message($time_error_message,$type='pk10');/*添加信息*/

                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type'=>'pk10',
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '「' . $message_data['content'] . '」' . '非竞猜时间，竞猜失败',
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));
                    $time_message['type'] = 'error';
                    $this->add_message($time_message,$type);/*添加信息*/
                } else {
                    /*检测格式和金额*/
                    $res = check_format($message_data['content']);
                    if ($res['error'] == 0) {
                        $error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'send_type'=>'pk10',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($error_message));
                        $error_message['type'] = 'say_error';
                        $this->add_message($error_message,$type='pk10');/*添加信息*/

                        $new_message = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'send_type'=>'pk10',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '单笔点数最低10,竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message));
                        $new_message['type'] = 'error';
                        $this->add_message($new_message,$type='pk10');/*添加信息*/
                    } else if ($res['type']) {
                        /*查询积分*/
                        $jifen = M('user')->where("id = $userid")->find();
                        if ($jifen['points'] < $res['points']) {
                            $points_error = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type'=>'pk10',
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_error));
                            $points_error['type'] = 'say_error';
                            $this->add_message($points_error,$type='pk10');/*添加信息*/

                            $points_tips = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type'=>'pk10',
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' => '「' . $message_data['content'] . '」' . '点数不足，竞猜失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_tips));
                            $points_tips['type'] = 'error';
                            $this->add_message($points_tips,$type='pk10');/*添加信息*/
                        } else {
                            $user = M('user')->where("id = $userid")->find();
                            $pk10data = F('getpk10data');
                            $map['userid'] = $userid;
                            $map['type'] = $res['type'];
                            $map['state'] = 1;
                            $map['is_add'] = 0;
                            $map['time'] = time();
                            $map['number'] = $pk10data['next']['periodNumber'];
                            $map['jincai'] = $message_data['content'];
                            $map['del_points'] = $res['points'];
                            $map['balance'] = $user['points'] - $map['del_points'];
                            $map['nickname'] = $message_data['client_name'];
                            $map['game'] = 'pk10';
                            /*添加竞猜*/
                            $return = $this->add_order($map);
                            if ($return) {
                                /*减分*/
                                $res_points = M('user')->where("id = $userid")->setDec('points', $map['del_points']);

                                if ($res_points) {
                                    $points_del = array(
                                        'type' => 'points',
                                        'content' => $res['points'],
                                        'time' => date('H:i:s')
                                    );
                                    $connection->send(json_encode($points_del));
                                }

                                $new_message2 = array(
                                    'uid' => $connection->uid,
                                    'type' => 'say',
                                    'send_type'=>'pk10',
                                    'head_img_url' => $message_data['headimgurl'],
                                    'from_client_name' => $message_data['client_name'],
                                    'content' => $message_data['content'],
                                    'time' => date('H:i:s')
                                );
                                foreach ($this->worker->uidConnections as $con) {
                                    $con->send(json_encode($new_message2));
                                }
                                $add_return = $this->add_message($new_message2,$type='pk10');/*添加信息*/

                                if ($add_return) {
                                    /*成功通知*/
                                    $new_message1 = array(
                                        'uid' => $connection->uid,
                                        'type' => 'admin',
                                        'send_type'=>'pk10',
                                        'head_img_url' => '/Public/main/img/kefu.jpg',
                                        'from_client_name' => 'GM管理员',
                                        'content' => '@' . $user['nickname'] . '「' . $message_data['content'] . '」' . ',竞猜成功',
                                        'time' => date('H:i:s')
                                    );
                                    $connection->send(json_encode($new_message1));
                                    $new_message1['type'] = 'error';
                                    $this->add_message($new_message1,$type='pk10');/*添加信息*/
                                }
                            }
                        }
                    } else {
                        $format_error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'send_type'=>'pk10',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($format_error_message));
                        $format_error_message['type'] = 'say_error';
                        $this->add_message($format_error_message,$type='pk10');/*添加信息*/

                        $new_message3 = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'send_type'=>'pk10',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '格式不正确,竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message3));
                        $new_message3['type'] = 'error';
                        $this->add_message($new_message3,$type='pk10');/*添加信息*/
                    }
                }
                break;
            case 'say_kuai3':
                $userid = $connection->uid;
                /*是否竞猜时间*/
                $state = F('kuai3_status');
                if ($state == 0) {
                    $time_error_message = array(
                        'uid' => $connection->uid,
                        'type' => 'say',
                        'send_type'=>'kuai3',
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_error_message));
                    $time_error_message['type'] = 'say_error';
                    $this->add_message($time_error_message,$type='kuai3');/*添加信息*/

                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type'=>'kuai3',
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '「' . $message_data['content'] . '」' . '非竞猜时间，竞猜失败',
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));
                    $time_message['type'] = 'error';
                    $this->add_message($time_message,$type='kuai3');/*添加信息*/
                } else {
                    /*检测格式和金额*/
                    $res = check_format_kuai3($message_data['content']);
                    if ($res['error'] == 0) {
                        $error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'send_type'=>'kuai3',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($error_message));
                        $error_message['type'] = 'say_error';
                        $this->add_message($error_message,$type='kuai3');/*添加信息*/

                        $new_message = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'send_type'=>'kuai3',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '竞猜金额为'.$res['money'].',竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message));
                        $new_message['type'] = 'error';
                        $this->add_message($new_message,$type='kuai3');/*添加信息*/
                    } else if ($res['type']) {
                        /*查询积分*/
                        $jifen = M('user')->where("id = $userid")->find();
                        if ($jifen['points'] < $res['points']) {
                            $points_error = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type'=>'kuai3',
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_error));
                            $points_error['type'] = 'say_error';
                            $this->add_message($points_error,$type='kuai3');/*添加信息*/

                            $points_tips = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type'=>'kuai3',
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' => '「' . $message_data['content'] . '」' . '点数不足，竞猜失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_tips));
                            $points_tips['type'] = 'error';
                            $this->add_message($points_tips,$type='kuai3');/*添加信息*/
                        } else {
                            $user = M('user')->where("id = $userid")->find();
                            $pk10data = F('getkuai3data');
                            $map['userid'] = $userid;
                            $map['type'] = $res['type'];
                            $map['state'] = 1;
                            $map['is_add'] = 0;
                            $map['time'] = time();
                            $map['number'] = $pk10data['next']['periodNumber'];
                            $map['jincai'] = $message_data['content'];
                            $map['del_points'] = $res['points'];
                            $map['balance'] = $user['points'] - $map['del_points'];
                            $map['nickname'] = $message_data['client_name'];
                            $map['game'] = 'kuai3';
                            /*添加竞猜*/
                            $return = $this->add_order($map);
                            if ($return) {
                                /*减分*/
                                $res_points = M('user')->where("id = $userid")->setDec('points', $map['del_points']);

                                if ($res_points) {
                                    $points_del = array(
                                        'type' => 'points',
                                        'content' => $res['points'],
                                        'time' => date('H:i:s')
                                    );
                                    $connection->send(json_encode($points_del));
                                }

                                $new_message2 = array(
                                    'uid' => $connection->uid,
                                    'type' => 'say',
                                    'send_type'=>'kuai3',
                                    'head_img_url' => $message_data['headimgurl'],
                                    'from_client_name' => $message_data['client_name'],
                                    'content' => $message_data['content'],
                                    'time' => date('H:i:s')
                                );
                                foreach ($this->worker->uidConnections as $con) {
                                    $con->send(json_encode($new_message2));
                                }
                                $add_return = $this->add_message($new_message2,$type='kuai3');/*添加信息*/

                                if ($add_return) {
                                    /*成功通知*/
                                    $new_message1 = array(
                                        'uid' => $connection->uid,
                                        'type' => 'admin',
                                        'send_type'=>'kuai3',
                                        'head_img_url' => '/Public/main/img/kefu.jpg',
                                        'from_client_name' => 'GM管理员',
                                        'content' => '@' . $user['nickname'] . '「' . $message_data['content'] . '」' . ',竞猜成功',
                                        'time' => date('H:i:s')
                                    );
                                    $connection->send(json_encode($new_message1));
                                    $new_message1['type'] = 'error';
                                    $this->add_message($new_message1,$type='kuai3');/*添加信息*/
                                }
                            }
                        }
                    } else {
                        $format_error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'send_type'=>'kuai3',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($format_error_message));
                        $format_error_message['type'] = 'say_error';
                        $this->add_message($format_error_message,$type='kuai3');/*添加信息*/

                        $new_message3 = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'send_type'=>'kuai3',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '格式不正确,竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message3));
                        $new_message3['type'] = 'error';
                        $this->add_message($new_message3,$type='kuai3');/*添加信息*/
                    }
                }
                break;
//            case 'robot':
//                if (C('robot') == 1) {
//                    $mess = $this->robot_message();
//                    $robot = $this->robot();
//                    $jndstatus = F('jndstatus');
//                    $new_message = array(
//                        'type' => 'say',
//                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
//                        'from_client_name' => $robot[0]['nickname'],
//                        'content' => $mess[0]['content'],
//                        'time' => date('H:i:s')
//                    );
//                    if ($jndstatus == 1) {
//                        $new_message1['type'] = 'error';
//                        if (65 == 65) {
//                            foreach ($this->worker->uidConnections as $con) {
//                                $con->send(json_encode($new_message));
//                            }
//                            $type = 'jnd28';
//                            $this->add_message($new_message,$type);
//                        }
//                    }
//                }
//                break;
        }

    }
    public function robot_message($type)
    {
        if($type =='pk10'){
            $count = M('robot_message')->where("type = 'pk10'")->count();
            $rand = mt_rand(0, $count - 1); //产生随机数。
            $limit = $rand . ',' . '1';
            $data = M('robot_message')->where("type = 'pk10'")->limit($limit)->select();
            return $data;
        }
        if($type =='ssc'){
            $count = M('robot_message')->where("type = 'ssc'")->count();
            $rand = mt_rand(0, $count - 1); //产生随机数。
            $limit = $rand . ',' . '1';
            $data = M('robot_message')->where("type = 'ssc'")->limit($limit)->select();
            return $data;
        }
        if($type =='jnd28'){
            $count = M('robot_message')->where("type = 'bj28'")->count();
            $rand = mt_rand(0, $count - 1); //产生随机数。
            $limit = $rand . ',' . '1';
            $data = M('robot_message')->where("type = 'bj28'")->limit($limit)->select();
            return $data;
        }

    }
    public function robot()
    {
        $count = M('robot')->where("type = 'bj28'")->count();
        $rand = mt_rand(0, $count - 1); //产生随机数。
        $limit = $rand . ',' . '1';
        $data = M('robot')->where("type = 'bj28'")->limit($limit)->select();
        return $data;
    }
    /**
     * onClose
     * 关闭连接
     * @access public
     * @param  void
     * @return void
     */
    public function onClose($connection)
    {
        $user = session($connection->id);
        foreach ($this->worker->uidConnections as $con) {
            if (!empty($user)) {
                $new_message = array(
                    'type' => 'logout',
                    'from_client_name' => $user,
                    'time' => date('H:i:s')
                );
                $con->send(json_encode($new_message));
            }
        }

        if (isset($connection->uid)) {
            // 连接断开时删除映射
            unset($this->worker->uidConnections[$connection->uid]);
        }
    }


    /**
     * 存竞猜记录和信息
     * */
    protected function add_order($mew_message)
    {
        $res = M('order')->add($mew_message);
        return $res;
    }
    protected function add_message($new_message,$type)
    {
        switch ($type){
            case 'kuai3':
                $res = M('kuai3message')->add($new_message);
                break;
            case 'pk10':
                $res = M('message')->add($new_message);
                break;
            case 'bj28':
                $res = M('danmessage')->add($new_message);
                break;
            case 'jnd28':
                $res = M('jndmessage')->add($new_message);
                break;
            case 'ssc':
                $res = M('sscmessage')->add($new_message);
                break;
        }

        return $res;
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

    /**
     * 竞猜成功通知
     * */
    public function send_msg($type, $points, $userid)
    {
        $message_points = array(
            'type' => $type,
            'points' => $points,
            'time' => date('H:i:s')
        );
        if (isset($this->worker->uidConnections[$userid])) {
            $connection = $this->worker->uidConnections[$userid];
            $connection->send(json_encode($message_points));
        }
    }
}

?>