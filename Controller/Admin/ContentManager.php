<?php
/**
 * Bang Hun.
 * 16.07.10
 */

require _DIR.'/Model/Content.model.php';

class ContentManagerController extends BH_Controller{

	public $model;

	public function __Init(){
		$this->_Value['NowMenu'] = '003';
		$this->_CF->AdminAuth();

		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		$this->SetFollowQuery(array('where', 'keyword','page'));
		$this->Layout = '_Admin';
		$this->model = new ContentModel();
	}

	public function Index(){
		// 리스트를 불러온다.
		$dbGetList = new BH_DB_GetListWithPage($this->model->table);
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = $this->URLAction('').$this->GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->sort = 'reg_date DESC';
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
			$this->model->SetValue('reg_date',date('Y-m-d H:i:s'));
			$res = $this->model->DBInsert();
			if($res->result){
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
				$url = $this->URLAction('View').'?bid='.$_POST['bid'].$this->GetFollowQuery();
				Redirect($url, '수정완료');
			}else{
				Redirect('-1', '수정에 실패했습니다.');
			}
		}
	}
}