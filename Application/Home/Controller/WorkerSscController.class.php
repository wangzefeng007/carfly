<?php

namespace Home\Controller;

use Think\Server;

header('content-type:text/html;charset=utf-8');

class WorkerSscController extends Server
{
    protected $socket = 'websocket://0.0.0.0:7275';
    protected $processes = 1000;
    /**
     * 添加定时器
     *监控连接状态
     * */
    public function onWorkerStart()
    {
        $beginToday = strtotime('09:00:00');
        $endToday = strtotime("23:59:59");

//        F('url', 'http://m.ndz886.com/home/api/getPk10');
        //F('url','http://pk.w3s.wang/Home/Index/getPk101?t=');
        $time_interval = 1;
        //机器人随机数
        $jiqiren = rand(0,10);

        //获取数据
        $pkdata = getSsc();
        //判断是否开盘和判断是否有缓存
        $nexttime = $pkdata['next']['delayTimeInterval'] + strtotime($pkdata['next']['awardTime']);
        //如果时间在开盘的时间.
        if ($nexttime - time() > 8 && $nexttime - time() < 300 && time() > $beginToday && time() < $endToday) {
            F('ssc_status', 1);//开盘
        } else {
            F('ssc_status', 0);
        }
        //把api获取的值缓存到服务器.
        if (!F('getssc')) {
            F('getssc', $pkdata);
        }
        //
        if (!F('id_sscsend')) {
            F('id_sscsend', 1);
        }
        /*开奖time*/
        \Workerman\Lib\Timer::add($time_interval, function () {
            $beginToday = strtotime('09:00:00');
            $endToday = strtotime("23:59:59");
            //获取缓存.
            $getssc = F('getssc');
            //再次判断状态.
            $next_time = $getssc['next']['delayTimeInterval'] + strtotime($getssc['next']['awardTime']);
            if ($next_time - time() > 8 && $next_time - time() < 600 && time() > $beginToday && time() < $endToday) {
                F('ssc_status', 1);
            } else {
                F('ssc_status', 0);
            }
            //在距离38s的时候，提醒一次
            if ($next_time - time() == 38) {
                $new_message = array(
                    'type' => 'admin',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getssc['next']['periodNumber'] . '<br/>' . '--距离封盘还有30秒--',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
            }
            //如果时间小于8秒的时候，提醒投注并关闭
            if ($next_time - time() == 8) {
                F('id_sscsend', 0);
                $new_message = array(
                    'type' => 'admin',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getssc['next']['periodNumber'] . '<br/>' . '--关闭，请耐心等待--',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
            }
            if ($next_time - time() < 600 && $next_time - time() > 180 && F('id_sscsend') == 0 || F('id_sscsend') == 0 && $next_time - time() > 300) {
                //结算
                F('id_sscsend', 1);
                F('ssc_status', 1);
                $new_message = array(
                    'delay' => '8',
                    'type' => 'admin',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $getssc['next']['periodNumber'] ."<br>". '开放，请各位老板开始下注',
                    'time' => date('H:i:s')
                );
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
            }
            /*添加信息,保存到数据库中， html初始化的时候会调用数据库中的数据*/
            $this->add_message($new_message);
        });
        //重载，在当天时间的最后10分钟提醒一次
        \Workerman\Lib\Timer::add($time_interval, function () {
            $beginToday = strtotime('09:00:00');
            $endToday = strtotime("23:59:59");
            $tips = array(
                'type' => 'system',
                'head_img_url' => '/Public/main/img/system.jpg',
                'from_client_name' => '客服',
                'content' => '重庆时时彩即将关闭！',
                'time' => date('H:i:s')
            );
            if (date('H:i:s') == '23:50:00') {
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($tips));
                }
            }

        });

        //ping 统计人数
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
            //}
        });
        //系统公告
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
        //机器人随机发送消息
        \Workerman\Lib\Timer::add($jiqiren, function () {
            if (C('robot') == 1) {
                $mess = $this->robot_message();
                $robot = $this->robot();
                $ssc_status = F('ssc_status');
                $new_message = array(
                    'type' => 'say',
                    'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
                    'from_client_name' => $robot[0]['nickname'],
                    'content' => $mess[0]['content'],
                    'time' => date('H:i:s')
                );
                if ($ssc_status == 1) {
                    $new_message1['type'] = 'error';
                    if (54 == 54) {
                        foreach ($this->worker->uidConnections as $con) {
                            $con->send(json_encode($new_message));
                        }
                        $this->add_message($new_message);
                    }
                }
            }
        });
        //存每期结果
        \Workerman\Lib\Timer::add(10, function () {
            $data = getSsc();
            if (F('periodNumber') != $data['current']['periodNumber']) {
                $res = M('sscnumber')->where("periodnumber = {$data['current']['periodNumber']}")->find();
                if (!$res) {
                    $map['awardnumbers'] = $data['current']['awardNumbers'];
                    $map['awardtime'] = $data['current']['awardTime'];
                    $map['time'] = strtotime($data['current']['awardTime']);
                    $map['periodnumber'] = $data['current']['periodNumber'];
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
                        F('periodNumber', $data['current']['periodNumber']);
                        F('getssc', $data);
                    }

                }
//				$this->zidongjiesuan();//存结果的时候顺便结算
            }

        });
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
            case 'login' :
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
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '欢迎莅临重庆时时彩，祝您竞猜愉快！本平台由线上娱乐领航者重金打造！玩法多样，超高赔率，等你来战！上下分请联系客服！！！推荐人反水5%，下注隔日反水10%！！！。',
                    'time' => date('H:i:s')
                );
                $connection->send(json_encode($new_message));
                break;
            case 'say':
                $userid = $connection->uid;
                /*是否竞猜时间*/
                $ssc_status = F('ssc_status');
                if ($ssc_status == 0) {
                    $time_error_message = array(
                        'uid' => $connection->uid,
                        'type' => 'say',
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s').strto
                    );
                    $connection->send(json_encode($time_error_message));
                    $time_error_message['type'] = 'say_error';
                    $this->add_message($time_error_message);/*添加信息*/

                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '「' . $message_data['content'] . '」' . '非竞猜时间，竞猜失败',
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));
                    $time_message['type'] = 'error';
                    $this->add_message($time_message);/*添加信息*/
                } else {
                    /*检测格式和金额*/
                    $res = check_format_ssc($message_data['content'],$connection->uid);
                    if ($res['error'] == 0) {
                        $error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );

                        $connection->send(json_encode($error_message));
                        $error_message['type'] = 'say_error';
                        $this->add_message($error_message);/*添加信息*/
                        $new_message = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '单笔点数'.$res['money'].'竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message));
                        $new_message['type'] = 'error';
                        $this->add_message($new_message);/*添加信息*/
                    } else if ($res['type']) {
                        /*查询积分*/
                        $jifen = M('user')->where("id = $userid")->find();
                        if ($jifen['points'] < $res['points']) {
                            $points_error = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_error));
                            $points_error['type'] = 'say_error';
                            $this->add_message($points_error);/*添加信息*/

                            $points_tips = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' => '「' . $message_data['content'] . '」' . '点数不足，竞猜失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($points_tips));
                            $points_tips['type'] = 'error';
                            $this->add_message($points_tips);/*添加信息*/
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
                                    'head_img_url' => $message_data['headimgurl'],
                                    'from_client_name' => $message_data['client_name'],
                                    'content' => $message_data['content'],
                                    'time' => date('H:i:s')
                                );
                                foreach ($this->worker->uidConnections as $con) {
                                    $con->send(json_encode($new_message2));
                                }
                                $add_return = $this->add_message($new_message2);/*添加信息*/

                                if ($add_return) {
                                    /*成功通知*/
                                    $new_message1 = array(
                                        'uid' => $connection->uid,
                                        'type' => 'admin',
                                        'head_img_url' => '/Public/main/img/kefu.jpg',
                                        'from_client_name' => 'GM管理员',
                                        'content' => '@'.$user['nickname'].'「' . $message_data['content'] . '」' . ',竞猜成功',
                                        'time' => date('H:i:s')
                                    );
                                    $connection->send(json_encode($new_message1));
                                    $new_message1['type'] = 'error';
                                    $this->add_message($new_message1);/*添加信息*/
                                }
                            }
                        }
                    } else {
                        $format_error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($format_error_message));
                        $format_error_message['type'] = 'say_error';
                        $this->add_message($format_error_message);/*添加信息*/

                        $new_message3 = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '「' . $message_data['content'] . '」' . '格式不正确,竞猜失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message3));
                        $new_message3['type'] = 'error';
                        $this->add_message($new_message3);/*添加信息*/
                    }
                }
                break;
