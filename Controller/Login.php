<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use Custom\Email;
use \DB as DB;

class Login{
	/**
	 * @var MemberModel
	 */
	public $model = null;
	public $useMailId = false;
	public $mailIdAddrSelection = false;

	public function __construct(){
	}

	public function __init(){
		$this->model = App::InitModel('Member');
		$this->useMailId = (CM::Config('Default', 'useMailId') == 'y');
		$this->mailIdAddrSelection = (CM::Config('Default', 'mailIdAddrSelection') == 'y');
		if(_MEMBERIS === true && !in_array(App::$Action, array('Logout'))) URLRedirect(_URL . '/');
	}

	public function Index(){
		App::View($this->model);
	}
	public function PostLogin(){
		if($this->useMailId){
			$key = trim(!is_null(Post('email')) ? Post('email') : Post('email1').'@'.Post('email2'));
		}
		else $key = trim(Post('mid'));

		if(strlen($key) < 1){
			URLReplace('-1', ($this->useMailId ? '이메일을' : '아이디를') . ' 입력하여 주세요.');
		}
		if(strlen(Post('pwd')) < 1){
			URLReplace('-1', '패스워드를 입력하여 주세요.');
		}
		$res = $this->useMailId ? $this->LoginEmailCheck($key, Post('pwd')) : $this->LoginMidCheck($key, Post('pwd'));

		if($res === false){
			URLReplace('-1', '일치하는 회원이 없습니다.');
		}else{
			DB::UpdateQryObj(TABLE_MEMBER)
				->AddWhere('muid = %d', $res['muid'])
				->SetDataStr('login_date', date('Y-m-d H:i:s'))
				->Run();
			$_SESSION['member'] = array();
			$_SESSION['member']['muid'] = $res['muid'];
			$_SESSION['member']['level'] = $res['level'];

			// 로그인 카운터
			$vcnt = \Common\VisitCounter::GetInstance();
			$vcnt->InsertLoginCounter();

			URLReplace(_URL.'/');
		}
	}

	public function Register(){
		App::View();
	}

	public function PostRegister(){
		App::$Html = 'RegisterForm.html';
		$this->model->data['nickname']->Required = true;
		App::$Data['email1'] = '';
		App::$Data['email2'] = '';
		App::View($this->model);
	}

	public function PostRegisterProcess(){
		$RegResult = true;
		$email = !is_null(Post('email')) ? Post('email') : Post('email1').'@'.Post('email2');
		if($this->useMailId)
			$res = $this->Check('email', $email, false);
		else $res = $this->Check('mid', Post('mid'), false);

		$res2 = $this->Check('nickname', Post('nickname'));

		if(!$res){
			App::$Data['alertMsg'] = '이미 사용중인 이메일입니다.';
			$RegResult = false;
		}
		else if(!$res2){
			App::$Data['alertMsg'] = '이미 사용중인 닉네임입니다.';
			$RegResult = false;
		}
		else if(Post('pwd') != Post('chkpwd')){
			App::$Data['alertMsg'] = '패스워드가 일치하지 않습니다.';
			$RegResult = false;
		}else{
			$this->model->SetPostValues();
			if($this->useMailId){
				$this->model->SetValue('email', $email);
				$this->model->SetValue('mid', $email);
			}else{
				$this->model->SetValue('mid', Post('mid'));
			}
			if(CM::Config('Default', 'joinApprove') == 'n') $this->model->SetValue('approve', 'n');
			else $this->model->SetValue('approve', 'y');

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
			App::$Data['email1'] = Post('email1');
			App::$Data['email2'] = Post('email2');
			App::$Html = 'RegisterForm.html';
			App::View($this->model);
		}else{
			URLReplace(_URL.'/', '등록되었습니다.');
		}
	}

	public function EmailCheck(){
		$res = $this->Check('email', Get('email') ? Get('email') : (Get('email1').'@'.Get('email2')), false);
		echo json_encode(array('result' => true, 'data' => $res ));
	}

	public function MidCheck(){
		$res = $this->Check('mid', Get('mid'), false);
		echo json_encode(array('result' => true, 'data' => $res ));
	}

