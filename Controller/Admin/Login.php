<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Admin;

class LoginController extends \BH_Controller{
	public $model;

	public function __Init(){
		require _DIR.'/Model/Member.model.php';
		$this->model = new \MemberModel();
		$this->model->data['mid']->Required = true;
		$this->Layout = '_Empty';
	}

	public function Index(){
		if(_MEMBERIS === true && ($_SESSION['member']['level'] == _SADMIN_LEVEL || $_SESSION['member']['level'] == _ADMIN_LEVEL)) Redirect(_ADMINURL);
		$this->_View($this->model);
	}

	public function PostLogin(){
		$mid = trim($_POST['mid']);
		if(strlen($mid) < 1){
			Redirect('-1', '아이디를 입력하여 주세요.');
		}
		if(strlen($_POST['pwd']) < 1){
			Redirect('-1', '패스워드를 입력하여 주세요.');
		}
		$res = $this->LoginMidCheck($mid, $_POST['pwd'], array(_SADMIN_LEVEL, _ADMIN_LEVEL));
		if($res === false){
			Redirect('-1', '일치하는 회원이 없습니다.');
		}else{
			$_SESSION['member'] = array();
			$_SESSION['member']['muid'] = $res['muid'];
			$_SESSION['member']['level'] = $res['level'];
			Redirect(_ADMINURL);
		}
	}

	public function Logout(){
		unset($_SESSION['member']);
		session_destroy();
		Redirect(_ADMINURL.'/Login');
	}


	private function LoginMidCheck($mid, $pwd, $level = 1){
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->SetKey(array('muid', 'level', 'pwd', 'PASSWORD('.SetDBText($pwd).') as pwd2'));
		$dbGet->AddWhere('mid='.SetDBText($mid));
		if(is_array($level)) $dbGet->AddWhere('level IN ('.implode(',', $level).')');
		else $dbGet->AddWhere('level='.$level);
		//$dbGet->test = true;
		$res = $dbGet->Get();
		if($res === false) return false;
		return ($res['pwd'] == $res['pwd2']) ? array('muid' => $res['muid'], 'level' => $res['level']) : false;
	}

	private function LoginEmailCheck($email, $pwd, $level = 1){
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->SetKey(array('muid', 'level', 'pwd', 'PASSWORD('.SetDBText($pwd).') as pwd2'));
		$dbGet->AddWhere('email='.SetDBText($email));
		$dbGet->AddWhere('level='.$level);
		$res = $dbGet->Get();
		if($res === false) return false;
		return ($res['pwd'] == $res['pwd2']) ? array('muid' => $res['muid'], 'level' => $res['level']) : false;
	}
}