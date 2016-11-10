<?php
/**
 * Bang Hun.
 * 16.07.10
 */

require _DIR.'/Model/Member.model.php';

class SampleController extends BH_Controller{

	/**
	 * @var MemberModel
	 */
	public $model = null;

	public function __Init(){

		$this->model = new MemberModel();
		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		$this->SetFollowQuery(array('where', 'keyword','page'));
	}

	public function Index(){
		if(1 && _DEVELOPERIS === true){
			// 디버그 모드일때 Index, Write, View 파일을 자동 생성(파일이 존재하지 않을 경우)
			// 삭제해도 무방함.
			require _CLASSDIR.'/BH_HtmlCreate.class.php';
			BH_HtmlCreate::Create('Sample', 'Member');
		}

		// 리스트를 불러온다.
		$dbGetList = new BH_DB_GetListWithPage($this->model->table);
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = $this->URLAction($this->Action).$this->GetFollowQuery('page');
		$dbGetList->articleCount = 1;
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
}