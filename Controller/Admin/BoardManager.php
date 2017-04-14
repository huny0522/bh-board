<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Admin;

class BoardManagerController extends \BH_Controller{

	/**
	 * @var BoardManagerModel
	 */
	public $model = NULL;

	public function __Init(){
		$this->_Value['NowMenu'] = '002';
		$this->_CF->AdminAuth();

		require _DIR.'/Model/BoardManager.model.php';
		$this->model = new \BoardManagerModel();

		$AdminAuth = explode(',', $this->_CF->GetMember('admin_auth'));
		$this->_Value['menuAuth'] = (in_array('004', $AdminAuth) || $_SESSION['member']['level'] == _SADMIN_LEVEL);

		// HTMl 생성
		// 디버그 모드일때 Index, Write, View 파일을 자동 생성(파일이 존재하지 않을 경우)
		// require _COMMONDIR . '/BH_HtmlCreate.class.php';
		// BH_HtmlCreate::Create('BoardManager', 'BoardManager');

		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		$this->SetFollowQuery(array('where', 'keyword','page'));
		$this->Layout = '_Admin';
	}

	public function Index(){

		// 리스트를 불러온다.
		$dbGetList = new \BH_DB_GetListWithPage($this->model->table.' A LEFT JOIN '.TABLE_MENU.' B ON A.bid = B.bid AND B.type=\'board\'');
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = $this->URLAction('').$this->GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->group = 'A.bid';
		$dbGetList->SetKey('A.*, group_concat(B.title SEPARATOR \', \') as title');
		$dbGetList->Run();

		$this->_View($this->model, $dbGetList);
	}
	public function View(){
		$res = $this->model->DBGet($_GET['bid']);

		$dbGet = new \BH_DB_GetList(TABLE_MENU);
		$dbGet->AddWhere('type=\'board\'');
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
		$dbGet->AddWhere('type=\'board\'');
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
			$res = $this->model->DBInsert();
			if($res->result){
				$r1 = $this->model->CreateTableBoard(TABLE_FIRST.'bbs_'.$this->model->GetValue('bid'));
				if($r1){
					$r2 = $this->model->CreateTableReply(TABLE_FIRST.'bbs_'.$this->model->GetValue('bid').'_reply');
					if($r2){
						$r3 = $this->model->CreateTableImg(TABLE_FIRST.'bbs_'.$this->model->GetValue('bid').'_images');
						if($r3){
							$this->_CF->MenuConnect($this->model->GetValue('bid'), 'board');
						}
					}
				}
				Redirect($this->URLAction().$this->GetFollowQuery());
			}else{
				Redirect($this->URLAction().$this->GetFollowQuery(), 'ERROR');
			}
		}
	}

	public function PostModify(){
		$res = $this->model->DBGet($_POST['bid']);
		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			$res = $this->model->DBUpdate();
			if($res->result){
				$this->_CF->MenuConnect($this->model->GetValue('bid'), 'board');
				$url = $this->URLAction('View').'?bid='.$_POST['bid'].$this->GetFollowQuery();
				Redirect($url, '수정완료');
			}else{
				Redirect('-1', 'ERROR');
			}
		}
	}

	public function PostDelete(){
		if(isset($_POST['bid']) && $_POST['bid'] != ''){
			$res = $this->model->DBDelete($_POST['bid']);
			if($res->result){
				$board_nm = TABLE_FIRST.'bbs_'.$_POST['bid'];

				@Sqlquery("DROP TABLE `{$board_nm}`");
				@Sqlquery("DROP TABLE `{$board_nm}_reply`");
				@Sqlquery("DROP TABLE `{$board_nm}_images`");

				Redirect($this->URLAction('').$this->GetFollowQuery(), '삭제되었습니다.');
			}else{
				Redirect('-1', $res->message);
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