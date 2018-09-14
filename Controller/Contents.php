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
		$res = $this->model->DBGet(App::$TID);
		if(!$res->result){
			URLReplace('-1', 'ERROR');
		}

		$this->_LayoutSet();

		$html = $this->model->GetValue('html');
		if($html){
			if(substr($html, -5) != '.html') $html .= '.html';
			$htmlPath = App::$NativeSkinDir.'/'.$html;
			if(file_exists(_SKINDIR.'/Contents/'.$htmlPath)) $html = $htmlPath;
			App::$Html = '/Contents/'.$html;
		}

		if(!file_exists(_SKINDIR . App::$Html)) URLReplace(_URL . '/', _DEVELOPERIS === true ? '컨텐츠 파일이 없습니다.(' . App::$Html . ')' : '');

		$cookieName = $this->model->table.$this->model->GetValue('bid');
		if(!isset($_COOKIE[$cookieName]) || !$_COOKIE[$cookieName]){
			$dbUpdate = new \BH_DB_Update($this->model->table);
			$dbUpdate->SetData('hit', '`hit` + 1');
			$dbUpdate->AddWhere('bid = %s', $this->model->GetValue('bid'));
			$dbUpdate->Run();
			setcookie($cookieName, 'y');
		}

		if(_JSONIS === true) JSON(true, '', App::GetView());

		App::$Data['contentActionData'] = array();
		if(_MEMBERIS === true){
			App::$Data['contentActionData'] = $this->model->GetMyActions($this->model->GetValue('bid'), $_SESSION['member']['muid']);
		}

		App::$Data['recommendButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$TID) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$TID) . '" data-type="recommend" class="contentActionBtn contentRecommendActionBtn' .(isset(App::$Data['contentActionData']['recommend']) ? ' already' : ''). '"><b>추천</b> <span class="num">' . ($this->model->_recommend->txt()) . '</span></a>';

		App::$Data['scrapButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$TID) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$TID) . '" data-type="scrap" class="contentActionBtn contentSubscribeActionBtn' .(isset(App::$Data['contentActionData']['scrap']) ? ' already' : ''). '"><b>스크랩</b> <span class="num">' . ($this->model->_scrap->txt()) . '</span></a>';

		App::$Data['opposeButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$TID) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$TID) . '" data-type="oppose" class="contentActionBtn contentOpposeActionBtn' .(isset(App::$Data['contentActionData']['oppose']) ? ' already' : ''). '"><b>반대</b> <span class="num">' . ($this->model->_oppose->txt()) . '</span></a>';

		$this->model->_ReadAction($this->model->_bid->txt());

		App::View();

	}

	public function _DirectView(){

		$html = App::$ID;
		if($html){
			if(substr($html, -5) != '.html') $html .= '.html';
			$htmlPath = App::$NativeSkinDir.'/'.$html;
			if(file_exists(_SKINDIR.'/Contents/'.$htmlPath)) $html = $htmlPath;
			App::$Html = '/Contents/'.$html;
		}

		if(!file_exists(_SKINDIR . App::$Html)) URLReplace(_URL . '/', _DEVELOPERIS === true ? '컨텐츠 파일이 없습니다.(' . App::$Html . ')' : '');

		$this->_LayoutSet();

		App::View();
	}

	public function _LayoutSet(){
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
	}

	public function PostJSONAction(){
		$type = Post('type');
		$this->_ActionCheck($type);

		$res = $this->model->ActionDuplicationCheck($type, App::$ID);
		if($res) JSON(false, '', $res['action_type']);

		$res = $this->model->InsertAction($type, App::$ID);
		if($res->result) JSON(true);
		else JSON(false, $res->message ? $res->message : '삽입오류');
	}

	public function PostJSONCancelAction(){
		$type = Post('type');
		$this->_ActionCheck($type);

		$res = $this->model->DeleteAction($type, App::$ID);
		if($res->result) JSON(true);
		else JSON(false, $res->message ? $res->message : '삭제 오류');
	}

	protected function _ActionCheck($type){
		if(_MEMBERIS !== true) JSON(false, _MSG_NEED_LOGIN);

		if(!in_array($type, $this->possibleActionType)) JSON(false, _MSG_WRONG_CONNECTED);

		$res = $this->model->DBGet(App::$ID);
		if(!$res->result) JSON(false, _MSG_WRONG_CONNECTED);
	}
}
