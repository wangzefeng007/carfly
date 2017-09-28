<?php

namespace Admin\Controller;
use Think\Controller;
class SiteController extends BaseController {
	
	public function pk10(){
		if(IS_POST){
			$this -> sitesave('site.php');
		}else{
			$this->display();
		}
	}
	public function Bj28(){
        if(IS_POST){
            $this -> sitesave('site.php');
        }else{
            $this->display();
        }
    }
    public function Jnd28(){
        if(IS_POST){
            $this -> sitesave('site.php');
        }else{
            $this->display();
        }
    }
    public function postfanshui(){
        if(IS_POST){
            $this -> sitesavefs('site.php');
        }else{
            $this->display();
        }
    }
	
	public function index() {
		if (IS_POST) {
			$this -> sitesave('site.php');
		} else {
			$this -> display();
		}
	}

	public function setting() {
		if (IS_POST) {
			$this -> sitesave('route.php');
		} else {
			$this -> display();
		}
	}

	private function sitesave($filename) {

		if ($this -> update_config($_POST, $filename)) {

			$this -> success('修改成功，跳转中~', U('site/' . ACTION_NAME));

		} else {

			$this -> error('操作失败', U('site/' . ACTION_NAME));
		}
	}
    private function sitesavefs($filename) {

        if ($this -> update_config($_POST, $filename)) {

            $this -> success('修改成功，跳转中~', U('site/' . 'fanshuishezhi'));

        } else {

            $this -> error('操作失败', U('site/' . 'fanshuishezhi'));
        }
    }

	private function update_config($new_config, $filename) {
		$config_file = CONF_PATH . $filename;
		if (is_file($config_file) && is_writable($config_file)) {
			$config =
			require $config_file;

			$config = array_merge($config, $new_config);

			file_put_contents($config_file, "<?php \nreturn " . stripslashes(var_export($config, true)) . ";", LOCK_EX);

			@unlink(RUNTIME_FILE);

			return true;

		} else {

			return false;

		}

	}
	public function fanshuishezhi(){
        $this->display();
    }

