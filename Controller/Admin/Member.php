<?php
/**
 * Bang Hun.
 * 16.07.10
 */

require _DIR.'/Model/Member.model.php';

class MemberController extends BH_Controller{

	/**
	 * @var MemberModel
	 */
	public $model = NULL;

	public function __Init(){
		$this->_Value['NowMenu'] = '005';
		$this->Common->AdminAuth();

		$this->model = new MemberModel();

		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		$this->SetFollowQuery(array('SLevel', 'keyword','page'));
		$this->Layout = '_Admin';
	}

	public function Index(){

		// 리스트를 불러온다.
		$dbGetList = new BH_DB_GetListWithPage();
		$dbGetList->table = $this->model->table;
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = $this->URLAction('').$this->GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		if(isset($_GET['Keyword']) && strlen(trim($_GET['Keyword']))){
			$keywrod = my_escape_string(trim($_GET['Keyword']));
			$dbGetList->AddWhere('( mid LIKE \'%'.$keywrod.'%\' OR email LIKE \'%'.$keywrod.'%\' OR mname LIKE \'%'.$keywrod.'%\' OR nickname LIKE \'%'.$keywrod.'%\' OR phone LIKE \'%'.$keywrod.'%\' )');
		}
		if(isset($_GET['SLevel']) && strlen($_GET['SLevel'])){
			$dbGetList->AddWhere('level='.SetDBInt($_GET['SLevel']));
		}
		$dbGetList->Run();

		$this->_View($this->model, $dbGetList);
	}

	public function View(){
		$res = $this->model->DBGet($_GET['muid']);

		if(!$res->result){
			Redirect('-1', $res->message);
		}

		$this->_View($this->model);
	}
	public function Write(){
		$this->_View($this->model);
	}
	public function Modify(){
		$this->model->data['pwd']->Required = false;
		$res = $this->model->DBGet($_GET['muid']);

		if(!$res->result){
			Redirect('-1', $res->message);
		}
		$this->Html = 'Write';
		$this->_View($this->model);
	}
	public function PostWrite(){
		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			$res = $this->model->DBInsert();
			if($res->result){
				Redirect($this->URLAction().$this->GetFollowQuery());
			}else{
				Redirect($this->URLAction().$this->GetFollowQuery(), 'ERROR');
			}
		}
	}

	public function PostModify(){
		if(!$_POST['pwd']) unset($_POST['pwd']);

		$res = $this->model->DBGet($_POST['muid']);
		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			$res = $this->model->DBUpdate();
			if($res->result){
				$url = $this->URLAction('View').'?muid='.$_POST['muid'].$this->GetFollowQuery();
				Redirect($url, '수정완료');
			}else{
				Redirect('-1', 'ERROR');
			}
		}
	}

	public function PostDelete(){
		$this->model->DBGet($_POST['muid']);
		if($this->model->GetValue('level') == _SADMIN_LEVEL){
			Redirect('-1', '최고관리자는 삭제가 불가능합니다.');
		}
		if(isset($_POST['muid']) && $_POST['muid'] != ''){
			$res = $this->model->DBDelete($_POST['muid']);
			if($res->result){
				Redirect($this->URLAction('').$this->GetFollowQuery(), '삭제되었습니다.');
			}else{
				Redirect('-1', $res->message);
			}
		}
	}

	public function AuthAdmin(){
		unset($this->Layout);
		$dbGet = new BH_DB_Get($this->model->table);
		$dbGet->AddWhere('muid='.SetDBInt($_GET['muid']));
		$dbGet->SetKey(array('level', 'admin_auth'));
		$res = $dbGet->Get();
		if(!$res) return;
		if($res['level'] != _ADMIN_LEVEL) return;
		$this->_Value['auth'] = explode(',', $res['admin_auth']);
		$this->_View();
	}

	public function PostAuthAdmin(){
		$muid = SetDBInt($_POST['muid']);
		$dbGet = new BH_DB_Get($this->model->table);
		$dbGet->AddWhere('muid='.$muid);
		$dbGet->SetKey(array('level'));
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
		$sql = 'UPDATE '.TABLE_MEMBER.' SET admin_auth = '.SetDBText($adminAuth).' WHERE muid = ' . $muid;
		SqlQuery($sql);
		echo json_encode(array('result' => true));
	}

}