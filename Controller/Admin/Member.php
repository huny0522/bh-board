<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Admin;
use \BH_Application as App;
use \BH_Common as CF;

class MemberController{

	/**
	 * @var MemberModel
	 */
	public $model = NULL;

	public function __construct(){
		require_once _MODELDIR.'/Member.model.php';
		$this->model = new \MemberModel();
	}

	public function __init(){
		App::$_Value['NowMenu'] = '005';
		CF::Get()->AdminAuth();

		App::$Instance->SetFollowQuery(array('SLevel', 'keyword','page'));
		App::$Instance->Layout = '_Admin';
	}

	public function Index(){

		// 리스트를 불러온다.
		$dbGetList = new \BH_DB_GetListWithPage($this->model->table);
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = App::$Instance->URLAction('').App::$Instance->GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->AddWhere('level < '.$_SESSION['member']['level'].' OR muid = '.$_SESSION['member']['muid']);
		if(isset($_GET['Keyword']) && strlen(trim($_GET['Keyword']))){
			$keywrod = my_escape_string(trim($_GET['Keyword']));
			$dbGetList->AddWhere('( mid LIKE \'%'.$keywrod.'%\' OR email LIKE \'%'.$keywrod.'%\' OR mname LIKE \'%'.$keywrod.'%\' OR nickname LIKE \'%'.$keywrod.'%\' OR phone LIKE \'%'.$keywrod.'%\' )');
		}
		if(isset($_GET['SLevel']) && strlen($_GET['SLevel'])){
			$dbGetList->AddWhere('level='.SetDBInt($_GET['SLevel']));
		}
		$dbGetList->Run();

		App::$Instance->_View($this, $this->model, $dbGetList);
	}

	public function View(){
		$res = $this->model->DBGet($_GET['muid']);
		if($this->model->GetValue('level') > $_SESSION['member']['level'] || ($_SESSION['member']['muid'] != $this->model->GetValue('muid') && $this->model->GetValue('level') == $_SESSION['member']['level'])){
			Redirect('-1', _WRONG_CONNECTED);
		}

		if(!$res->result){
			Redirect('-1', $res->message);
		}

		App::$Instance->_View($this, $this->model);
	}
	public function Write(){
		foreach($this->model->data['level']->EnumValues as $k => $v){
			if($k <= $_SESSION['member']['level']) App::$_Value['level'][$k] = $v;
		}
		App::$Instance->_View($this, $this->model);
	}
	public function Modify(){
		foreach($this->model->data['level']->EnumValues as $k => $v){
			if($k <= $_SESSION['member']['level']) App::$_Value['level'][$k] = $v;
		}
		$this->model->data['pwd']->Required = false;
		$res = $this->model->DBGet($_GET['muid']);
		if($this->model->GetValue('level') > $_SESSION['member']['level'] || ($_SESSION['member']['muid'] != $this->model->GetValue('muid') && $this->model->GetValue('level') == $_SESSION['member']['level'])){
			Redirect('-1', _WRONG_CONNECTED);
		}

		if(!$res->result){
			Redirect('-1', $res->message);
		}
		App::$Instance->Html = 'Write';
		App::$Instance->_View($this, $this->model);
	}
	public function PostWrite(){
		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			if($this->model->GetValue('level') >= $_SESSION['member']['level']){
				Redirect('-1', '해당 레벨로 등록이 불가능합니다.');
			}
			$this->model->SetValue('reg_date', date('Y-m-d H:i:s'));
			$res = $this->model->DBInsert();
			if($res->result){
				Redirect(App::$Instance->URLAction().App::$Instance->GetFollowQuery());
			}else{
				Redirect(App::$Instance->URLAction().App::$Instance->GetFollowQuery(), 'ERROR');
			}
		}
	}

	public function PostModify(){
		if(!$_POST['pwd']) unset($_POST['pwd']);

		$res = $this->model->DBGet($_POST['muid']);
		if($this->model->GetValue('level') > $_SESSION['member']['level'] || ($_SESSION['member']['muid'] != $this->model->GetValue('muid') && $this->model->GetValue('level') == $_SESSION['member']['level'])){
			Redirect('-1', _WRONG_CONNECTED);
		}

		$res = $this->model->SetPostValues();
		if($this->model->GetValue('level') >= $_SESSION['member']['level'] && $this->model->GetValue('muid') != $_SESSION['member']['muid']){
			Redirect('-1', '해당 레벨로 등록이 불가능합니다.');
		}

		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			$res = $this->model->DBUpdate();
			if($res->result){
				$url = App::$Instance->URLAction('View').'?muid='.$_POST['muid'].App::$Instance->GetFollowQuery();
				Redirect($url, '수정완료');
			}else{
				Redirect('-1', 'ERROR');
			}
		}
	}

	public function PostDelete(){
		$this->model->DBGet($_POST['muid']);

		if($this->model->GetValue('level') >= $_SESSION['member']['level']){
			Redirect('-1', '관리자는 삭제가 불가능합니다.');
		}
		if(isset($_POST['muid']) && $_POST['muid'] != ''){
			$res = $this->model->DBDelete($_POST['muid']);
			if($res->result){
				Redirect(App::$Instance->URLAction('').App::$Instance->GetFollowQuery(), '삭제되었습니다.');
			}else{
				Redirect('-1', $res->message);
			}
		}
	}

	public function AuthAdmin(){
		unset(App::$Instance->Layout);
		if($_SESSION['member']['level'] != _SADMIN_LEVEL) return;
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->AddWhere('muid='.SetDBInt($_GET['muid']));
		$dbGet->SetKey(array('level', 'admin_auth'));
		$res = $dbGet->Get();
		if(!$res) return;
		if($res['level'] != _ADMIN_LEVEL) return;
		App::$_Value['auth'] = explode(',', $res['admin_auth']);
		App::$Instance->_View($this);
	}

	public function PostAuthAdmin(){
		if($_SESSION['member']['level'] != _SADMIN_LEVEL) JSON(false, _WRONG_CONNECTED);
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