	public function fanshui(){
        if (IS_POST) {


        //利用数据库中的时间戳判断是否存储了值。
        $ago = strtotime('-1 day 00:00:00');
        $order_day_data_arr=M('order_day')->where("order_time = $ago")->count();
        $order_day_data = $order_day_data_arr[0]['count'];
        if($order_day_data >0){
//            echo '已经结算'.date('Y年m月d日',$ago).'的返水';
            $this -> success(date('Y年m月d日',$ago).'已经返水，请勿重复操作', U('site/' . ACTION_NAME));
        }
        else{

            //数据库中没有数据， 根据条件插入数据
            //今天00点的时间
            $time = strtotime(date("Y-m-d"));
//            $time = time();
            $olddate = strtotime('-1 day 00:00:00');
            $map['time'] = array('between', "$olddate,$time");
            $res = M('order')->where($map)->field('sum(add_points),userid,sum(type = 2)as zuhetype,sum(type = 2)/count(userid) as zuhebili,sum(del_points),count(userid) as count,sum(del_points)-sum(add_points) as del_data')->group('userid')->select();
            $mycount = count($res);
            $renshu = 0;
            for ($i = 0; $i < $mycount; $i++) {
                //判断是否为大于是十把，&&判断组合比例要大于20%，//小单，大双组合超过75%没有返水。
                if ($res[$i]['count'] >= C('fs_jushu') && $res[$i]['zuhebili'] > C('fs_zuixiaobili') && $res[$i]['del_data'] >= C('fs_jine_1')) {
                    $data['userid'] = $res[$i]['userid'];
                    $data['shuying'] = $res[$i]['del_data'];
                    $data['time'] = time();
                    $data['order_time'] = strtotime('-1 day 00:00:00');
                    //把user信息传递给order day 表中
                    $headurldata = M('user')->where(array("id" => $res[$i]['userid']))->select();
                    $data['headimgurl'] = $headurldata[0]['headimgurl'];
                    $data['nickname'] = $headurldata[0]['nickname'];
                    if ($res[$i]['del_data'] >= C('fs_jine_1') && $res[$i]['del_data'] <= C('fs_jine_2')) {
                        $fanshuidata = $res[$i]['del_data'] * C('fs_bl_1');
                        $data['fanshui'] = $fanshuidata;
                        $adduserdata = M('user')->where(array("id" => $res[$i]['userid']))->setInc('points', $fanshuidata);
                        if ($adduserdata) {
                           M('order_day')->add($data);
                            $renshu = $renshu +1;
                        }
                    }
                    if ($res[$i]['del_data'] >= C('fs_jine_2') && $res[$i]['del_data'] <= C('fs_jine_3')) {
                        $fanshuidata = $res[$i]['del_data'] * C('fs_bl_2');

                        $data['fanshui'] = $fanshuidata;
                        $adduserdata = M('user')->where(array("id" => $res[$i]['userid']))->setInc('points', $fanshuidata);
                        if ($adduserdata) {
                             M('order_day')->add($data);
                            $renshu = $renshu +1;
                        }
                    }
                    if ($res[$i]['del_data'] >= C('fs_jine_3')) {
                        $fanshuidata = $res[$i]['del_data'] * C('fs_bl_3');
                        $data['fanshui'] = $fanshuidata;
                        $adduserdata = M('user')->where(array("id" => $res[$i]['userid']))->setInc('points', $fanshuidata);
                        if ($adduserdata) {
                             M('order_day')->add($data);
                            $renshu = $renshu +1;
                        }
                    }
                }

            }
//            for循环结束
            if($renshu >0){
                $this -> success(date('Y年m月d日',$ago).'有'.$renshu.'位成功返水', U('site/' . ACTION_NAME));
            }else{
                $this -> success(date('Y年m月d日',$ago).'没有达到可以返水的用户', U('site/' . ACTION_NAME));
            }

        }
        }else{
            $list = M('order_day')->order('id DESC')->select();

            $this->assign('list',$list);
            $this->display();
        }
    }
    public function pk10sd(){
        if (IS_POST) {
            $data  =$_POST;
            dump($data);
        }

            $this->display();
    }
    public function bj28sd(){
        if (IS_POST) {
            $data  =$_POST;
            if (empty($data["awardnumbers"]) ||empty($data["periodnumber"])){
                $this -> error('开奖期号或者号码不能为空');
            }
            if (strlen($data["awardnumbers"]) !==5){
                $this -> error('开奖号码格式错误，例如：3,5,6');
            }
            if (!is_numeric($data["periodnumber"])){
                $this -> error('开奖期数格式错误');
            }
            if (M('dannumber')->where(array('periodnumber'=>$data["periodnumber"],'game'=>'Bj28'))->find()){
                $this -> error('不能手动开奖已经开奖的期号');
            }
            $info = explode(',',$data["awardnumbers"]);
            $n1 = $info[0];
            $n2 = $info[1];
            $n3 = $info[2];
            $alln = $n1+$n2+$n3;
            //总和，赋值给总和.
            $map['zonghe'] = $alln;
            //判断单双
            if($alln %2 == 0){
                $jiou = "双";
            }else{
                $jiou = "单";
            }
            $map['danshuang'] = $jiou;
            //判断大小单双
            if($jiou == "双"){
                if(0<= $alln&&$alln<=13){
                    $daxiaodanshuang ="小双";
                }else{
                    $daxiaodanshuang = "大双";
                }
            }
            if($jiou =="单"){
                if(0<= $alln&&$alln<=13){
                    $daxiaodanshuang = "小单";
                }else{
                    $daxiaodanshuang = "大单";
                }
            }
            //储存大小单双到服务器
            $map['dxds'] = $daxiaodanshuang;
            // 判断极值
            $jizhi = "";
            if(0<=$alln && $alln<=5){
                $jizhi = "极小";
            }
            if(5<$alln && $alln<22){
                $jizhi = "非极";
            }
            if(22<=$alln && $alln<=27){
                $jizhi = "极大";
            }
            //判断是否为顺子
            $shunzi = "";
            $ss = $n1.$n2.$n3;
            if(preg_match('/^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/',$ss)){
                $shunzi = "顺子";
            }else{
                $shunzi = "非顺子";
            }
            //判断是否为豹子
            $bz = "";
            if($n1 ==$n2 && $n1 ==$n3 && $n2 ==$n3 ){
                $bz = "豹子";
            }else{
                $bz = "非豹子";
            }
            //判断为的大小
            $dx = "";
            if($alln<=13){
                $dx = "小";
            }else{
                $dx = "大";
            }
            //判断对子
            $dz = "";
            $duizinum = 0;
            if ($n1 ==$n2){
                $duizinum=$duizinum+1;
            }
            if ($n1 ==$n3){
                $duizinum=$duizinum+1;
            }
            if($n2 ==$n3){
                $duizinum =$duizinum+1;
            }
            if($duizinum == 1){
                $dz = "对子";
            }else{
                $dz = "非对子";
            }
            $map['dz'] =$dz;
            $map['dx'] = $dx;
            $map['bz'] =$bz;
            $map['sz'] = $shunzi;
            $map['jz'] =$jizhi;
            $map['number'] = "55";
            $map['game'] = 'Bj28';
            $map['time'] = time();
            $map['periodnumber'] =$data["periodnumber"];
            $map['awardtime'] = $data['kaijiangtime'];
            $map['awardnumbers'] = $data['awardnumbers'];

            if (M('dannumber')->add($map)){
                $this -> success('修改成功，跳转中~', U('site/' . ACTION_NAME));
            }else{
                $this -> success('修改失败~', U('site/' . ACTION_NAME));
            }
        }else{
            $this->display();
        }
    }
    public function jnd28sd(){
        if (IS_POST) {
            $data  =$_POST;
            if (empty($data["awardnumbers"]) ||empty($data["periodnumber"])){
                $this -> error('开奖期号或者号码不能为空');
            }
            if (strlen($data["awardnumbers"]) !==5){
                $this -> error('开奖号码格式错误，例如：3,5,6');
            }
            if (!is_numeric($data["periodnumber"])){
                $this -> error('开奖期数格式错误');
            }
            if (M('dannumber')->where(array('periodnumber'=>$data["periodnumber"],'game'=>'Jnd28'))->find()){
                $this -> error('不能手动开奖已经开奖的期号');
            }
            $info = explode(',',$data["awardnumbers"]);
            $n1 = $info[0];
            $n2 = $info[1];
            $n3 = $info[2];
            $alln = $n1+$n2+$n3;
            //总和，赋值给总和.
            $map['zonghe'] = $alln;
            //判断单双
            if($alln %2 == 0){
                $jiou = "双";
            }else{
                $jiou = "单";
            }
            $map['danshuang'] = $jiou;
            //判断大小单双
            if($jiou == "双"){
                if(0<= $alln&&$alln<=13){
                    $daxiaodanshuang ="小双";
                }else{
                    $daxiaodanshuang = "大双";
                }
            }
            if($jiou =="单"){
                if(0<= $alln&&$alln<=13){
                    $daxiaodanshuang = "小单";
                }else{
                    $daxiaodanshuang = "大单";
                }
            }
            //储存大小单双到服务器
            $map['dxds'] = $daxiaodanshuang;
            // 判断极值
            $jizhi = "";
            if(0<=$alln && $alln<=5){
                $jizhi = "极小";
            }
            if(5<$alln && $alln<22){
                $jizhi = "非极";
            }
            if(22<=$alln && $alln<=27){
                $jizhi = "极大";
            }
            //判断是否为顺子
            $shunzi = "";
            $ss = $n1.$n2.$n3;
            if(preg_match('/^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/',$ss)){
                $shunzi = "顺子";
            }else{
                $shunzi = "非顺子";
            }
            //判断是否为豹子
            $bz = "";
            if($n1 ==$n2 && $n1 ==$n3 && $n2 ==$n3 ){
                $bz = "豹子";
            }else{
                $bz = "非豹子";
            }
            //判断为的大小
            $dx = "";
            if($alln<=13){
                $dx = "小";
            }else{
                $dx = "大";
            }
            //判断对子
            $dz = "";
            $duizinum = 0;
            if ($n1 ==$n2){
                $duizinum=$duizinum+1;
            }
            if ($n1 ==$n3){
                $duizinum=$duizinum+1;
            }
            if($n2 ==$n3){
                $duizinum =$duizinum+1;
            }
            if($duizinum == 1){
                $dz = "对子";
            }else{
                $dz = "非对子";
            }
            $map['dz'] =$dz;
            $map['dx'] = $dx;
            $map['bz'] =$bz;
            $map['sz'] = $shunzi;
            $map['jz'] =$jizhi;
            $map['number'] = "55";
            $map['game'] = 'Jnd28';
            $map['time'] = time();
            $map['periodnumber'] =$data["periodnumber"];
            $map['awardtime'] = $data['kaijiangtime'];
            $map['awardnumbers'] = $data['awardnumbers'];

          if (M('dannumber')->add($map)){
              $this -> success('修改成功，跳转中~', U('site/' . ACTION_NAME));
          }else{
              $this -> success('修改失败~', U('site/' . ACTION_NAME));
          }
        }else{
            $this->display();
        }
    }
    public function ssc(){
        if(IS_POST){
            $this -> sitesave('site.php');
        }else{
            $this->display();
        }
    }
}
