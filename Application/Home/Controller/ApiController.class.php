<?php

namespace Home\Controller;
use Think\Controller;

header('content-type:text/html;charset=utf-8');
class ApiController extends Controller{
	
	public function getPk10(){
		echo json_encode(getPk10());
		die();
	}
	
	public function getXyft(){
		$url="http://pk.w3s.wang/Home/Index/getXyft?t=".time();
		$data = curlGet($url);
		echo $data;
	}
	public function test(){
		$beginToday=$endToday=strtotime("01:00:00")+86400;
		$endToday=strtotime('23:59:00');
		print_r(date('Y-m-d H:i:s',$beginToday));die();
	}
    public function getBj28(){
        $begin = strtotime('00:00:00');
        $end= strtotime("09:00:00");
        if($begin<time() && time()<$end){
            echo json_encode(getJnd28());
        }else{
            echo json_encode(getBj28());
        }
        die();
    }
    public function getJnd28(){
        echo json_encode(getJnd28());
        die();
    }
    public function getkuai3(){
        echo json_encode(getkuai3());
        die();
    }
    public function getssc(){
        echo json_encode(getssc());
        die();
    }

}
?>