//            case 'robot':
//                if (C('robot') == 1) {
//                    $mess = $this->robot_message();
//                    $robot = $this->robot();
//                    $ssc_status = F('ssc_status');
//                    $new_message = array(
//                        'type' => 'say',
//                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
//                        'from_client_name' => $robot[0]['nickname'],
//                        'content' => $mess[0]['content'],
//                        'time' => date('H:i:s')
//                    );
//                    if ($ssc_status == 1) {
//                        $new_message1['type'] = 'error';
//                        if ($connection->uid == 54) {
//                            foreach ($this->worker->uidConnections as $con) {
//                                $con->send(json_encode($new_message));
//                            }
//                            $this->add_message($new_message);
//                        }
//                    }
//                }
//                break;
        }
    }
    //机器人发送的信息
    public function robot_message()
    {
        $count = M('robot_message')->where("type = 'ssc'")->count();
        $rand = mt_rand(0, $count - 1); //产生随机数
        $limit = $rand . ',' . '1';
        $data = M('robot_message')->where("type = 'ssc'")->limit($limit)->select();
        return $data;
    }

    public function robot()
    {
        $count = M('robot')->where("type = 'ssc'")->count();
        $rand = mt_rand(0, $count - 1); //产生随机数。
        $limit = $rand . ',' . '1';
        $data = M('robot')->where("type = 'ssc'")->limit($limit)->select();
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
    protected function add_message($new_message)
    {
        $res = M('sscmessage')->add($new_message);
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