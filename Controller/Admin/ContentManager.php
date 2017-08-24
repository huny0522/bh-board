<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class ContentManager{

	/**
	 * @var \ContentModel;
	 */
	public $model;

	public function __construct(){
		$this->model = App::InitModel('Content');
	}

	public function __init(){
		App::$Data['NowMenu'] = '003';
		CM::AdminAuth();

		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		App::SetFollowQuery(array('where', 'keyword','page'));
		App::$Layout = '_Admin';

		$AdminAuth = explode(',', CM::GetMember('admin_auth'));
		App::$Data['menuAuth'] = (in_array('004', $AdminAuth) || $_SESSION['member']['level'] == _SADMIN_LEVEL);
	}

	public function Index(){
		// 리스트를 불러온다.
		$dbGetList = new \BH_DB_GetListWithPage($this->model->table.' A LEFT JOIN '.TABLE_MENU.' B ON A.bid = B.bid AND B.type=\'content\'');
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = App::URLAction('').App::GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->sort = 'A.reg_date DESC';
		$dbGetList->group = 'A.bid';
		$dbGetList->SetKey('A.*, group_concat(B.title SEPARATOR \', \') as title');
		$dbGetList->Run();

		App::View($this->model, $dbGetList);
	}
	public function View(){
		$res = $this->model->DBGet($_GET['bid']);

		$dbGet = new \BH_DB_GetList(TABLE_MENU);
		$dbGet->AddWhere('type=\'content\'');
		$dbGet->AddWhere('bid = %s', $this->model->GetValue('bid'));
		App::$Data['selectedMenu'] = $dbGet->GetRows();

		if(!$res->result){
			URLReplace('-1', $res->message);
		}

		App::View($this->model);
	}
	public function Write(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		App::$Data['menu'] = $dbGetList->GetRows();
		App::View($this->model);
	}
	public function Modify(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		App::$Data['menu'] = $dbGetList->GetRows();

		$res = $this->model->DBGet($_GET['bid']);
		$dbGet = new \BH_DB_GetList(TABLE_MENU);
		$dbGet->AddWhere('type=\'content\'');
		$dbGet->AddWhere('bid = %s', $this->model->GetValue('bid'));
		App::$Data['selectedMenu'] = $dbGet->GetRows();

		if(!$res->result){
			URLReplace('-1', $res->message);
		}
		App::$Html = 'Write';
		App::View($this->model);
	}
	public function PostWrite(){
		$res = $this->model->SetPostValues();
		if(!$res->result){
			URLReplace('-1',$res->message);
		}
		else{
			$this->model->SetValue('reg_date',date('Y-m-d H:i:s'));
			$res = $this->model->DBInsert();
			if($res->result){
				CM::MenuConnect($this->model->GetValue('bid'), 'content');
				URLReplace(App::URLAction());
			}else{
				URLReplace('-1', '등록에 실패했습니다.');
			}
		}
	}
	public function PostModify(){
		$res = $this->model->DBGet($_POST['bid']);
		if(!$res->result){
			URLReplace('-1',$res->message);
		}

		$res = $this->model->SetPostValues();
		if(!$res->result){
			URLReplace('-1',$res->message);
		}
		else{
			$res = $this->model->DBUpdate();
			if($res->result){
				CM::MenuConnect($this->model->GetValue('bid'), 'content');
				$url = App::URLAction('View').'?bid='.$_POST['bid'].App::GetFollowQuery();
				URLReplace($url, '수정완료');
			}else{
				URLReplace('-1', '수정에 실패했습니다.');
			}
		}
	}

	public function GetSubMenu(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = %d', strlen(App::$ID) + _CATEGORY_LENGTH);
		$dbGetList->AddWhere('LEFT(category, %d) = %s', strlen(App::$ID), App::$ID);
		JSON(true, '', $dbGetList->GetRows());
	}

	public function PostDelete(){
		$res = $this->model->DBDelete($_POST['bid']);
		if($res->result) URLReplace(App::URLAction('').App::GetFollowQuery());
		else URLReplace('-1', '삭제에 실패했습니다.');
	}
}