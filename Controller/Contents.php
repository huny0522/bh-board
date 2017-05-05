<?php
/**
 * Bang Hun.
 * 16.07.10
 */

use \BH_Application as App;
use \BH_Common as CF;

class ContentsController{
	/**
	 * @var ContentModel
	 */
	public $model = null;

	public function __construct(){
		require _DIR.'/Model/Content.model.php';
		$this->model = new \ContentModel();
	}

	public function __init(){
	}

	public function Index(){
		$res = $this->model->DBGet(App::$TID);
		if(!$res->result){
			Redirect('-1', 'ERROR');
		}
		if($this->model->GetValue('layout')) App::$Layout = $this->model->GetValue('layout');
		if($this->model->GetValue('html')) App::$Html = $this->model->GetValue('html');

		$cookieName = $this->model->table.$this->model->GetValue('bid');
		if(!isset($_COOKIE[$cookieName]) || !$_COOKIE[$cookieName]){
			$dbUpdate = new \BH_DB_Update($this->model->table);
			$dbUpdate->SetData('hit', 'hit + 1');
			$dbUpdate->AddWhere('bid='.SetDBText($this->model->GetValue('bid')));
			$dbUpdate->Run();
			setcookie($cookieName, 'y');
		}

		App::_View($this);

	}
}
