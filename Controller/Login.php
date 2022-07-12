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
	 * @var \MemberModel
	 */
	public $model = null;
	public $useMailId = false;
	public $mailIdAddrSelection = false;

	/**
	 * @var \ConfigDefault
	 */
	public $defCfg;

	public function __construct(){
		$this->defCfg = App::$cfg->Def();
		$this->model = new \MemberModel();
		$this->useMailId = ($this->defCfg->useMailId->Val() == 'y');
		$this->mailIdAddrSelection = ($this->defCfg->mailIdAddrSelection->Val() == 'y');
	}

	public function __init(){
		if(\BHG::$isMember === true && !in_array(App::$action, array('Logout'))) URLRedirect(\Paths::Url() . '/');
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
			URLReplace('-1', $this->useMailId ? App::$lang['NEED_EMAIL'] : App::$lang['NEED_ID']);
		}
		if(StrLenPost('pwd') < 1){
			URLReplace('-1', App::$lang['INPUT_PASSWORD']);
		}
		$res = $this->useMailId ? $this->LoginEmailCheck($key, Post('pwd')) : $this->LoginMidCheck($key, Post('pwd'));

		if($res === false){
			URLReplace('-1', App::$lang['NOT_MATCH_MEMBER']);
		}else{
			if($res['approve'] !== 'y'){
				if($this->defCfg->joinApprove->Val() !== 'y' && $this->defCfg->emailCer->Val() !== 'n') URLRedirect(-1, App::$lang['NO_AUTH_EMAIL']);
				else URLRedirect(-1, App::$lang['NOT_APPROVE_ID']);
			}
			else{
				DB::UpdateQryObj(TABLE_MEMBER)
					->AddWhere('muid = %d', $res['muid'])
					->SetDataStr('login_date', date('Y-m-d H:i:s'))
					->Run();

				\BHG::$session->member->muid->Set($res['muid'], false);
				\BHG::$session->member->level->Set($res['level']);

				// 로그인 카운터
				$vcnt = \Common\VisitCounter::GetInstance();
				$vcnt->InsertLoginCounter();

				URLReplace(EmptyGet('r_url') ? \Paths::Url().'/' : Get('r_url'));
			}
		}
	}

	public function Register(){
		App::View();
	}

	public function PostRegister(){
		App::$html = 'RegisterForm.html';
		$this->model->data['nickname']->required = true;
		App::$data['email1'] = '';
		App::$data['email2'] = '';
		App::View($this->model);
	}

	private function _PostRegister($msg){
		App::$data['alertMsg'] = $msg;
		App::$data['email1'] = Post('email1');
		App::$data['email2'] = Post('email2');
		App::View('RegisterForm', $this->model);
	}

	public function PostRegisterProcess(){
		$email = !is_null(Post('email')) ? Post('email') : ((!EmptyPost('email1') && EmptyPost('email2')) ? Post('email1').'@'.Post('email2') : '');

		$this->model->SetPostValues();

		if(strlen($email)){
			$res = $this->Check('email', $email, false);
			if(!$res){
				$this->_PostRegister(App::$lang['ALREADY_EMAIL']);
				return;
			}
		}
		else if($this->useMailId) URLRedirect(-1, App::$lang['MSG_WRONG_CONNECTED']);

		if(!EmptyPost('mid')){
			$res = $this->Check('mid', Post('mid'), false);
			if(!$res){
				$this->_PostRegister(App::$lang['ALREADY_ID']);
				return;
			}
		}
		else if(!$this->useMailId) URLRedirect(-1, App::$lang['MSG_WRONG_CONNECTED']);

		$res = $this->Check('nickname', Post('nickname'));
		if(!$res){
			$this->_PostRegister(App::$lang['ALREADY_NICKNAME']);
			return;
		}

		if(Post('pwd') != Post('chkpwd')){
			$this->_PostRegister(App::$lang['MSG_NOT_MATCH_PASSWORD']);
			return;
		}

		if($this->useMailId){
			$this->model->SetValue('email', $email);
			$this->model->SetValue('mid', $email);
		}else{
			$this->model->SetValue('mid', Post('mid'));
		}
		if($this->defCfg->joinApprove->Val() != 'y') $this->model->SetValue('approve', 'n');
		else $this->model->SetValue('approve', 'y');

		$this->model->SetValue('level', 1);
		$this->model->SetValue('reg_date', date('Y-m-d H:i:s'));

		$ErrorMessage = $this->model->GetErrorMessage();
		if(sizeof($ErrorMessage)){
			$this->_PostRegister($ErrorMessage[0]);
			return;
		}

		$res = $this->model->DBInsert();
		if(!$res->result){
			$this->_PostRegister('DB ERROR');
			return;
		}
		else $this->model->_muid->value = $res->id;

		if($this->defCfg->joinApprove->Val() !== 'y' && $this->defCfg->emailCer->Val() !== 'n'){
			$this->_SendEmailCode($this->model);
			URLReplace(\Paths::Url().'/', App::$lang['REGISTERED_AND_CHECK_EMAIL']);
		}
		else URLReplace(\Paths::Url().'/', App::$lang['MSG_COMPLETE_REGISTER']);

	}

	public function EmailCheck(){
		$res = $this->Check('email', Get('email') ? Get('email') : (Get('email1').(EmptyGet('email2') ? '' : '@'.Get('email2'))), false);
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
		unset(\BHG::$session->member);
		URLReplace(\Paths::Url().'/');
	}

	private function LoginMidCheck($mid, $pwd){
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->SetKey(array('muid', 'level', 'pwd', 'approve'));
		$dbGet->AddWhere('mid = %s', $mid);
		//$params->test = true;
		$res = $dbGet->Get();
		if($res === false) return false;
		return (_password_verify($pwd, $res['pwd'])) ? array('muid' => $res['muid'], 'level' => $res['level'], 'approve' => $res['approve']) : false;
	}

	private function LoginEmailCheck($email, $pwd){
		$dbGet = new \BH_DB_Get();
		$dbGet->table = $this->model->table;
		$dbGet->SetKey(array('muid', 'level', 'pwd', 'approve'));
		$dbGet->AddWhere('email = %s', $email);
		$res = $dbGet->Get();
		if($res === false) return false;
		return (_password_verify($pwd, $res['pwd'])) ? array('muid' => $res['muid'], 'level' => $res['level'], 'approve' => $res['approve']) : false;
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
		$key = EmptyPost('mname') ? 'nickname' : 'mname';
		if(EmptyPost($key)) JSON(false, App::$lang['MSG_WRONG_CONNECTED']);
		if(EmptyPost('email')) JSON(false, App::$lang['MSG_WRONG_CONNECTED']);
		if(App::$id == 'PW' && !StrLenPost('mid')) JSON(false, App::$lang['MSG_WRONG_CONNECTED']);

		$qry = DB::GetQryObj($this->model->table)
			->AddWhere($key . ' = %s', Post($key))
			->AddWhere('email = %s', Post('email'))
			->SetKey('muid', 'mname', 'nickname', 'mid', 'email');

		// 패스워드 찾기
		if(App::$id == 'PW'){
			$qry->AddWhere('mid = %s', Post('mid'));
			$res = $qry->Get();
			if($res){
				$mail = Email::GetInstance();
				$mail->AddMail(Post('email'), Post($key));

				$t = explode(' ', microtime());
				$t2 = trim(str_replace('.','',$t[0]));
				$code = date('Y-m-d H:i:s').toBase(mt_rand(10000, 99999).substr($t2, 0, 4), 36);

				\DB::SQL()->Query('UPDATE %1 SET pw_reset_code = %s WHERE muid = %d', $this->model->table, aes_encrypt($code, PW_RESET_KEY), $res['muid']);
				$mail->SendMailByFindPW(Post($key), Post('mid'), substr($code, 19));
				JSON(true, str_replace('{email}', GetDBText(Post('email')), App::$lang['SEND_PW_CHANGE_CODE']));
			}else JSON(false, App::$lang['NOT_MATCH_MEMBER_BY_INF']);
		}

		// 아이디 찾기
		else{
			$res = $qry->Get();
			if($res){
				$mail = Email::GetInstance();
				$mail->AddMail(Post('email'), Post($key));
				$mail->SendMailByFindID(Post($key), $res['mid']);
				JSON(true, str_replace('{email}', GetDBText(Post('email')), App::$lang['SEND_EMAIL_ID']));
			}else JSON(false, App::$lang['NOT_MATCH_MEMBER_BY_INF']);
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
			URLRedirect(-1, App::$lang['ID_NO_EXIST']);
		}
		/**
		 * @var \MemberModel
		 */
		$this->model;
		$this->model->AddExcept('tel', 'phone');
		$this->model->SetArrayToData($data);

		$this->model->SetValue('pwd', Post('pwd'));
		$this->model->SetValue('pw_reset_code', '');

		$err = $this->model->GetErrorMessage();
		if(sizeof($err)){
			URLRedirect(-1, $err[0]);
			return;
		}
		if(Post('pwd') !== Post('chkpwd')){
			URLRedirect(-1, App::$lang['MSG_NOT_MATCH_PASSWORD']);
			return;
		}


		if($data){
			if($data['pw_reset_code']){
				$temp = aes_decrypt($data['pw_reset_code'], PW_RESET_KEY);
				$date = substr($temp, 0, 19);
				$code = substr($temp, 19);
				$min = date('Y-m-d H:i:s', strtotime('-1 hour'));
				if($date < $min){
					URLRedirect(-1, App::$lang['EXPIRED_CODE']);
					return;
				}
				if($code !== Post('pw_reset_code')){
					URLRedirect(-1, App::$lang['NOT_MATCH_PW_CHANGE_CODE']);
					return;
				}

				$res = $this->model->DBUpdate();
				if($res->result){
					URLRedirect(\Paths::Url().'/', App::$lang['PW_CHANGED']);
				}
				else{
					URLRedirect('-1', App::$lang['ERROR_PW_CHANGE']);
				}
			}
			else URLRedirect(-1, App::$lang['ACCOUNT_NO_REQUEST_CHANGE_PASSWORD']);
		}

	}

	public function EmailCertification(){
		if($this->defCfg->joinApprove->Val() === 'y' || $this->defCfg->emailCer->Val() === 'n'){
			URLRedirect(-1, App::$lang['MSG_WRONG_CONNECTED'].'(1)');
		}
		if(EmptyGet('code')) URLRedirect(-1, App::$lang['MSG_WRONG_CONNECTED'].'(2)');

		$temp = explode(':', Get('code'));
		if(sizeof($temp) !== 2) URLRedirect(-1, App::$lang['MSG_WRONG_CONNECTED'].'(3)');
		$mid = $temp[0];
		$get_code = $temp[1];
		$data = DB::GetQryObj($this->model->table)
			->AddWhere('`mid` = %s', $mid)
			->Get();
		if(!$data){
			URLRedirect(-1, App::$lang['ID_OF_CODE_NOT_EXIST']);
		}
		else{
			if($data['approve'] == 'y'){
				URLRedirect(-1, App::$lang['ALREADY_AUTH_ID']);
			}
			else if($data['email_code']){
				$temp = aes_decrypt($data['email_code'], PW_RESET_KEY);
				$code = substr($temp, 19);
				if($code !== $get_code){
					URLRedirect(-1, App::$lang['MSG_WRONG_CONNECTED'].'(4)'.$code.'/'.$get_code);
					return;
				}

				$res = DB::UpdateQryObj(TABLE_MEMBER)
					->AddWhere('muid = %d', $data['muid'])
					->SetDataStr('email_code', '')
					->SetDataStr('approve', 'y')
					->Run();
				if($res->result){
					URLRedirect(\Paths::Url().'/', App::$lang['APPROVED']);
				}
				else{
					URLRedirect('-1', App::$lang['ERROR_APPROVE_DB']);
				}
			}
			else URLRedirect(-1, App::$lang['NOT_REGISTERED_AUTH_CODE']);
		}

	}

	/**
	 * @param $model \MemberModel
	 */
	private function _SendEmailCode($model){
		$mail = Email::GetInstance();
		$mail->AddMail($model->_email->value, $model->_mname->value);


		$t = explode(' ', microtime());
		$t2 = trim(str_replace('.','',$t[0]));
		$code = date('Y-m-d H:i:s').toBase(mt_rand(10000, 99999).substr($t2, 0, 4), 36);

		\DB::SQL()->Query('UPDATE %1 SET email_code = %s WHERE muid = %d', $this->model->table, aes_encrypt($code, PW_RESET_KEY), $model->_muid->value);
		$mail->SendMailByEmailCertification($model->_mid->value, $model->_mname->value, $model->_mid->value.':'.substr($code, 19));
	}

	public static function PwdCheck($pwd){
		$checkPwd = preg_replace(NOT_ENG_NUM_SPECIAL_CHAR_PATTERN,'', $pwd);
		if($checkPwd !== $pwd) return \BH_Result::Init(false, App::$lang['PWD_USABLE_ENG_SPECIAL_NUMBER_CHAR']);
		$checkPwd = preg_replace(SPECIAL_CHAR_PATTERN,'', $pwd);
		if($checkPwd === $pwd) return \BH_Result::Init(false, App::$lang['PWD_NEED_SPECIAL_CHAR']);
		$checkPwd = preg_replace('#[a-zA-Z]#','', $pwd);
		if($checkPwd === $pwd) return \BH_Result::Init(false, App::$lang['PWD_NEED_ENG_CHAR']);
		$checkPwd = preg_replace('#[0-9]#','', $pwd);
		if($checkPwd === $pwd) return \BH_Result::Init(false, App::$lang['PWD_NEED_NUMBER_CHAR']);
		return \BH_Result::Init(true);
	}
}