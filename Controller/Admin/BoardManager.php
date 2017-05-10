<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Admin;
use \BH_Application as App;
use \BH_Common as CF;

class BoardManagerController
{

	/**
	 * @var \BoardManagerModel
	 */
	public $model = null;

	public function __construct(){
		$this->model = App::GetModel('BoardManager');
	}

	public function __init(){
		App::$Data['NowMenu'] = '002';
		CF::AdminAuth();

		$AdminAuth = explode(',', CF::GetMember('admin_auth'));
		App::$Data['menuAuth'] = (in_array('004', $AdminAuth) || $_SESSION['member']['level'] == _SADMIN_LEVEL);

		App::SetFollowQuery(array('where', 'keyword','page'));
		App::$Layout = '_Admin';
	}

	public function Index(){

		// 리스트를 불러온다.
		$dbGetList = new \BH_DB_GetListWithPage($this->model->table.' A LEFT JOIN '.TABLE_MENU.' B ON A.bid = B.bid AND B.type=\'board\'');
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = App::URLAction('').App::GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->group = 'A.bid';
		$dbGetList->sort = 'A.reg_date DESC';
		$dbGetList->SetKey('A.*, group_concat(B.title SEPARATOR \', \') as title');
		$dbGetList->Run();

		App::View($this, $this->model, $dbGetList);
	}
	public function View(){
		$res = $this->model->DBGet($_GET['bid']);

		$dbGet = new \BH_DB_GetList(TABLE_MENU);
		$dbGet->AddWhere('type=\'board\'');
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
		$dbGet->AddWhere('type=\'board\'');
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
		if(!$res->result) Redirect('-1',$res->message);

		$this->model->SetValue('reg_date', date('Y-m-d H:i:s'));
		$res = $this->model->DBInsert();
		if($res->result){
			$r1 = $this->model->CreateTableBoard(TABLE_FIRST.'bbs_'.$this->model->GetValue('bid'));
			if($r1){
				$r2 = $this->model->CreateTableReply(TABLE_FIRST.'bbs_'.$this->model->GetValue('bid').'_reply');
				if($r2){
					$r3 = $this->model->CreateTableImg(TABLE_FIRST.'bbs_'.$this->model->GetValue('bid').'_images');
					if($r3){
						CF::MenuConnect($this->model->GetValue('bid'), 'board');
					}
				}
			}
			Redirect(App::URLAction().App::GetFollowQuery());
		}

		Redirect(App::URLAction().App::GetFollowQuery(), 'ERROR');
	}

	public function PostModify(){
		$res = $this->model->DBGet($_POST['bid']);
		if(!$res->result) Redirect('-1',$res->message);

		$res = $this->model->SetPostValues();
		if(!$res->result) Redirect('-1',$res->message);
		$res = $this->model->DBUpdate();

		if($res->result){
			CF::MenuConnect($this->model->GetValue('bid'), 'board');
			$url = App::URLAction('View').'?bid='.$_POST['bid'].App::GetFollowQuery();
			Redirect($url, '수정완료');
		}
		Redirect('-1', 'ERROR');
	}

	public function PostDelete(){
		if(isset($_POST['bid']) && $_POST['bid'] != ''){
			$res = $this->model->DBDelete($_POST['bid']);
			if($res->result){
				$board_nm = TABLE_FIRST.'bbs_'.$_POST['bid'];

				@\DB::SQL()->Query("DROP TABLE `{$board_nm}`");
				\BH_DB_Cache::DelPath($board_nm);
				@\DB::SQL()->Query("DROP TABLE `{$board_nm}_reply`");
				\BH_DB_Cache::DelPath($board_nm.'_reply');
				@\DB::SQL()->Query("DROP TABLE `{$board_nm}_images`");
				\BH_DB_Cache::DelPath($board_nm.'_images');

				Redirect(App::URLAction('').App::GetFollowQuery(), '삭제되었습니다.');
			}else{
				Redirect('-1', $res->message);
			}
		}
	}

	public function GetSubMenu(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '.(strlen(App::$ID) + _CATEGORY_LENGTH));
		$dbGetList->AddWhere('LEFT(category, '.strlen(App::$ID).') = '.SetDBText(App::$ID));
		JSON(true, '', $dbGetList->GetRows());

	}

}