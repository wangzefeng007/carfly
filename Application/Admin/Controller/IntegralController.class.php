<?php

namespace Admin\Controller;

use Think\Controller;

class IntegralController extends BaseController
{

    public function lists()
    {
        $nickname = I('nickname');
        $userid = I('userid');
        $integral = M('integral');
        $time = I('time');
        if ($time) {
            $start = strtotime($time . '00:00:00');
            $end = strtotime($time . '23:59:59');
            $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
        }
        if ($userid) {
            $map['userid'] = $userid;
        }
        if ($nickname) {
            $map['nickname'] = array("LIKE", '%' . $nickname . '%');
        }
        $count = $integral->where($map)->count();
        $page = new \Think\Page($count, 10);
        $show = $page->show();
        $list = $integral->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();

        for ($i = 0; $i < count($list); $i++) {
            $list[$i]['user'] = M('user')->where("id = {$list[$i]['userid']}")->find();
        }
        $this->assign('nickname', $nickname);
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display('lists');
    }

    protected function send($content)
    {
        // 指明给谁推送，为空表示向所有在线用户推送
        $to_uid = "";
        // 推送的url地址，上线时改成自己的服务器地址
        $push_api_url = C('push_api_url');
        $post_data = array(
            "type" => "publish",
            "content" => json_encode($content),
            "to" => $to_uid,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $push_api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }

    public function gonggao()
    {
        if (IS_POST) {
            $gongao = I('gonggao');
            $message = array(
                'time' => date('H:i:s'),
                'content' => $gongao
            );
            $res = $this->send($message);
            $new_message = array(
                'type' => 'system',
                'head_img_url' => '/Public/main/img/system.jpg',
                'from_client_name' => '客服',
                'content' => $gongao,
                'time' => date('H:i:s')
            );
            if ($res) {
                $message = M('message');
                $danmessage = M('danmessage');
                $danmessage->add($new_message);
                $message->add($new_message);
                $this->success('成功');
            } else {
                $this->error('失败');
            }
        } else {
            $this->display();
        }
    }

    public function index()
    {
        if (IS_POST) {
            if (!IS_AJAX) {
                $this->error('提交方式不正确');
            } else {
                $userid = I('userid');
                $points = I('points');
                if (!preg_match('/^[1-9]\d*$/', $points)) {
                    $this->error('充值点数为正整数');
                }
                $res2 = M('user')->where("id = $userid")->setInc('points', $points);
                if ($res2) {
                    $info = M('user')->where("id = $userid")->find();

                    //充值记录
                    $data['userid'] = $userid;
                    $data['time'] = time();
                    $data['points'] = $points;
                    $data['type'] = '1';
                    $data['ip'] = get_client_ip();
                    $data['balance'] = $info['points'];
                    M('integral')->add($data);

                    //是否有人推荐
                    if ($info['t_id']) {
                        if ($points >= C('fenxiao_min')) {//最低充值
                            M('user')->where("id = {$info['t_id']}")->setInc('points',$points*C('fenxiao')*0.01);
                            M('user')->where("id = {$info['t_id']}")->setInc('t_add',$points*C('fenxiao')*0.01);
                        }
                    }
                    $message = array(
                        'to' => $userid,
                        'type' => 'system',
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => '客服',
                        'time' => date('H:i:s'),
                        'content' => '玩家「' . $info['nickname'] . '」上分已受理，请注意查看点数'
                    );
                    M('message')->add($message);
                    $message['points'] = $points;
                    $this->send($message);
                    $this->success('充值成功,跳转中~', U('Admin/Member/index'), 1);
                } else {
                    $this->error('充值失败！');
                }
            }
        } else {
            $id = I('id');
            $userinfo = M('user')->where("id = $id")->find();
            $this->assign('userinfo', $userinfo);
            $this->display();
        }

    }

    public function under()
    {
        if (IS_POST) {
            if (!IS_AJAX) {
                $this->error('提交方式不正确！');
            } else {
                $userid = I('userid');
                $points = I('points');
                if (!preg_match('/^[1-9]\d*$/', $points)) {
                    $this->error('兑换点数为正整数');
                }
                $info = M('user')->where("id = $userid")->find();
                if ($info['points'] < $points) {
                    $this->error('点数不足');
                }
                $res2 = M('user')->where("id = $userid")->setDec('points', $points);
                if ($res2) {

                    //下分记录
                    $data['userid'] = $userid;
                    $data['time'] = time();
                    $data['points'] = $points;
                    $data['type'] = '0';
                    $data['ip'] = get_client_ip();
                    $data['balance'] = $info['points'] - $points;
                    M('integral')->add($data);

                    $message = array(
                        'to' => $userid,
                        'type' => 'system',
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => '客服',
                        'time' => date('H:i:s'),
                        'content' => '玩家「' . $info['nickname'] . '」回分已受理，请确认'
                    );
                    M('message')->add($message);
                    $message['points'] = $points * (-1);
                    $this->send($message);
                    $this->success('下分成功,跳转中~', U('Admin/Member/index'), 1);
                } else {
                    $this->error('下分失败！');
                }
            }
        } else {
            $id = I('id');
            $userinfo = M('user')->where("id = $id")->find();
            $this->assign('userinfo', $userinfo);
            $this->display();
        }
    }

}


?>