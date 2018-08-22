<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class Contents{
	/**
	 * @var ContentModel
	 */
	public $model = null;

	public function __construct(){
		$this->model = App::InitModel('Content');
	}

	public function __init(){
	}

	public function Index(){
		$res = $this->model->DBGet(App::$TID);
		if(!$res->result){
			URLReplace('-1', 'ERROR');
		}

		$layout = $this->model->GetValue('layout');
		if($layout){
			$layoutPath = App::$NativeSkinDir.'/'.$layout;
			$e = explode('.', $layoutPath);
			if(sizeof($e) > 1){
				$ext = array_pop($e);
				if($ext !== 'html' && $ext !== 'php') $layoutPath = implode('.', $e) . '.html';
			}
			else{
				$layoutPath .= '.html';
			}

			if(file_exists(_SKINDIR.'/Layout/'.$layoutPath)) $layout = $layoutPath;
			App::$Layout = $layout;
		}

		$html = $this->model->GetValue('html');
		if($html){
			if(substr($html, -5) != '.html') $html .= '.html';
			$htmlPath = App::$NativeSkinDir.'/'.$html;
			if(file_exists(_SKINDIR.'/Contents/'.$htmlPath)) $html = $htmlPath;
			App::$Html = '/Contents/'.$html;
		}

		$cookieName = $this->model->table.$this->model->GetValue('bid');
		if(!isset($_COOKIE[$cookieName]) || !$_COOKIE[$cookieName]){
			$dbUpdate = new \BH_DB_Update($this->model->table);
			$dbUpdate->SetData('hit', 'hit + 1');
			$dbUpdate->AddWhere('bid = %s'.$this->model->GetValue('bid'));
			$dbUpdate->Run();
			setcookie($cookieName, 'y');
		}

		if(_JSONIS === true) JSON(true, '', App::GetView());

		App::View();

	}
}
