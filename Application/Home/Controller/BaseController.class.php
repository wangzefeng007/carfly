<?php

namespace Home\Controller;
use Think\Controller;
header('content-type:text/html;charset=utf-8');
class BaseController extends Controller{
	
	public function _initialize(){
		//检测登录状态
		$userid = session('user');
//		测试专用  $userid['id']=72;
        $userid['id']=72;
		if(CONTROLLER_NAME!='Index'){
			if(empty($userid['id'])){
				$this->redirect('Index/index');
			}
		}
        $room = trim($_GET['T']);
		$userinfo = M('user')->where("id = {$userid['id']}")->find();
        $datasssssssss = $_SERVER["SERVER_NAME"] ;
        $this->assign('severname',$datasssssssss);
		$this->assign('userinfo',$userinfo);
        $this->assign('room',$room);
	}
	
}


?>