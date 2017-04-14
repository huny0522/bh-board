<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Admin;

class ContentManagerController extends \BH_Controller{

	public $model;

	public function __Init(){
		$this->_Value['NowMenu'] = '003';
		$this->_CF->AdminAuth();

		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		$this->SetFollowQuery(array('where', 'keyword','page'));
		$this->Layout = '_Admin';
		require _DIR.'/Model/Content.model.php';
		$this->model = new \ContentModel();

		$AdminAuth = explode(',', $this->_CF->GetMember('admin_auth'));
		$this->_Value['menuAuth'] = (in_array('004', $AdminAuth) || $_SESSION['member']['level'] == _SADMIN_LEVEL);
	}

	public function Index(){
		// 리스트를 불러온다.
		$dbGetList = new \BH_DB_GetListWithPage($this->model->table.' A LEFT JOIN '.TABLE_MENU.' B ON A.bid = B.bid AND B.type=\'content\'');
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = $this->URLAction('').$this->GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->sort = 'A.reg_date DESC';
		$dbGetList->group = 'A.bid';
		$dbGetList->SetKey('A.*, group_concat(B.title SEPARATOR \', \') as title');
		$dbGetList->Run();

		$this->_View($this->model, $dbGetList);
	}
	public function View(){
		$res = $this->model->DBGet($_GET['bid']);

		$dbGet = new \BH_DB_GetList(TABLE_MENU);
		$dbGet->AddWhere('type=\'content\'');
		$dbGet->AddWhere('bid='.SetDBText($this->model->GetValue('bid')));
		$this->_Value['selectedMenu'] = $dbGet->GetRows();

		if(!$res->result){
			Redirect('-1', $res->message);
		}

		$this->_View($this->model);
	}
	public function Write(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		$this->_Value['menu'] = $dbGetList->GetRows();
		$this->_View($this->model);
	}
	public function Modify(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		$this->_Value['menu'] = $dbGetList->GetRows();

		$res = $this->model->DBGet($_GET['bid']);
		$dbGet = new \BH_DB_GetList(TABLE_MENU);
		$dbGet->AddWhere('type=\'content\'');
		$dbGet->AddWhere('bid='.SetDBText($this->model->GetValue('bid')));
		$this->_Value['selectedMenu'] = $dbGet->GetRows();

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
			$this->model->SetValue('reg_date',date('Y-m-d H:i:s'));
			$res = $this->model->DBInsert();
			if($res->result){
				$this->_CF->MenuConnect($this->model->GetValue('bid'), 'content');
				Redirect($this->URLAction());
			}else{
				Redirect('-1', '등록에 실패했습니다.');
			}
		}
	}
	public function PostModify(){
		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			$res = $this->model->DBUpdate();
			if($res->result){
				$this->_CF->MenuConnect($this->model->GetValue('bid'), 'content');
				$url = $this->URLAction('View').'?bid='.$_POST['bid'].$this->GetFollowQuery();
				Redirect($url, '수정완료');
			}else{
				Redirect('-1', '수정에 실패했습니다.');
			}
		}
	}

	public function GetSubMenu(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '.(strlen($this->ID) + _CATEGORY_LENGTH));
		$dbGetList->AddWhere('LEFT(category, '.strlen($this->ID).') = '.SetDBText($this->ID));
		JSON(true, '', $dbGetList->GetRows());

	}
}