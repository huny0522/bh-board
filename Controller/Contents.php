<?php
/**
 * Bang Hun.
 * 16.07.10
 */

require _DIR.'/Model/Content.model.php';
use \BH_Application as App;
use \BH as BH;
class ContentsController{
	/**
	 * @var ContentModel
	 */
	public $model = null;
	public function __construct(){
		$this->model = new \ContentModel();
	}

	public function Index(){
		$res = $this->model->DBGet(BH::APP()->TID);
		if(!$res->result){
			Redirect('-1', 'ERROR');
		}
		if($this->model->GetValue('layout')) BH::APP()->Layout = $this->model->GetValue('layout');
		if($this->model->GetValue('html')) BH::APP()->Html = $this->model->GetValue('html');

		$cookieName = $this->model->table.$this->model->GetValue('bid');
		if(!isset($_COOKIE[$cookieName]) || !$_COOKIE[$cookieName]){
			$dbUpdate = BH::DBUpdate($this->model->table);
			$dbUpdate->SetData('hit', 'hit + 1');
			$dbUpdate->AddWhere('bid='.SetDBText($this->model->GetValue('bid')));
			$dbUpdate->Run();
			setcookie($cookieName, 'y');
		}

		BH::APP()->_View();

	}
}
