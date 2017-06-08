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
		$this->model = App::GetModel('Content');
	}

	public function __init(){
	}

	public function Index(){
		$res = $this->model->DBGet(App::$TID);
		if(!$res->result){
			Redirect('-1', 'ERROR');
		}

		$layout = $this->model->GetValue('layout');
		if($layout){
			$layoutPath = App::$NativeDir.'/'.$layout;
			if(file_exists(_SKINDIR.'/Layout/'.$layoutPath.'.html')) $layout = $layoutPath;
			App::$Layout = $layout;
		}

		$html = $this->model->GetValue('html');
		if($html){
			if(substr($html, -5) != '.html') $html .= '.html';
			$htmlPath = App::$NativeDir.'/'.$html;
			if(file_exists(_SKINDIR.'/Contents/'.$htmlPath)) $html = $htmlPath;
			App::$Html = '/Contents/'.$html;
		}

		$cookieName = $this->model->table.$this->model->GetValue('bid');
		if(!isset($_COOKIE[$cookieName]) || !$_COOKIE[$cookieName]){
			$dbUpdate = new \BH_DB_Update($this->model->table);
			$dbUpdate->SetData('hit', 'hit + 1');
			$dbUpdate->AddWhere('bid='.SetDBText($this->model->GetValue('bid')));
			$dbUpdate->Run();
			setcookie($cookieName, 'y');
		}

		if(_JSONIS === true) JSON(true, '', App::GetView($this));

		App::View($this);

	}
}
