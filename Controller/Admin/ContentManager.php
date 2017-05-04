<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Admin;
use \BH_Application as App;
use \BH as BH;
class ContentManagerController{

	/**
	 * @var \ContentModel;
	 */
	public $model;

	public function __construct(){
		App::$_Value['NowMenu'] = '003';
		BH::CF()->AdminAuth();

		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		BH::APP()->SetFollowQuery(array('where', 'keyword','page'));
		BH::APP()->Layout = '_Admin';
		require _DIR.'/Model/Content.model.php';
		$this->model = new \ContentModel();

		$AdminAuth = explode(',', BH::CF()->GetMember('admin_auth'));
		App::$_Value['menuAuth'] = (in_array('004', $AdminAuth) || $_SESSION['member']['level'] == _SADMIN_LEVEL);
	}

	public function Index(){
		// 리스트를 불러온다.
		$dbGetList = BH::DBListPage($this->model->table.' A LEFT JOIN '.TABLE_MENU.' B ON A.bid = B.bid AND B.type=\'content\'');
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = BH::APP()->URLAction('').BH::APP()->GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->sort = 'A.reg_date DESC';
		$dbGetList->group = 'A.bid';
		$dbGetList->SetKey('A.*, group_concat(B.title SEPARATOR \', \') as title');
		$dbGetList->Run();

		BH::APP()->_View($this->model, $dbGetList);
	}
	public function View(){
		$res = $this->model->DBGet($_GET['bid']);

		$dbGet = BH::DBList(TABLE_MENU);
		$dbGet->AddWhere('type=\'content\'');
		$dbGet->AddWhere('bid='.SetDBText($this->model->GetValue('bid')));
		App::$_Value['selectedMenu'] = $dbGet->GetRows();

		if(!$res->result){
			Redirect('-1', $res->message);
		}

		BH::APP()->_View($this->model);
	}
	public function Write(){
		$dbGetList = BH::DBList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		App::$_Value['menu'] = $dbGetList->GetRows();
		BH::APP()->_View($this->model);
	}
	public function Modify(){
		$dbGetList = BH::DBList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		App::$_Value['menu'] = $dbGetList->GetRows();

		$res = $this->model->DBGet($_GET['bid']);
		$dbGet = BH::DBList(TABLE_MENU);
		$dbGet->AddWhere('type=\'content\'');
		$dbGet->AddWhere('bid='.SetDBText($this->model->GetValue('bid')));
		App::$_Value['selectedMenu'] = $dbGet->GetRows();

		if(!$res->result){
			Redirect('-1', $res->message);
		}
		BH::APP()->Html = 'Write';
		BH::APP()->_View($this->model);
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
				BH::CF()->MenuConnect($this->model->GetValue('bid'), 'content');
				Redirect(BH::APP()->URLAction());
			}else{
				Redirect('-1', '등록에 실패했습니다.');
			}
		}
	}
	public function PostModify(){
		$res = $this->model->DBGet($_GET['bid']);
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
				BH::CF()->MenuConnect($this->model->GetValue('bid'), 'content');
				$url = BH::APP()->URLAction('View').'?bid='.$_POST['bid'].BH::APP()->GetFollowQuery();
				Redirect($url, '수정완료');
			}else{
				Redirect('-1', '수정에 실패했습니다.');
			}
		}
	}

	public function GetSubMenu(){
		$dbGetList = BH::DBList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '.(strlen(BH::APP()->ID) + _CATEGORY_LENGTH));
		$dbGetList->AddWhere('LEFT(category, '.strlen(BH::APP()->ID).') = '.SetDBText(BH::APP()->ID));
		JSON(true, '', $dbGetList->GetRows());
	}

	public function PostDelete(){
		$res = $this->model->DBDelete($_POST['bid']);
		if($res->result) Redirect(BH::APP()->URLAction('').BH::APP()->GetFollowQuery());
		else Redirect('-1', '삭제에 실패했습니다.');
	}
}