	public function NicknameCheck(){
		$res = $this->Check('nickname', Get('nickname'));
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



	/* --------------------------------------------
	 *
	 *       아이디/비밀번호 찾기
	 *
	 -------------------------------------------- */

	public function FindID(){
		App::View($this->model);
	}

	public function FindPW(){
		App::View('FindID', $this->model);
	}

	public function PostFindID(){
		if(!strlen(Post('mname'))) JSON(false, _WRONG_CONNECTED);
		if(!strlen(Post('email'))) JSON(false, _WRONG_CONNECTED);
		if(App::$ID == 'PW' && !strlen(Post('mid'))) JSON(false, _WRONG_CONNECTED);

		$qry = DB::GetQryObj($this->model->table)
			->AddWhere('mname = %s', Post('mname'))
			->AddWhere('email = %s', Post('email'))
			->SetKey('muid', 'mname', 'mid');

		// 패스워드 찾기
		if(App::$ID == 'PW'){
			$qry->AddWhere('mid = %s', Post('mid'));
			$res = $qry->Get();
			if($res){
				$mail = Email::GetInstance();
				$mail->AddMail(Post('email'), Post('mname'));

				$t = explode(' ', microtime());
				$t2 = trim(str_replace('.','',$t[0]));
				$code = date('Y-m-d H:i:s').toBase(mt_rand(10000, 99999).substr($t2, 0, 4), 36);

				\DB::SQL()->CCQuery($this->model->table, 'UPDATE %t SET pw_reset_code = %s WHERE muid = %d', aes_encrypt($code, PW_RESET_KEY), $res['muid']);
				$mail->SendMailByFindPW(Post('mname'), Post('mid'), substr($code, 19));
				JSON(true, '해당메일('.GetDBText(Post('email')).')로 비밀번호 변경 코드를 발송하였습니다.');
			}else JSON(false, '해당 정보와 일치하는 회원이 없습니다.');
		}

		// 아이디 찾기
		else{
			$res = $qry->Get();
			if($res){
				$mail = Email::GetInstance();
				$mail->AddMail(Post('email'), Post('mname'));
				$mail->SendMailByFindID(Post('mname'), $res['mid']);
				JSON(true, '해당메일('.GetDBText(Post('email')).')로 아이디를 발송하였습니다.');
			}else JSON(false, '해당 정보와 일치하는 회원이 없습니다.');
		}
	}

	public function ResetPW(){
		App::View($this->model);
	}

	public function PostResetPW(){
		$data = DB::GetQryObj($this->model->table)
			->AddWhere('`mid` = %s', Post('mid'))
			->Get();
		if(!$data){
			URLRedirect(-1, '해당 아이디가 존재하지 않습니다.');
		}
		/**
		 * @var \MemberModel
		 */
		$this->model;
		$this->model->AddExcept('tel', 'phone');
		$this->model->SetDBValues($data);

		$this->model->SetValue('pwd', Post('pwd'));
		$this->model->SetValue('pw_reset_code', '');

		$err = $this->model->GetErrorMessage();
		if(sizeof($err)){
			URLRedirect(-1, $err[0]);
			return;
		}
		if(Post('pwd') !== Post('chkpwd')){
			URLRedirect(-1, '비밀번호가 일치하지 않습니다.');
			return;
		}


		if($data){
			if($data['pw_reset_code']){
				$temp = aes_decrypt($data['pw_reset_code'], PW_RESET_KEY);
				$date = substr($temp, 0, 19);
				$code = substr($temp, 19);
				$min = date('Y-m-d H:i:s', strtotime('-1 hour'));
				if($date < $min){
					URLRedirect(-1, '기간이 만료된 코드입니다.');
					return;
				}
				if($code !== Post('pw_reset_code')){
					URLRedirect(-1, '비밀번호 변경 코드가 불일치합니다.');
					return;
				}

				$res = $this->model->DBUpdate();
				if($res->result){
					URLRedirect(_URL.'/', '비밀번호가 변경되었습니다.');
				}
				else{
					URLRedirect('-1', '비밀번호 변경 오류');
				}
			}
			else URLRedirect(-1, '해당 계정은 비밀번호 변경 요청이 없습니다.');
		}

	}
}