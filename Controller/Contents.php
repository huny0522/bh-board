<?php
/**
 * Bang Hun.
 * 16.07.10
 */

require _DIR.'/Model/Content.model.php';

class ContentsController extends \BH_Controller{
	/**
	 * @var ContentModel
	 */
	public $model = null;
	public function __Init(){
		$this->model = new \ContentModel();
	}

	public function Index(){
		$res = $this->model->DBGet($this->TID);
		if(!$res->result){
			Redirect('-1', 'ERROR');
		}
		if($this->model->GetValue('layout')) $this->Layout = $this->model->GetValue('layout');
		if($this->model->GetValue('html')) $this->Html = $this->model->GetValue('html');

		$cookieName = $this->model->table.$this->model->GetValue('bid');
		if(!isset($_COOKIE[$cookieName]) || !$_COOKIE[$cookieName]){
			$dbUpdate = new \BH_DB_Update($this->model->table);
			$dbUpdate->SetData('hit', 'hit + 1');
			$dbUpdate->AddWhere('bid='.SetDBText($this->model->GetValue('bid')));
			$dbUpdate->Run();
			setcookie($cookieName, 'y');
		}

		$this->_View();

	}
}
