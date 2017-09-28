<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/10
 * Time: 11:50
 */

namespace Home\Controller;
use Think\Controller;

class SelectController extends BaseController
{
   public function index(){
       //显示开奖数据数据
       $jndlist = M('dannumber')->where("game = 'Jnd28'")->order("id DESC")->limit(10)->select();
       $list = M('dannumber')->where("game = 'Bj28'")->order("id DESC")->limit(10)->select();
       $pklist = M('number')->order("id DESC")->limit(10)->select();
       $kefu = M('config')->where("id = 1")->find();

       //显示个人开奖记录数据
        $data = session('user');
        $id = $data['id'];
        $pk10data = M('order')->where(array("userid"=>$id,"game"=>'pk10'))->order("id DESC")->limit(10)->select();
        $bj28data = M('order')->where(array("userid"=>$id,"game"=>'Bj28'))->order("id DESC")->limit(10)->select();
        $jnd28data = M('order')->where(array("userid"=>$id,"game"=>'Jnd28'))->order("id DESC")->limit(10)->select();
        $this->assign('danlist',$list);
        $this->assign('jndlist',$jndlist);
        $this->assign('pklist',$pklist);
        $this->assign('bj28data',$bj28data);
        $this->assign('pk10data',$pk10data);
       $this->assign('jnd28data',$jnd28data);
        $this->assign('kefu',$kefu);
        $this->display();
   }
    public function choice(){
        $this->display();
    }
   public function integral(){
    $data = session('user');
    #上下分记录
    $integral = M("integral")->where(array("userid"=>$data['id']))->order("id DESC")->select();
    $this->assign('integral',$integral);
    $this->display();
}
   public function beijinga(){
       $room = M('beijingroom')->select();
       $this->assign('room',$room);
        $this->display();
   }
   public function room(){
//       echo $_GET['rid'];
       $room = M('room')->where("rid='roomid'")->select();
       dump($room);
       $this->display();
   }

//   计算在线人数跟玩家金额
   public function money(){

   }
}