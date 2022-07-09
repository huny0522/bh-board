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
	public $model;
	public $possibleActionType = array('recommend', 'oppose', 'scrap');

	public function __construct(){
		$this->model = new \ContentModel();
	}

	public function __init(){
	}

	public function Index(){
		App::JSAdd('content.view.js');
		$res = $this->model->DBGet(App::$tid);
		if(!$res->result){
			URLReplace('-1', 'ERROR');
		}

		$this->_LayoutSet();

		$html = $this->model->GetValue('html');
		if($html){
			if(substr($html, -5) != '.html') $html .= '.html';
			$htmlPath = App::$nativeSkinDir.'/'.$html;
			if(file_exists(\Paths::DirOfSkin().'/Contents/'.$htmlPath)) $html = $htmlPath;
			App::$html = '/Contents/'.$html;
		}

		if(!file_exists(\Paths::DirOfSkin() . App::$html)) URLReplace(\Paths::Url() . '/', \BHG::$isDeveloper === true ? 'Contents files does not exist.(' . App::$html . ')' : '');

		$cookieName = $this->model->table.$this->model->GetValue('bid');
		if(!isset($_COOKIE[$cookieName]) || !$_COOKIE[$cookieName]){
			$dbUpdate = new \BH_DB_Update($this->model->table);
			$dbUpdate->SetData('hit', '`hit` + 1');
			$dbUpdate->AddWhere('bid = %s', $this->model->GetValue('bid'));
			$dbUpdate->Run();
			setcookie($cookieName, 'y');
		}

		if(_JSONIS === true) JSON(true, '', App::GetView());

		App::$data['contentActionData'] = array();
		if(\BHG::$isMember === true){
			App::$data['contentActionData'] = $this->model->GetMyActions($this->model->GetValue('bid'), \BHG::$session->member->muid->Get());
		}

		App::$data['recommendButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$tid) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$tid) . '" data-type="recommend" class="contentActionBtn contentRecommendActionBtn' .(isset(App::$data['contentActionData']['recommend']) ? ' already' : ''). '"><b>' . App::$lang['RECOMMEND'] . '</b> <span class="num">' . ($this->model->_recommend->Txt()) . '</span></a>';

		App::$data['scrapButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$tid) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$tid) . '" data-type="scrap" class="contentActionBtn contentSubscribeActionBtn' .(isset(App::$data['contentActionData']['scrap']) ? ' already' : ''). '"><b>' . App::$lang['SCRAP'] . '</b> <span class="num">' . ($this->model->_scrap->Txt()) . '</span></a>';

		App::$data['opposeButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$tid) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$tid) . '" data-type="oppose" class="contentActionBtn contentOpposeActionBtn' .(isset(App::$data['contentActionData']['oppose']) ? ' already' : ''). '"><b>' . App::$lang['OPPOSE'] . '</b> <span class="num">' . ($this->model->_oppose->Txt()) . '</span></a>';

		$this->model->_ReadAction($this->model->_bid->Txt());

		App::View();

	}

	public function _DirectView(){

		$html = App::$id;
		if($html){
			if(substr($html, -5) != '.html') $html .= '.html';
			$htmlPath = App::$nativeSkinDir.'/'.$html;
			if(file_exists(\Paths::DirOfSkin().'/Contents/'.$htmlPath)) $html = $htmlPath;
			App::$html = '/Contents/'.$html;
		}

		if(!file_exists(\Paths::DirOfSkin() . App::$html)) URLReplace(\Paths::Url() . '/', \BHG::$isDeveloper === true ? 'Contents files does not exist.(' . App::$html . ')' : '');

		$this->_LayoutSet();

		App::View();
	}

	public function _LayoutSet(){
		$layout = $this->model->GetValue('layout');
		if($layout){
			$layoutPath = App::$nativeSkinDir.'/'.$layout;
			$e = explode('.', $layoutPath);
			if(sizeof($e) > 1){
				$ext = array_pop($e);
				if($ext !== 'html' && $ext !== 'php') $layoutPath = implode('.', $e) . '.html';
			}
			else{
				$layoutPath .= '.html';
			}

			if(file_exists(\Paths::DirOfSkin().'/Layout/'.$layoutPath)) $layout = $layoutPath;
			App::$layout = $layout;
		}
	}

	public function PostJSONAction(){
		$type = Post('type');
		$this->_ActionCheck($type);

		$res = $this->model->ActionDuplicationCheck($type, App::$id);
		if($res) JSON(false, '', $res['action_type']);

		$res = $this->model->InsertAction($type, App::$id);
		if($res->result) JSON(true);
		else JSON(false, $res->message ? $res->message : App::$lang['ERROR_INSERT']);
	}

	public function PostJSONCancelAction(){
		$type = Post('type');
		$this->_ActionCheck($type);

		$res = $this->model->DeleteAction($type, App::$id);
		if($res->result) JSON(true);
		else JSON(false, $res->message ? $res->message : App::$lang['ERROR_DELETE']);
	}

	protected function _ActionCheck($type){
		if(\BHG::$isMember !== true) JSON(false, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);

		if(!in_array($type, $this->possibleActionType)) JSON(false, App::$lang['MSG_WRONG_CONNECTED']);

		$res = $this->model->DBGet(App::$id);
		if(!$res->result) JSON(false, App::$lang['MSG_WRONG_CONNECTED']);
	}
}
