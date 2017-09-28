<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/20
 * Time: 19:21
 */

namespace Home\Controller;


use Think\Controller;

class Jnd28Controller extends BaseController
{
    public function index(){

    }

    public function rule(){
        $this->display();
    }
    //活动
    public function activity(){
        $this->display();
    }



    //走势、
    public function trend(){
        $list = M('dannumber')->where("game = 'Jnd28'")->order("id DESC")->limit(10)->select();
        $this->assign('list',$list);
        $this->display();
    }
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