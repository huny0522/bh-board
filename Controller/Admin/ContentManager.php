<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Admin;
use \BH_Application as App;
use \BH_Common as CF;

class ContentManagerController{

	/**
	 * @var \ContentModel;
	 */
	public $model;

	public function __construct(){
		$this->model = App::GetModel('Content');
	}

	public function __init(){
		App::$Data['NowMenu'] = '003';
		CF::AdminAuth();

		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		App::SetFollowQuery(array('where', 'keyword','page'));
		App::$Layout = '_Admin';

		$AdminAuth = explode(',', CF::GetMember('admin_auth'));
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

		App::View($this, $this->model, $dbGetList);
	}
	public function View(){
		$res = $this->model->DBGet($_GET['bid']);

		$dbGet = new \BH_DB_GetList(TABLE_MENU);
		$dbGet->AddWhere('type=\'content\'');
		$dbGet->AddWhere('bid='.SetDBText($this->model->GetValue('bid')));
		App::$Data['selectedMenu'] = $dbGet->GetRows();

		if(!$res->result){
			Redirect('-1', $res->message);
		}

		App::View($this, $this->model);
	}
	public function Write(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		App::$Data['menu'] = $dbGetList->GetRows();
		App::View($this, $this->model);
	}
	public function Modify(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		App::$Data['menu'] = $dbGetList->GetRows();

		$res = $this->model->DBGet($_GET['bid']);
		$dbGet = new \BH_DB_GetList(TABLE_MENU);
		$dbGet->AddWhere('type=\'content\'');
		$dbGet->AddWhere('bid='.SetDBText($this->model->GetValue('bid')));
		App::$Data['selectedMenu'] = $dbGet->GetRows();

		if(!$res->result){
			Redirect('-1', $res->message);
		}
		App::$Html = 'Write';
		App::View($this, $this->model);
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
				CF::MenuConnect($this->model->GetValue('bid'), 'content');
				Redirect(App::URLAction());
			}else{
				Redirect('-1', '등록에 실패했습니다.');
			}
		}
	}
	public function PostModify(){
		$res = $this->model->DBGet($_POST['bid']);
		if(!$res->result){
			Redirect('-1',$res->message);
		}

		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			$res = $this->model->DBUpdate();
			if($res->result){
				CF::MenuConnect($this->model->GetValue('bid'), 'content');
				$url = App::URLAction('View').'?bid='.$_POST['bid'].App::GetFollowQuery();
				Redirect($url, '수정완료');
			}else{
				Redirect('-1', '수정에 실패했습니다.');
			}
		}
	}

	public function GetSubMenu(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '.(strlen(App::$ID) + _CATEGORY_LENGTH));
		$dbGetList->AddWhere('LEFT(category, '.strlen(App::$ID).') = '.SetDBText(App::$ID));
		JSON(true, '', $dbGetList->GetRows());
	}

	public function PostDelete(){
		$res = $this->model->DBDelete($_POST['bid']);
		if($res->result) Redirect(App::URLAction('').App::GetFollowQuery());
		else Redirect('-1', '삭제에 실패했습니다.');
	}
}