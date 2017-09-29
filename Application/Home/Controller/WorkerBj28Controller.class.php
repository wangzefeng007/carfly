<?php

namespace Home\Controller;

use Think\Server;

header('content-type:text/html;charset=utf-8');

class WorkerBj28Controller extends Server
{
    protected $socket = 'websocket://0.0.0.0:7273';
    protected $processes = 1000;

    /**
     * 添加定时器
     *监控连接状态
     * */
    public function onWorkerStart()
    {
        $beginToday = strtotime('00:00:00');
        $endToday = strtotime("23:59:59");

//        F('url', 'http://m.ndz886.com/home/api/getPk10');
//        F('url','http://pk.w3s.wang/Home/Index/getPk101?t=');
        $time_interval = 1;
        //按照北京转加拿大的时间：
        $jianadabegin = strtotime('00:00:00');
        $jianadaend= strtotime("09:00:00");
        if($jianadabegin<time() && time()<$jianadaend){
            $type = 'update';
            $pkdata = getJnd28($type);
        }else{
            $type = 'update';
            $pkdata = getBj28($type);
        }
        //判断是否开盘和判断是否有缓存
        $nexttime = $pkdata['next']['delayTimeInterval'] + strtotime($pkdata['next']['awardTime']);
        //如果时间在开盘的时间.
        if ($nexttime - time() > 20 && $nexttime - time() < 300) {
            F('status', 1);//开盘
        } else {
            F('status', 0);
        }
        //把api获取的值缓存到服务器.
        if (!F('get28data')) {
            F('get28data', $pkdata);
        }
        //
        if (!F('id_dansend')) {
            F('id_dansend', 1);
        }
        /*开奖time*/
        \Workerman\Lib\Timer::add($time_interval, function () {
            $beginToday = strtotime('00:00:00');
            $endToday = strtotime("23:59:59");
            //获取缓存.
            $get28data = F('get28data');
            //再次判断状态.
            $next_time = $get28data['next']['delayTimeInterval'] + strtotime($get28data['next']['awardTime']);
            if ($next_time - time() > 20 && $next_time - time() < 300) {
                F('status', 1);
            } else {
                F('status', 0);
            }
            //在距离38s的时候，提醒一次
            if ($next_time - time() == 38) {
                $new_message = array(
                    'type' => 'admin',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $get28data['next']['periodNumber'] . '<br/>' . '--距离封盘还有30秒--'. '<br/>' . '请各位老板抓紧下注',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
            }
            //如果时间小于8秒的时候，提醒投注并关闭
            if ($next_time - time() == 20) {
                F('id_dansend', 0);
                $new_message = array(
                    'type' => 'admin',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $get28data['next']['periodNumber'] . '<br/>' . '--关闭，请各位老板停止下注--',
                    'time' => date('H:i:s')
                );
                //发给在线用户
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
            }


            //如果投注关闭 展示玩家有效投注信息
//            if ($next_time - time() == 19) {
//                $user=M('order');
//                // 获取当前期数
//                $qishi=$user->order("number DESC")->limit(1)->getField("number");
//                $tonggao=$user->where("number=$qishi")->field('nickname,jincai')->select();
//                $hhh.='期号:' .$qishi . '<br/>';
//                for ($i=0;$i<count($tonggao);$i++){
//                    $hhh.= $tonggao[$i]["nickname"]."&nbsp;&nbsp;投注&nbsp;&nbsp;".$tonggao[$i]["jincai"]."<br/>";
//                }
//
//                F('id_dansend', 0);
//                $new_message = array(
//                    'type' => 'admin',
//                    'head_img_url' => '/Public/main/img/kefu.jpg',
//                    'from_client_name' => 'GM管理员',
//                    'content' => $hhh,
//                    'time' => date('H:i:s')
//                );
//                //发给在线用户
//                foreach ($this->worker->connections as $conn) {
//                    $conn->send(json_encode($new_message));
//                }
//            }
//




            if ($next_time - time() < 300 && $next_time - time() > 180 && F('id_dansend') == 0 || F('id_dansend') == 0 && $next_time - time() > 300) {
                //结算
                F('id_dansend', 1);
                F('status', 1);
                $new_message = array(
                    'delay' => '8',
                    'type' => 'admin',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '期号:' . $get28data['next']['periodNumber']  ."<br>". '开放，请各位老板开始下注',
                    'time' => date('H:i:s')
                );
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($new_message));
                }
            }
            /*添加信息,保存到数据库中， html初始化的时候会调用数据库中的数据*/
            $this->add_message($new_message);
        });
        //即将关闭提醒------------------------------------------------------------------------
        //即将关闭提醒------------------------------------------------------------------------
        \Workerman\Lib\Timer::add($time_interval, function () {
            $tips = array(
                'type' => 'system',
                'head_img_url' => '/Public/main/img/system.jpg',
                'from_client_name' => '客服',
                'content' => '北京28即将关闭！',
                'time' => date('H:i:s')
            );
            if (date('H:i:s') == '23:50:00') {
                foreach ($this->worker->connections as $conn) {
                    $conn->send(json_encode($tips));
                }
            }
        });
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
//        $jiqiren = rand(0,30);
//        //机器人触发-------------------------------------------------------------------------------
//        //机器人触发-------------------------------------------------------------------------------
//        \Workerman\Lib\Timer::add($jiqiren, function () {
//
//                if (C('robot') == 1) {
//                    $mess = $this->robot_message();
//                    $robot = $this->robot();
//                    $status = F('status');
//                    $new_message = array(
//                        'type' => 'say',
//                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
//                        'from_client_name' => $robot[0]['nickname'],
//                        'content' => $mess[0]['content'],
//                        'time' => date('H:i:s')
//                    );
//                    if ($status == 1) {
//                        $new_message1['type'] = 'error';
//                        if (54 == 54) {
//                            foreach ($this->worker->uidConnections as $con) {
//                                $con->send(json_encode($new_message));
//                            }
//                            $this->add_message($new_message);
//                        }
//                    }
//                }
//
//        });
        //存储每期的结果----------------------------------------------------------------------------------------
        //存储每期的结果----------------------------------------------------------------------------------------
        \Workerman\Lib\Timer::add(10, function () {

            $jianadabegin = strtotime('00:00:00');
            $jianadaend= strtotime("09:00:00");
            if ($jianadabegin<time() && time()<$jianadaend){
                $datas = getBj28();
            }else{
                $datas = getJnd28();
            }

//            $this->zidongjiesuan();//存结果的时候顺便结算
        });
    }
    /**
     * 客户端连接时
     * */
    public function onConnect($connection)
    {
//        $connection->onWebSocketConnect = function ($connection, $http_header) {
//            // 可以在这里判断连接来源是否合法，不合法就关掉连接
//            // $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
//            if ($_SERVER['HTTP_ORIGIN'] != 'http://pk.aicaiyou.com') {
//                //$connection->close();
//            }
//        };
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
                    'content' => '欢迎莅临北京28，祝您竞猜愉快！本平台由皇家科技娱乐重金打造！玩法多样，超高赔率，等你来战！，下注成功机器人会提示，下注以机器人提示成功为准',
                    'time' => date('H:i:s')
                );
                $connection->send(json_encode($new_message));
                break;
            case 'say':
                $userid = $connection->uid;
                /*是否竞猜时间*/
                $status = F('status');
                if ($status == 0) {
                    $time_error_message = array(
                        'uid' => $connection->uid,
                        'type' => 'say',
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
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
                    $res = check_format_dan($message_data['content'],$connection->uid);
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
                            'content' => '「' . $message_data['content'] . '」' . '竞猜金额为'.$res['money'].',竞猜失败',
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
                            $get28data = F('get28data');
                            $map['userid'] = $userid;
                            $map['type'] = $res['type'];
                            $map['state'] = 1;
                            $map['is_add'] = 0;
                            $map['time'] = time();
                            $map['number'] = $get28data['next']['periodNumber'];
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
            case 'robot':
                if (C('robot') == 1) {
                    $mess = $this->robot_message();
                    $robot = $this->robot();
                    $status = F('status');
                    $new_message = array(
                        'type' => 'say',
                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
                        'from_client_name' => $robot[0]['nickname'],
                        'content' => $mess[0]['content'],
                        'time' => date('H:i:s')
                    );
                    if ($status == 1) {
                        $new_message1['type'] = 'error';
                        if (54 == 54) {
//                            $connection->uid ==54
                            foreach ($this->worker->uidConnections as $con) {
                                $con->send(json_encode($new_message));
                            }
                            $this->add_message($new_message);
                        }

                    }
                }
                break;
        }
    }
    public function robot_message()
    {
        $count = M('robot_message')->where("type = 'Bj28'")->count();
        $rand = mt_rand(0, $count - 1); //产生随机数。
        $limit = $rand . ',' . '1';
        $data = M('robot_message')->where("type = 'Bj28'")->limit($limit)->select();
        return $data;
    }

    public function robot()
    {
        $count = M('robot')->where("type = 'Bj28'")->count();
        $rand = mt_rand(0, $count - 1); //产生随机数。
        $limit = $rand . ',' . '1';
        $data = M('robot')->where("type = 'Bj28'")->limit($limit)->select();
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
        $res = M('danmessage')->add($new_message);
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