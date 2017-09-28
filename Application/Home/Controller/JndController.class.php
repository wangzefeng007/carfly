<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/10
 * Time: 13:42
 */

namespace Home\Controller;
use Think\Controller;

class JndController extends BaseController
{

    public function index(){
        $data = session('user');
        $id = $data['id'];
//        10期结果
        $list = M('dannumber')->where("game = 'Jnd28'")->order("id DESC")->limit(10)->select();
        // 创建SDK实例
        $script = &  load_wechat('Script');
        // 获取JsApi使用签名，通常这里只需要传 $ur l参数
        $url = 'http://'.$_SERVER['SERVER_NAME'].'/Home/Circle/index.html';
        $options = $script->getJsSign($url, $timestamp, $noncestr, $appid);
        $kefu = M('config')->where("id = 1")->find();
        $this->assign('kefu',$kefu);
        $this->assign('list',$list);
        $this->assign('options',$options);
        $this->display();
//        $data = F('dandata');
//        dump($data);
    }
    public function getdata(){
        $data = M('dannumber')->where("game = 'Jnd28'")->order('id DESC')->limit(1)->find();
        return $this->ajaxReturn (json_encode($data),'JSON');
    }
    public function jincai(){
        //聊天信息
        $list = M('jndmessage')->order("id DESC")->limit(20)->select();
        $this->assign('list',$list);
        $this->display();
    }
    /*客服*/
    public function kefu(){
        $kefu = M('config')->where("id = 1")->find();
        $this->assign('kefu',$kefu);
        $this->display();
    }
 
    /*记录*/
    public function record(){
        $t = I('t');
        $pkdata = F('getjnd28data');
        if($t == 1){
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        }
        if($t == 2){
            $beginToday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
        }
        if($t == 3){
            $beginToday=mktime(0,0,0,date('m'),date('d')-2,date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d')-1,date('Y'))-1;
        }
        if($t == 4){
            $beginToday=mktime(0,0,0,date('m'),1,date('Y'));
            $endToday=mktime(23,59,59,date('m'),date('t'),date('Y'));
        }

        $userinfo = session('user');
        $order = M('order');
        $count = $order->where("state=1 && userid = {$userinfo['id']} && time >=$beginToday && time < $endToday && game = 'Jnd28'")->count();
        if($t == 4){
            $page = new \Think\Page($count,7);
        }else{
            $page = new \Think\Page($count,5);
        }
        $show = $page->show();
        $list = $order->where("state=1 && userid = {$userinfo['id']} && time >=$beginToday && time < $endToday && game = 'Jnd28'")->limit($page->firstRow.','.$page->listRows)->order("number DESC")->select();

        if($t!=4){
            $number = array();
            for($i=0;$i<count($list);$i++){
                if(!in_array($list[$i]['number'], $number)){
                    $number[] = $list[$i]['number'];
                }
                for($a=0;$a<count($number);$a++){
                    if($list[$i]['number']==$number[$a]){
                        $list1[$a]['number'] = $number[$a];
                        $list1[$a]['order'][] = $list[$i];
                    }
                }
            }
        }
        //print_r($list1);
        $this->assign('list1',$list1);
        $this->assign('state',F('state'));
        $this->assign('number',$pkdata['next']['periodNumber']);
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->assign('today',mktime(0,0,0,date('m'),date('d'),date('Y')));
        $this->assign('t',$t);
        $this->display();
    }

    //取消
    public function del_all(){
        $state = F('state');
        $userinfo = session('user');
        $pkdata = F('getjnd28data');
        if($state==1){
            $number = I('number');
            $list = M('order')->where("number = {$number} && userid = {$userinfo['id']}")->select();
            for($i=0;$i<count($list);$i++){
                if($list[$i]['number']==$pkdata['next']['periodNumber']){
                    $res[$i] = M('order')->where("id = {$list[$i]['id']}")->setField('state',0);
                    if($res[$i]){
                        M('user')->where("id = {$list[$i]['userid']}")->setInc('points',$list[$i]['del_points']);
                    }
                }else{
                    $data['error']==0;
                    $data['msg']=='本期已封盘';
                }
            }
            $data['error']==1;
        }else{
            $data['error']==0;
            $data['msg']=='本期已封盘';
        }
        $this->ajaxReturn($data);
    }
    //取消
    public function del(){
        $state = F('state');
        $pkdata = F('getjnd28data');
        if($state==1){
            $id = I('id');
            $info = M('order')->where("id = $id")->find();
            if($info['number']==$pkdata['next']['periodNumber']){
                $res = M('order')->where("id = $id")->setField('state',0);
                if($res){
                    $data['error']==1;
                    //加分
                    M('user')->where("id = {$info['userid']}")->setInc('points',$info['del_points']);
                }else{
                    $data['error']==0;
                    $data['msg']=='删除失败';
                }
            }else{
                $data['error']==0;
                $data['msg']=='本期已封盘';
            }
        }else{
            $data['error']==0;
            $data['msg']=='本期已封盘';
        }
        $this->ajaxReturn($data);
    }


    public function test(){

        $dd = $_POST;
        $cc = explode(',', $dd);
        $data  = $dd['test'];
        if (preg_match('/^(大双|大单|小双|小单){1}+\/{1}+\d+$/', $data)){
            echo "单双测试成功<br>";
        }else{
            echo "大小error<br>";
        }
        //和值   1/55
        if (preg_match('/^\d{1,2}+\/{1}+\d+$/', $data)){
            echo "测试合值成功<br>";
        }else{
            echo "和error<br>";
        }
        if (preg_match('/^(极大|极小){1}+\/{1}+\d+$/', $data)){
            echo "极值测试成功<br>";
        }else{
            echo "极值error<br>";
        }
        //豹子
        if (preg_match('/^(?:([0-9])\1{2})+\/{1}+\d+$/', $data)){
            echo "测试豹子成功";
        }else{
            echo "豹子错误error<br>";
        }
        if (preg_match('/^(顺子){1}+\/{1}+\d+$/', $data)){
            echo "测试顺子成功";
        }else{
            echo "顺子错误error<br>";
        }
        $n1 = 4;
        $n2 = 2;
        $n3 = 3;
        $ss = 996;
        if(preg_match('/^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/',$ss)){
            echo "是顺子";
        }else{
            echo "不是顺子";
        }
        echo "<h4>测试数字</h4>";
        $string = 01;
        echo preg_replace('/^0*/', '', $string);
        $this->display();



    }
    public function show(){
        $dd = $_POST;
        $data  = $dd['test'];
        if (preg_match('/^(大双|大单|小双|小单){1}+\/{1}+\d+$/', $data)){
            echo "单双测试成功<br>";
        }else{
            echo "大小error<br>";
        }
        if (preg_match('/^(极大|极小){1}+\/{1}+\d+$/', $data)){
            echo "极值测试成功<br>";
        }else{
            echo "极值error<br>";
        }
        //和值   1/55
        if (preg_match('/^\d{0,27}+\/{1}+\d+$/', $data)){
            echo "测试和值成功<br>";
        }else{
            echo "和error<br>";
        }
        //豹子
        if (preg_match('/^(?:([0-9])\1{2})+\/{1}+\d+$/', $data)){
            echo "测试豹子成功";
        }else{
            echo "豹子错误error<br>";
        }
        if (preg_match('/^(顺子){1}+\/{1}+\d+$/', $data)){
            echo "测试顺子成功<br>";
        }else{
            echo "顺子错误error<br>";
        }

        dump($dd);

    }
}