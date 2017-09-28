<?php

namespace Admin\Controller;
use Think\Controller;

class MemberController extends BaseController{
	
	public function index(){
		$nickname = I('nickname');
		$userid = I('userid');
		$member = M('user');
		if(!empty($nickname)){
			$count = $member->where("nickname like '%{$nickname}%'")->count();
			$page = new \Think\Page($count,20);
			$show = $page->show();
			$list = $member->where("nickname like '%{$nickname}%'")->limit($page->firstRow.','.$page->listRows)->order("id ASC")->select();
		}elseif(!empty($userid)){
			$count = $member->where(array('id'=>$userid))->count();
			$page = new \Think\Page($count,20);
			$show = $page->show();
			$list = $member->where(array('id'=>$userid))->limit($page->firstRow.','.$page->listRows)->order("id ASC")->select();
		} else{
			$count = $member->count();
			$page = new \Think\Page($count,20);
			$show = $page->show();
			$list = $member->limit($page->firstRow.','.$page->listRows)->order("id ASC")->select();
		}
		
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	
	public function disable(){
		$id = I('id');
		$res = M('user')->where("id = $id")->setField('status',0);
		if($res){
			$this->success('禁用成功！');
		}else{
			$this->error('禁用失败！');
		}
	}


	public function delete(){
		$id = I('id');
		if(empty($id)){
			$this->error('删除失败！');
		}
		$res = M('user')->where(array("id"=>$id))->delete();//用户表
		$res1 = M('order')->where(array("userid" => $id))->delete();//下注order记录表
		$res2 = M('integral')->where(array("userid" => $id))->delete();//上下分记录表
		$res3 = M('wx')->where(array("userid" => $id))->delete();//上下分记录表
		if($res!==false){
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}
	
	
	public function endisable(){
		$id = I('id');
		$res = M('user')->where("id = $id")->setField('status',1);
		if($res){
			$this->success('启用成功！');
		}else{
			$this->error('启用失败！');
		}
	}
	
}

?>