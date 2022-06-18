<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class Login{
	public $model;

	public function __construct(){
		$this->model = App::InitModel('Member');
		$this->model->data['mid']->required = true;
	}

	public function __init(){
		App::$layout = '_Empty';
	}

	public function PostDevLogin(){
		if(_IS_DEVELOPER_IP !== true) exit;

		$res = DB::GetQryObj(TABLE_MEMBER)
			->AddWhere('`mid` = \'developer\'')
			->SetKey('`pwd`')
			->Get();
		if($res === false) URLRedirect(-1, '키값 오류');
		if(_password_verify(Post('devpwd'), $res['pwd'])){
			$_SESSION['developer_login'] = 'y';
			URLRedirect(Get('r_url') ? Get('r_url') : \Paths::Url() . '/', '개발자로 로그인하셨습니다.');
		}
		else URLRedirect(-1, '비밀번호 불일치');
	}

	public function DevLogout(){
		$_SESSION['developer_login'] = null;
		unset($_SESSION['developer_login']);
		URLRedirect(Get('r_url') ? Get('r_url') : \Paths::Url() . '/');
	}

	public function Index(){
		if(_MEMBERIS === true && ($_SESSION['member']['level'] == _SADMIN_LEVEL || $_SESSION['member']['level'] == _ADMIN_LEVEL)) URLReplace(\Paths::UrlOfAdmin());
		App::View($this->model);
	}

	public function PostLogin(){
		$mid = trim($_POST['mid'] ?? '');
		if(strlen($mid) < 1){
			URLReplace('-1', '아이디를 입력하여 주세요.');
		}
		if(StrLength($_POST['pwd']) < 1){
			URLReplace('-1', '패스워드를 입력하여 주세요.');
		}
		$res = $this->LoginMidCheck($mid, $_POST['pwd'], array(_SADMIN_LEVEL, _ADMIN_LEVEL));
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
			URLReplace(\Paths::UrlOfAdmin());
		}
	}

	public function Logout(){
		$_SESSION['member'] = null;
		unset($_SESSION['member']);
		URLReplace(\Paths::UrlOfAdmin().'/Login');
	}


	/**
	 * 로그인 아이디 체크
	 *
	 * @param string $mid
	 * @param string $pwd
	 * @param integer|array $level
	 * @return array|bool
	 */
	private function LoginMidCheck($mid, $pwd, $level = 1){
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->SetKey(array('muid', 'level', 'pwd'));
		$dbGet->AddWhere('mid = %s', $mid);
		if(is_array($level)) $dbGet->AddWhere('level IN ('.implode(',', $level).')');
		else $dbGet->AddWhere('level='.$level);
		//$dbGet->test = true;
		$res = $dbGet->Get();
		if($res === false) return false;
		return (_password_verify($pwd, $res['pwd'])) ? array('muid' => $res['muid'], 'level' => $res['level']) : false;
	}

	/**
	* 로그인 이메일 체크
	*
	* @param string $email
	* @param string $pwd
	* @param integer|array $level
	* @return array|bool
	*/
	private function LoginEmailCheck($email, $pwd, $level = 1){
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->SetKey(array('muid', 'level', 'pwd'));
		$dbGet->AddWhere('email = %s', $email);
		if(is_array($level)) $dbGet->AddWhere('level IN ('.implode(',', $level).')');
		else $dbGet->AddWhere('level='.$level);
		$res = $dbGet->Get();
		if($res === false) return false;
		return (_password_verify($pwd, $res['pwd'])) ? array('muid' => $res['muid'], 'level' => $res['level']) : false;
	}
}