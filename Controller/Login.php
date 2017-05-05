<?php
/**
 * Bang Hun.
 * 16.07.10
 */

use \BH_Application as App;
use \BH_Common as CF;

class LoginController{
	/**
	 * @var MemberModel
	 */
	public $model = null;

	public function __construct(){
	}

	public function __init(){
		require _DIR.'/Model/Member.model.php';
		$this->model = new \MemberModel();
	}

	public function Index(){
		App::$Instance->_View($this, $this->model);
	}
	public function PostLogin(){
		$email = trim($_POST['email1'].'@'.$_POST['email2']);
		if(strlen($email) < 1){
			Redirect('-1', '아이디를 입력하여 주세요.');
		}
		if(strlen($_POST['pwd']) < 1){
			Redirect('-1', '패스워드를 입력하여 주세요.');
		}
		$res = $this->LoginEmailCheck($email, $_POST['pwd']);
		if($res === false){
			Redirect('-1', '일치하는 회원이 없습니다.');
		}else{
			$_SESSION['member'] = array();
			$_SESSION['member']['muid'] = $res['muid'];
			$_SESSION['member']['level'] = $res['level'];
			Redirect(_URL.'/');
		}
	}

	public function Register(){
		App::$Instance->_View($this);
	}

	public function PostRegister(){
		App::$Instance->Html = 'RegisterForm.html';
		$this->model->data['nickname']->Required = true;
		App::$_Value['email1'] = '';
		App::$_Value['email2'] = '';
		App::$Instance->_View($this, $this->model);
	}

	public function PostRegisterProcess(){
		$RegResult = true;
		$res = $this->Check('email', $_POST['email1'].'@'.$_POST['email2'], false);
		$res2 = $this->Check('nickname', $_POST['nickname']);
		if(!$res){
			App::$_Value['alertMsg'] = '이미 사용중인 이메일입니다.';
			$RegResult = false;
		}
		else if(!$res2){
			App::$_Value['alertMsg'] = '이미 사용중인 닉네임입니다.';
			$RegResult = false;
		}
		else if($_POST['pwd'] != $_POST['chkpwd']){
			App::$_Value['alertMsg'] = '패스워드가 일치하지 않습니다.';
			$RegResult = false;
		}else{
			$this->model->AddExcept('approve');
			$this->model->SetPostValues();
			$this->model->SetValue('email', $_POST['email1'].'@'.$_POST['email2']);
			$this->model->SetValue('mid', $_POST['email1'].'@'.$_POST['email2']);
			$this->model->SetValue('level', 1);
			$this->model->SetValue('reg_date', date('Y-m-d H:i:s'));
			$ErrorMessage = $this->model->GetErrorMessage();
			if(sizeof($ErrorMessage)){
				$RegResult = false;
				App::$_Value['alertMsg'] = $ErrorMessage[0];
			}else{
				$res = $this->model->DBInsert();
				if(!$res->result){
					$RegResult = false;
					App::$_Value['alertMsg'] = 'ERROR';
				}
			}
		}

		if(!$RegResult){
			App::$_Value['email1'] = $_POST['email1'];
			App::$_Value['email2'] = $_POST['email2'];
			App::$Instance->Html = 'RegisterForm.html';
			App::$Instance->_View($this, $this->model);
		}else{
			Redirect(_URL.'/', '등록되었습니다.');
		}
	}

	public function EmailCheck(){
		$res = $this->Check('email', $_GET['email1'].'@'.$_GET['email2'], false);
		echo json_encode(array('result' => true, 'data' => $res ));
	}

	public function NicknameCheck(){
		$res = $this->Check('nickname', $_GET['nickname']);
		echo json_encode(array('result' => true, 'data' => $res ));
	}

	private function Check($key, $val, $wcheck = true){
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->SetKey('COUNT(muid) as cnt');
		$dbGet->AddWhere($key.'='.SetDBText($val));
		$res = $dbGet->Get();

		if($wcheck){ // 탈퇴회원 체크여부
			$dbGet = new \BH_DB_Get(TABLE_WITHDRAW_MEMBER);
			$dbGet->SetKey('COUNT(muid) as cnt');
			$dbGet->AddWhere($key.'='.SetDBText($val));
			$res2 = $dbGet->Get();
		}else $res2['cnt'] = 0;
		return (!$res['cnt'] && !$res2['cnt']);
	}


	public function Logout(){
		unset($_SESSION['member']);
		session_destroy();
		Redirect(_URL.'/');
	}

	private function LoginMidCheck($mid, $pwd){
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->SetKey(array('muid', 'level', 'pwd'));
		$dbGet->AddWhere('mid='.SetDBText($mid));
		//$params->test = true;
		$res = $dbGet->Get();
		if($res === false) return false;
		return (_password_verify($pwd, $res['pwd'])) ? array('muid' => $res['muid'], 'level' => $res['level']) : false;
	}

	private function LoginEmailCheck($email, $pwd){
		$dbGet = new \BH_DB_Get();
		$dbGet->table = $this->model->table;
		$dbGet->SetKey(array('muid', 'level', 'pwd'));
		$dbGet->AddWhere('email='.SetDBText($email));
		$res = $dbGet->Get();
		if($res === false) return false;
		return (_password_verify($pwd, $res['pwd'])) ? array('muid' => $res['muid'], 'level' => $res['level']) : false;
	}
}