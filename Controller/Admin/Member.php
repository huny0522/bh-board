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
		App::$Data['NowMenu'] = '005';
		CM::AdminAuth();

		App::SetFollowQuery(array('SLevel', 'keyword','page'));
		App::$Layout = '_Admin';
	}

	public function Index(){

		// 리스트를 불러온다.
		$dbGetList = DB::GetListPageQryObj($this->model->table)
			->SetPage(Get('page'))
			->SetPageUrl(App::URLAction('').App::GetFollowQuery('page'))
			->SetArticleCount(20)
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
		foreach($this->model->data['level']->EnumValues as $k => $v){
			if($k <= $_SESSION['member']['level']) App::$Data['level'][$k] = $v;
		}
		App::View($this->model);
	}
	public function Modify(){
		foreach($this->model->data['level']->EnumValues as $k => $v){
			if($k <= $_SESSION['member']['level']) App::$Data['level'][$k] = $v;
		}
		$this->model->data['pwd']->Required = false;
		$res = $this->model->DBGet($_GET['muid']);
		if($this->model->GetValue('level') > $_SESSION['member']['level'] || ($_SESSION['member']['muid'] != $this->model->GetValue('muid') && $this->model->GetValue('level') == $_SESSION['member']['level'])){
			URLReplace('-1', _MSG_WRONG_CONNECTED);
		}

		if(!$res->result) URLReplace('-1', $res->message);

		App::$Html = 'Write';
		App::View($this->model);
	}
	public function PostWrite(){
		$res = $this->model->SetPostValues();
		if(!$res->result){
			App::$Data['error'] = $res->message ? $res->message : 'ERROR';
			App::View($this->model);
			return;
		}

		$row = \DB::SQL()->Fetch('SELECT COUNT(*) as cnt FROM %1 WHERE mid=%s', $this->model->table, $_POST['mid']);
		if($row['cnt']){
			App::$Data['error'] = '중복되는 아이디가 존재합니다.';
			App::View($this->model);
			return;
		}
		$row = \DB::SQL()->Fetch('SELECT COUNT(*) as cnt FROM %1 WHERE nickname=%s', $this->model->table, $_POST['nickname']);
		if($row['cnt']){
			App::$Data['error'] = '중복되는 닉네임이 존재합니다.';
			App::View($this->model);
			return;
		}
		$row = \DB::SQL()->Fetch('SELECT COUNT(*) as cnt FROM %1 WHERE email=%s', $this->model->table, $_POST['email']);
		if($row['cnt']){
			App::$Data['error'] = '중복되는 이메일이 존재합니다.';
			App::View($this->model);
			return;
		}

		$err = $this->model->GetErrorMessage();
		if(sizeof($err)){
			App::$Data['error'] = $err[0];
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

	public function PostDelete(){
		$this->model->DBGet($_POST['muid']);

		if($this->model->GetValue('level') >= $_SESSION['member']['level']){
			URLReplace('-1', '관리자는 삭제가 불가능합니다.');
		}
		if(isset($_POST['muid']) && $_POST['muid'] != ''){
			$res = $this->model->DBDelete($_POST['muid']);
			if($res->result){
				URLReplace(App::URLAction('').App::GetFollowQuery(), '삭제되었습니다.');
			}else{
				URLReplace('-1', $res->message);
			}
		}
	}

	public function AuthAdmin(){
		App::$Layout = null;
		if($_SESSION['member']['level'] != _SADMIN_LEVEL) return;
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->AddWhere('muid='.SetDBInt($_GET['muid']));
		$dbGet->SetKey(array('level', 'admin_auth'));
		$res = $dbGet->Get();
		if(!$res) return;
		if($res['level'] != _ADMIN_LEVEL) return;
		App::$Data['auth'] = explode(',', $res['admin_auth']);
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

}