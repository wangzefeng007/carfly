<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/19
 * Time: 17:09
 */

namespace Home\Controller;


use Think\Controller;


//北京28
class Bj28Controller extends BaseController
{
    public function index(){
        $this->display();
    }

//规则
    public function rule()
    {
        $this->display();
    }
    //走势、
    public function trend(){
        $list = M('dannumber')->where("game = 'Bj28'")->order("id DESC")->limit(10)->select();
        $this->assign('list',$list);
        $this->display();
    }
    //竞猜列表
    public  function jingcaitable(){
        $user=M('order');
//        获取当前期数
        $qishi=$user->order("number DESC")->limit(1)->getField("number");
        $tonggao=$user->where("number=$qishi")->field('nickname,jincai')->select();
        $sum ='';
        for ($i=0;$i<count($tonggao);$i++){
         $sum.= '期号:' .$qishi . '<br/>' . $tonggao[$i]["nickname"].$tonggao[$i]["jincai"];
        }
        $this->display();
    }
    //活动
    public function activity(){
        $this->display();
    }
//    投注
    function touzhu(){
        $this->display();
    }
//    上下分
    function shangxiafen(){
        $this->display();
    }
//    游戏说明1
    function youxi1(){
        $this->display();
    }
    //    游戏说明2
    function youxi2(){
        $this->display();
    }
    //    游戏说明3
    function youxi3(){
        $this->display();
    }
 }