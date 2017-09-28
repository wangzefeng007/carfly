<?php
namespace Home\Controller;
use Think\Controller;
class AjaxController extends Controller
{
    public function index()
    {
        $data = session('user');
        $id = $data['id'];
        $integral = M('integral');
        $integralInfo = $integral->where("userid = ".$id)->order("time DESC")->find();
        $type= I('type');
        #$integralInfo['balance'] = 100;
        if($type==2 && $integralInfo['balance']<10000){
            $data2['ResultCode']=201;
            $data2['Message']='您的余额不足10000';
        }elseif($type==3 && $integralInfo['balance']<50000){
            $data2['ResultCode']=202;
            $data2['Message']='您的余额不足50000';
        }else{
            $data2['ResultCode']=200;
            $data2['Message']='返回成功';
        }
        $this->ajaxReturn($data2);
    }

}