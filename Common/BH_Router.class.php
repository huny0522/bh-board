<?php
/**
 * Bang Hun.
 * 16.07.10
 */

class BH_Router
{
	public $Member;
	public $MainMenu = array();
	public $SubMenu = array();
	public $ActiveMenu = array();
	public $RootC = '';
	public $GetUrl = array();
	public function __construct(){
		if($GLOBALS['_BH_App']->InstallIs) $this->SetMenu();
		$this->GetUrl = explode('/', isset($_GET['url']) ? $_GET['url'] : '');
		for($i = 0; $i < 10; $i++){
			if(!isset($this->GetUrl[$i])) $this->GetUrl[$i] = '';
		}
	}

	public function router(){
		global $_BH_App;

		if($this->GetUrl[1] == 'MyIp'){
			echo $_SERVER['REMOTE_ADDR'];
			exit;
		}

		if($this->GetUrl[1] == '~Create'){
			if(_DEVELOPERIS === true && _POSTIS === true){
				if(!isset($_POST['const']) || $_POST['const'] != 'y') $_POST['table_name'] = "'{$_POST['table_name']}'";
				BH_HtmlCreate::CreateController($_POST['controller_name'], $_POST['model_name'], $_POST['table_name']);
			}
			exit;
		}

		if(!isset($this->GetUrl[1]) || !strlen($this->GetUrl[1])){
			$this->GetUrl[1] = _DEFAULT_CONTROLLER;
		}

		//----------------------
		// 메뉴
		//----------------------
		$_BH_App->BaseDir = _URL;
		if($GLOBALS['_BH_App']->InstallIs){
			switch($this->GetUrl[1]){
				case _ADMINURLNAME: // 관리자
					$_BH_App->SubDir = 'Admin';
					$_BH_App->Controller = $this->GetUrl[2];
					$_BH_App->Action = $this->GetUrl[3];
					$_BH_App->ID = $this->GetUrl[4];
					$_BH_App->BaseDir .= '/'.$this->GetUrl[1];
					$_BH_App->CtrlUrl = _URL.'/'.$this->GetUrl[1].'/'.$_BH_App->Controller;
				break;

				case 'Board': // 게시판
				case 'Contents': // Contents
				case 'Reply': // 댓글
					$_BH_App->Controller = $this->GetUrl[1];
					$_BH_App->TID = $this->GetUrl[2];
					$_BH_App->Action = $this->GetUrl[3];
					$_BH_App->ID = $this->GetUrl[4];
					$_BH_App->CtrlUrl = _URL.'/'.$_BH_App->Controller.'/'.$_BH_App->TID;
				break;

				default:

					if(!$this->SetMenuRouter(_URL)){
						$_BH_App->Controller = $this->GetUrl[1];
						$_BH_App->Action = $this->GetUrl[2];
						$_BH_App->ID = $this->GetUrl[3];
						$_BH_App->CtrlUrl = _URL.'/'.$_BH_App->Controller;
					}
				break;
			}
		}else{
			if($this->GetUrl[1] == 'Install'){
				$_BH_App->Controller = $this->GetUrl[1];
				$_BH_App->Action = $this->GetUrl[2];
				$_BH_App->ID = $this->GetUrl[3];
				$_BH_App->CtrlUrl = _URL.'/'.$_BH_App->Controller;
			}else{
				exit;
			}
		}

	}

	public function SetMenu($Title = 'Home'){
		if(sizeof($this->MainMenu)) return;

		BH_Category::_SetFile(TABLE_MENU);

		$Menu = BH_Category::_GetRoot(TABLE_MENU, $Title);
		if($Menu){
			$this->RootC = $Menu['category'];
			$menu = BH_Category::_GetSub(TABLE_MENU, $this->RootC);
			foreach($menu as $row){
				$this->MainMenu[] = $row;
				$sub_menu = BH_Category::_GetSub(TABLE_MENU, $row['category']);
				foreach($sub_menu as $row2){
					$this->SubMenu[$row['category']][] = $row2;
				}
			}
		}
	}

	public function SetMenuRouter($url, $start = 1){
		global $_BH_App;
		$cont = $this->GetUrl[$start];
		if(!$cont) $cont = _DEFAULT_CONTROLLER;

		$find = 0;
		foreach($_BH_App->_Category[TABLE_MENU] as $k => $v){
			if($this->RootC != substr($k, 0, strlen($this->RootC)) && $k != BH_Category::ROOT_CATEGORY_CODE) continue;
			foreach($v as $k2=> $row){
				if($this->RootC != substr($k2, 0, strlen($this->RootC))) continue;
				if($row['controller'] == $cont){
					if($row['parent_enabled'] == 'y' && $row['enabled'] == 'y' && (!sizeof($this->ActiveMenu) || strlen($this->ActiveMenu['category']) < strlen($row['category']))) $this->ActiveMenu = $row;
					$find++;
				}
			}
		}

		if($find && !sizeof($this->ActiveMenu)){
			if(_DEVELOPERIS === true) Redirect(-1, '사용중지된 메뉴입니다.');
			Redirect(-1);
		}

		if($this->ActiveMenu){
			if($this->ActiveMenu['type'] == 'board') $_BH_App->Controller = 'Board';
			else if($this->ActiveMenu['type'] == 'content') $_BH_App->Controller = 'Contents';
			else $_BH_App->Controller = $this->GetUrl[$start] ? $this->GetUrl[$start] : _DEFAULT_CONTROLLER;

			$_BH_App->TID = $this->ActiveMenu['bid'];
			$_BH_App->Action = $this->GetUrl[$start + 1];
			$_BH_App->ID = $this->GetUrl[$start + 2];
			$_BH_App->CtrlUrl = $url.'/'.$this->GetUrl[$start];
			return true;
		}
		return false;
	}

}