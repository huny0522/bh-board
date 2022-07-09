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

class DeveloperPage
{

	public $memberModel = null;

	public function __construct(){
		$this->memberModel = \MemberModel::GetInstance();
	}

	public function __init(){
		// index.php 에서 $_DEVELOPER_IP 에 등록되지 않으면 로그인 할 수 없다.
		if(_IS_DEVELOPER_IP !== true) URLReplace(App::URLBase());
	}

	public function Index(){
		App::View('/DeveloperPage');
	}

	public function PostLogin(){
		if(StrLenPost('mid') < 1){
			URLReplace('-1', App::$lang['NEED_ID']);
		}
		if(StrLenPost('pwd') < 1){
			URLReplace('-1', App::$lang['INPUT_PASSWORD']);
		}
		$res = $this->LoginMidCheck(Post('mid'), Post('pwd'));

		if($res === false){
			URLReplace('-1', App::$lang['NOT_MATCH_MEMBER']);
		}else{
			if($res['approve'] !== 'y'){
				URLRedirect(-1, App::$lang['NOT_APPROVE_ID']);
			}
			else{
				DB::UpdateQryObj(TABLE_MEMBER)
					->AddWhere('muid = %d', $res['muid'])
					->SetDataStr('login_date', date('Y-m-d H:i:s'))
					->Run();
				$_SESSION['developer_login_muid'] = $res['muid'];
				$_SESSION['developer_login'] = 'y';

				URLReplace(App::URLAction());
			}
		}
	}

	public function Logout(){
		unset($_SESSION['developer_login_muid']);
		unset($_SESSION['developer_login']);
		URLReplace(App::URLAction());
	}

	public function DeleteHTML(){
		if(\BHG::$isDeveloper !== true) URLReplace(App::URLAction());
		delTree(\Paths::DirOfHtml());
		URLReplace(App::URLAction(), App::$lang['MSG_COMPLETE_DELETE']);
	}

	public function CacheUriQuery(){
		if(\BHG::$isDeveloper !== true) URLReplace(App::URLAction());
		App::$cfg->Sys()->refresh->value++;
		$res = App::$cfg->Sys()->DataWrite();
		URLReplace(App::URLAction(), App::$lang['MSG_COMPLETE_MODIFY'] . ' -> ' . App::$cfg->Sys()->refresh->value);
	}

	private function LoginMidCheck($mid, $pwd){
		$res = DB::GetQryObj('`%1`', $this->memberModel->table)
			->SetKey(array('muid', 'level', 'pwd', 'approve'))
			->AddWhere('mid = %s', $mid)
			->AddWhere('level = %d', _SADMIN_LEVEL)
			->Get();
		if($res === false) return false;
		return (_password_verify($pwd, $res['pwd'])) ? array('muid' => $res['muid'], 'level' => $res['level'], 'approve' => $res['approve']) : false;
	}
}