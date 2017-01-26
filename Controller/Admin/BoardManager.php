<?php
/**
 * Bang Hun.
 * 16.07.10
 */

require _DIR.'/Model/BoardManager.model.php';

class BoardManagerController extends BH_Controller{

	/**
	 * @var BoardManagerModel
	 */
	public $model = NULL;

	public function __Init(){
		$this->_Value['NowMenu'] = '002';
		$this->_CF->AdminAuth();

		$this->model = new BoardManagerModel();

		// HTMl 생성
		// 디버그 모드일때 Index, Write, View 파일을 자동 생성(파일이 존재하지 않을 경우)
		// require _CLASSDIR . '/BH_HtmlCreate.class.php';
		// BH_HtmlCreate::Create('BoardManager', 'BoardManager');

		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		$this->SetFollowQuery(array('where', 'keyword','page'));
		$this->Layout = '_Admin';
	}

	public function Index(){

		// 리스트를 불러온다.
		$dbGetList = new BH_DB_GetListWithPage($this->model->table);
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = $this->URLAction('').$this->GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->Run();

		$this->_View($this->model, $dbGetList);
	}
	public function View(){
		$res = $this->model->DBGet($_GET['bid']);

		if(!$res->result){
			Redirect('-1', $res->message);
		}

		$this->_View($this->model);
	}
	public function Write(){
		$this->_View($this->model);
	}
	public function Modify(){
		$res = $this->model->DBGet($_GET['bid']);

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
}