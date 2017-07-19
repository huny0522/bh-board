<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class Login{
	/**
	 * @var MemberModel
	 */
	public $model = null;

	public function __construct(){
	}

	public function __init(){
		$this->model = App::InitModel('Member');
	}

	public function Index(){
		App::View($this, $this->model);
	}
	public function PostLogin(){
		$email = trim($_POST['email1'].'@'.$_POST['email2']);
		if(strlen($email) < 1){
			URLReplace('-1', '아이디를 입력하여 주세요.');
		}
		if(strlen($_POST['pwd']) < 1){
			URLReplace('-1', '패스워드를 입력하여 주세요.');
		}
		$res = $this->LoginEmailCheck($email, $_POST['pwd']);
		if($res === false){
			URLReplace('-1', '일치하는 회원이 없습니다.');
		}else{
			$_SESSION['member'] = array();
			$_SESSION['member']['muid'] = $res['muid'];
			$_SESSION['member']['level'] = $res['level'];
			URLReplace(_URL.'/');
		}
	}

	public function Register(){
		App::View($this);
	}

	public function PostRegister(){
		App::$Html = 'RegisterForm.html';
		$this->model->data['nickname']->Required = true;
		App::$Data['email1'] = '';
		App::$Data['email2'] = '';
		App::View($this, $this->model);
	}

	public function PostRegisterProcess(){
		$RegResult = true;
		$res = $this->Check('email', $_POST['email1'].'@'.$_POST['email2'], false);
		$res2 = $this->Check('nickname', $_POST['nickname']);
		if(!$res){
			App::$Data['alertMsg'] = '이미 사용중인 이메일입니다.';
			$RegResult = false;
		}
		else if(!$res2){
			App::$Data['alertMsg'] = '이미 사용중인 닉네임입니다.';
			$RegResult = false;
		}
		else if($_POST['pwd'] != $_POST['chkpwd']){
			App::$Data['alertMsg'] = '패스워드가 일치하지 않습니다.';
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
				App::$Data['alertMsg'] = $ErrorMessage[0];
			}else{
				$res = $this->model->DBInsert();
				if(!$res->result){
					$RegResult = false;
					App::$Data['alertMsg'] = 'ERROR';
				}
			}
		}

		if(!$RegResult){
			App::$Data['email1'] = $_POST['email1'];
			App::$Data['email2'] = $_POST['email2'];
			App::$Html = 'RegisterForm.html';
			App::View($this, $this->model);
		}else{
			URLReplace(_URL.'/', '등록되었습니다.');
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
		$dbGet->AddWhere($key.' = %s', $val);
		$res = $dbGet->Get();

		if($wcheck){ // 탈퇴회원 체크여부
			$dbGet = new \BH_DB_Get(TABLE_WITHDRAW_MEMBER);
			$dbGet->SetKey('COUNT(muid) as cnt');
			$dbGet->AddWhere($key.' = %s', $val);
			$res2 = $dbGet->Get();
		}else $res2['cnt'] = 0;
		return (!$res['cnt'] && !$res2['cnt']);
	}


	public function Logout(){
		unset($_SESSION['member']);
		session_destroy();
		URLReplace(_URL.'/');
	}

	private function LoginMidCheck($mid, $pwd){
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->SetKey(array('muid', 'level', 'pwd'));
		$dbGet->AddWhere('mid = %s', $mid);
		//$params->test = true;
		$res = $dbGet->Get();
		if($res === false) return false;
		return (_password_verify($pwd, $res['pwd'])) ? array('muid' => $res['muid'], 'level' => $res['level']) : false;
	}

	private function LoginEmailCheck($email, $pwd){
		$dbGet = new \BH_DB_Get();
		$dbGet->table = $this->model->table;
		$dbGet->SetKey(array('muid', 'level', 'pwd'));
		$dbGet->AddWhere('email = %s', $email);
		$res = $dbGet->Get();
		if($res === false) return false;
		return (_password_verify($pwd, $res['pwd'])) ? array('muid' => $res['muid'], 'level' => $res['level']) : false;
	}
}