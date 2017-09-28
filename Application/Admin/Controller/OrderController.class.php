<?php

namespace Admin\Controller;

use Think\Controller;

class OrderController extends BaseController
{

    public function array_sort($array,   $key)
    {
        if (is_array($array)) {
            $key_array = null;
            $new_array = null;
            for ($i = 0; $i < count($array); $i++) {
                $key_array[$array[$i][$key]] = $i;
            }
            krsort($key_array);
            $j = 0;
            foreach ($key_array as $k => $v) {
                $new_array[$j] = $array[$v];
                $j++;
            }
            unset($key_array);
            return $new_array;
        } else {
            return $array;
        }
    }

    public function index()
    {
        $nickname = I('nickname');
        $userid = I('userid');
        $time = I('time');
        if ($nickname) {
            $map['nickname'] = array("LIKE", '%' . $nickname . '%');
        }
        if ($userid) {
            $map['userid'] = $userid;
        }
        if ($time) {
            $start = strtotime($time . '00:00:00');
            $end = strtotime($time . '23:59:59');
            $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
        }
        $order = M('order');
        $integral = M('integral');
        $count = $order->where($map)->count() + $integral->where($map)->count();
        $page = new \Think\Page($count, 20);
        $show = $page->show();
        $list1 = $order->where($map)->select();
        $list2 = $integral->where($map)->select();
        $list = array_merge_recursive($list1, $list2);
        $list1 = $this->array_sort($list, 'time');
        for ($i = 0; $i < count($list1); $i++) {
            $list1[$i]['user'] = M('user')->where("id = {$list1[$i]['userid']}")->find();
        }
        $list2 = array_slice($list1, $page->firstRow, $page->listRows);

        $this->assign('list', $list2);
        $this->assign('show', $show);
        $this->display();
    }


    //每日输赢
    public function win_lose()
    {
        $time = I('time');
        if ($time) {
            $start = strtotime($time . '00:00:00');
            $end = strtotime($time . '23:59:59');
            $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
        } else {
            $time = date('Y-m-d');
            $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
        }
        $order = M('order');
        $integral = M('integral');
        $list1 = array();
        $list2 = array();
        $list1 = $order->where($map)->select();
        $list2 = $integral->where($map)->select();
        $list = array_merge_recursive($list1, $list2);
        $list1 = $this->array_sort($list, 'time');

        $add = 0;
        $del = 0;

        for ($i = 0; $i < count($list1); $i++) {
            if ($list1[$i]['uid']) {
                if ($list1[$i]['type'] == 1) {
                    $add = $add + $list1[$i]['points'];
                } else {
                    $del = $del + $list1[$i]['points'];
                }
            } else {
                $add = $add + $list1[$i]['add_points'];
                $del = $del + $list1[$i]['del_points'];
            }
        }

        $this->assign('del', $del);
        $this->assign('add', $add);
        $this->assign('time', $time);
        $this->display();
    }


    //每日输赢
    public function user_win_lose()
    {
        $nickname = I('nickname');
        $userid = I('userid');
        $time = I('time');
        if ($nickname) {
        $map['nickname'] = array("LIKE", '%' . $nickname . '%');
    }
        if ($userid) {
            $map['userid'] = $userid;
        }
        if ($time) {
            $start = strtotime($time . '00:00:00');
            $end = strtotime($time . '23:59:59');
            $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
        } else {
            $time = date('Y-m-d');
            $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
        }
        $order = M('order');
        $integral = M('integral');
        $list1 = array();
        $list2 = array();
        $list1 = $order->where($map)->select();
        $list2 = $integral->where($map)->select();
        $list = array_merge_recursive($list1, $list2);
        $list1 = $this->array_sort($list, 'time');

        $add = 0;
        $del = 0;

        for ($i = 0; $i < count($list1); $i++) {
            if ($list1[$i]['uid']) {
                if ($list1[$i]['type'] == 1) {
                    $add = $add + $list1[$i]['points'];
                } else {
                    $del = $del + $list1[$i]['points'];
                }
            } else {
                $add = $add + $list1[$i]['add_points'];
                $del = $del + $list1[$i]['del_points'];
            }
        }
        $this->assign('nickname', $nickname);
        $this->assign('del', $del);
        $this->assign('add', $add);
        $this->assign('time', $time);
        $this->display();
    }


}


?>