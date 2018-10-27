<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class Member{

	/**
	 * @var \MemberModel
	 */
	public $model = NULL;

	public function __construct(){
		$this->model = App::InitModel('Member');
	}

	public function __init(){
		App::$data['NowMenu'] = '005';
		CM::AdminAuth();

		App::SetFollowQuery(array('SLevel', 'keyword','page'));
		App::$layout = '_Admin';
	}

	public function Index(){

		// 리스트를 불러온다.
		$dbGetList = DB::GetListPageQryObj($this->model->table)
			->SetPage(Get('page'))
			->SetPageUrl(App::URLAction('').App::GetFollowQuery('page'))
			->SetArticleCount(20)
			->AddWhere('withdraw = \'n\'')
			->AddWhere('level < %d OR muid = %d', $_SESSION['member']['level'], $_SESSION['member']['muid']);
		$keyword = trim(Get('Keyword'));
		$slevel = Get('SLevel');

		if(strlen($keyword)){
			$dbGetList->AddWhere('INSTR(mid, %s) OR INSTR(email, %s) OR INSTR(mname, %s) OR INSTR(nickname, %s) OR INSTR(phone, %s)', $keyword, $keyword, $keyword, $keyword, $keyword);
		}
		if(strlen($slevel)){
			$dbGetList->AddWhere('level = %d', $slevel);
		}
		$dbGetList->Run();

		App::View($this->model, $dbGetList);
	}

	public function View(){
		$res = $this->model->DBGet($_GET['muid']);
		if($this->model->GetValue('level') > $_SESSION['member']['level'] || ($_SESSION['member']['muid'] != $this->model->GetValue('muid') && $this->model->GetValue('level') == $_SESSION['member']['level'])){
			URLReplace('-1', _MSG_WRONG_CONNECTED);
		}

		if(!$res->result){
			URLReplace('-1', $res->message);
		}

		App::View($this->model);
	}
	public function Write(){
		foreach($this->model->data['level']->enumValues as $k => $v){
			if($k <= $_SESSION['member']['level']) App::$data['level'][$k] = $v;
		}
		App::View($this->model);
	}
	public function Modify(){
		foreach($this->model->data['level']->enumValues as $k => $v){
			if($k <= $_SESSION['member']['level']) App::$data['level'][$k] = $v;
		}
		$this->model->data['pwd']->required = false;
		$res = $this->model->DBGet($_GET['muid']);
		if($this->model->GetValue('level') > $_SESSION['member']['level'] || ($_SESSION['member']['muid'] != $this->model->GetValue('muid') && $this->model->GetValue('level') == $_SESSION['member']['level'])){
			URLReplace('-1', _MSG_WRONG_CONNECTED);
		}

		if(!$res->result) URLReplace('-1', $res->message);

		App::$html = 'Write';
		App::View($this->model);
	}
	public function PostWrite(){
		$res = $this->model->SetPostValues();
		if(!$res->result){
			App::$data['error'] = $res->message ? $res->message : 'ERROR';
			App::View($this->model);
			return;
		}

		$row = \DB::SQL()->Fetch('SELECT COUNT(*) as cnt FROM %1 WHERE mid=%s', $this->model->table, $_POST['mid']);
		if($row['cnt']){
			App::$data['error'] = '중복되는 아이디가 존재합니다.';
			App::View($this->model);
			return;
		}
		$row = \DB::SQL()->Fetch('SELECT COUNT(*) as cnt FROM %1 WHERE nickname=%s', $this->model->table, $_POST['nickname']);
		if($row['cnt']){
			App::$data['error'] = '중복되는 닉네임이 존재합니다.';
			App::View($this->model);
			return;
		}
		$row = \DB::SQL()->Fetch('SELECT COUNT(*) as cnt FROM %1 WHERE email=%s', $this->model->table, $_POST['email']);
		if($row['cnt']){
			App::$data['error'] = '중복되는 이메일이 존재합니다.';
			App::View($this->model);
			return;
		}

		$err = $this->model->GetErrorMessage();
		if(sizeof($err)){
			App::$data['error'] = $err[0];
			App::View($this->model);
			return;
		}

		if($this->model->GetValue('level') >= $_SESSION['member']['level']) URLReplace('-1', '해당 레벨로 등록이 불가능합니다.');

		$this->model->SetValue('reg_date', date('Y-m-d H:i:s'));
		$res = $this->model->DBInsert();
		if($res->result) URLReplace(App::URLAction().App::GetFollowQuery());
		else URLReplace(App::URLAction().App::GetFollowQuery(), 'ERROR');

	}

	public function PostModify(){
		if(!strlen(Post('pwd'))) $this->model->AddExcept('pwd');

		$res = $this->model->DBGet(Post('muid'));
		if($this->model->GetValue('level') > $_SESSION['member']['level'] || ($_SESSION['member']['muid'] != $this->model->GetValue('muid') && $this->model->GetValue('level') == $_SESSION['member']['level'])){
			URLReplace('-1', _MSG_WRONG_CONNECTED);
		}

		$res = $this->model->SetPostValues();
		if($this->model->GetValue('level') >= $_SESSION['member']['level'] && $this->model->GetValue('muid') != $_SESSION['member']['muid']){
			URLReplace('-1', '해당 레벨로 등록이 불가능합니다.');
		}

		$err = $this->model->GetErrorMessage();
		if(sizeof($err)){
			URLRedirect('-1',$err[0]);
		}

		if(!$res->result){
			URLReplace('-1',$res->message);
		}
		else{
			$res = $this->model->DBUpdate();
			if($res->result){
				$url = App::URLAction('View').'?muid='.$_POST['muid'].App::GetFollowQuery();
				URLReplace($url, '수정완료');
			}else{
				URLReplace('-1', $res->message ? $res->message : 'ERROR');
			}
		}
	}

	public function PostWithdraw(){
		$res = Member::_Withdraw(Post('muid'), '관리자 권한 회원 탈퇴처리');
		if(!$res->result) URLRedirect(-1, $res->message ? $res->message : '탈퇴 오류');
		else URLRedirect(App::URLAction('').App::GetFollowQuery(), '탈퇴처리되었습니다.');
	}

	public function AuthAdmin(){
		App::$layout = null;
		if($_SESSION['member']['level'] != _SADMIN_LEVEL) return;
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->AddWhere('muid='.SetDBInt($_GET['muid']));
		$dbGet->SetKey(array('level', 'admin_auth'));
		$res = $dbGet->Get();
		if(!$res) return;
		if($res['level'] != _ADMIN_LEVEL) return;
		App::$data['auth'] = explode(',', $res['admin_auth']);
		JSON(true, '', App::GetView());
	}

	public function PostAuthAdmin(){
		if($_SESSION['member']['level'] != _SADMIN_LEVEL) JSON(false, _MSG_WRONG_CONNECTED);
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->AddWhere('muid =  %d', $_POST['muid']);
		$dbGet->SetKey('level');
		$res = $dbGet->Get();
		if(!$res) return;
		if($res['level'] != _ADMIN_LEVEL){
			echo json_encode(array('result' => false));
			return;
		}

		$adminAuth = '';
		if(isset($_POST['Category'])){
			$adminAuth = implode(',', $_POST['Category']);
		}
		$qry = new \BH_DB_Update(TABLE_MEMBER);
		$qry->SetDataStr('admin_auth', $adminAuth);
		$qry->AddWhere('muid = %d', $_POST['muid']);
		$qry->Run();
		echo json_encode(array('result' => true));
	}

	/**
	 * @param int $muid
	 * @param string $reason
	 * @return \BH_InsertResult|\BH_Result
	 */
	public static function _Withdraw($muid, $reason){
		$model = new \MemberModel();
		$model->DBGet($muid);
		if($model->_level->value >= _ADMIN_LEVEL && $model->_level->value >= $_SESSION['member']['level']){
			return \BH_Result::Init(false, '관리자는 탈퇴가 불가능합니다.');
		}

		$dbInsert = new \BH_DB_Insert(TABLE_WITHDRAW_MEMBER);
		$dbInsert->SetData('muid', $model->GetValue('muid'));
		$dbInsert->SetDataStr('mid', $model->GetValue('mid'));
		$dbInsert->SetDataStr('mname', $model->GetValue('mname'));
		$dbInsert->SetDataStr('cname', $model->GetValue('cname'));
		$dbInsert->SetDataStr('nickname', $model->GetValue('nickname'));
		$dbInsert->SetDataStr('level', $model->GetValue('level'));
		$dbInsert->SetDataStr('email', $model->GetValue('email'));
		$dbInsert->SetDataStr('reg_date', $model->GetValue('reg_date'));
		$dbInsert->SetDataStr('reason', $reason);
		$dbInsert->SetData('w_date', 'NOW()');

		foreach($model->data as $k => $v){
			if($k == 'muid' || $k == 'nickname' || $k == 'email' || $k == 'mid') continue;
			$v->SetMinLength(false);
			$v->SetMinValue(false);
			$v->SetRequired(false);
			if($v->type == \ModelType::INT || $v->type == \ModelType::FLOAT){
				$v->SetValue(0);
			}
			else if($v->type == \ModelType::ENUM){
				if(is_array($v->enumValues) && sizeof($v->enumValues)){
					reset($v->enumValues);
					$v->SetValue(key($v->enumValues));
				}
			}
			else if($v->type == \ModelType::DATE || $v->type == \ModelType::DATETIME){
				continue;
			}
			else{
				$v->SetValue('');
			}
		}

		$model->_withdraw->SetValue('y');
		$model->_email->SetValueIsQuery(true);
		$model->_email->SetValue('NULL');
		$model->_mid->SetValueIsQuery(true);
		$model->_mid->SetValue('NULL');
		$model->_nickname->SetValueIsQuery(true);
		$model->_nickname->SetValue('NULL');

		$err = $model->GetErrorMessage();
		if(sizeof($err)) return (object)array('result' => false, 'message' => $err[0]);
		$res = $dbInsert->Run();
		if($res->result) $res = $model->DBUpdate();

		return $res;
	}

}