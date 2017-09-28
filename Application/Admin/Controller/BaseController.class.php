<?php

namespace Admin\Controller;
use Think\Controller;

class BaseController extends Controller{
	
	public function _initialize(){
		if(CONTROLLER_NAME!='Login'){
			if(empty($_SESSION['admin'])){
				$this->redirect('Login/index');
			}
		}
		
	}
	
	
}


?>