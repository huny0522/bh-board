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

	public function SetMenu(){
		if(sizeof($this->MainMenu)) return;
		$Menu = $this->GetRootMenu('Home');
		if($Menu){
			$this->RootC = $Menu['category'];
			$menu = $this->GetSubMenu($this->RootC);
			foreach($menu as $row){
				if(strlen($row['category']) == strlen($this->RootC) + _CATEGORY_LENGTH) $this->MainMenu[] = $row;
				else $this->SubMenu[substr($row['category'], 0, strlen($row['category']) - _CATEGORY_LENGTH)][] = $row;
			}
		}
	}


	/**
	 * @param string $title
	 * @return array|bool|null
	 */
	public function GetRootMenu($title = ''){
		$dbGet = new BH_DB_Get(TABLE_MENU);
		$dbGet->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		$dbGet->AddWhere('enabled = \'y\'');
		$dbGet->AddWhere('parent_enabled = \'y\'');
		if($title) $dbGet->AddWhere('controller='.SetDBText($title));
		return $dbGet->Get();
	}

	public function GetSubMenu($key){
		$dbGetList = new BH_DB_GetList(TABLE_MENU);

		$dbGetList->AddWhere('LEFT(category,'.strlen($key).') ='. SetDBText($key));
		$dbGetList->AddWhere('LENGTH(category) IN ('. (strlen($key) + _CATEGORY_LENGTH).','. (strlen($key) + _CATEGORY_LENGTH + _CATEGORY_LENGTH).')');

		$dbGetList->AddWhere('enabled = \'y\'');
		$dbGetList->AddWhere('parent_enabled = \'y\'');
		$dbGetList->sort = 'sort';
		$menu = array();
		while($row = $dbGetList->Get()){
			$menu[$row['category']] = $row;
		}
		return $menu;
	}

	public function SetMenuRouter($url, $start = 1){
		global $_BH_App;
		$cont = $this->GetUrl[$start];
		if(!$cont) $cont = _DEFAULT_CONTROLLER;

		$sql = 'SELECT * FROM '.TABLE_MENU.' WHERE controller = '.SetDBText($cont).' AND LEFT(category, '.strlen($this->RootC).') = '.SetDBText($this->RootC).' ORDER BY LENGTH(category) DESC';
		$this->ActiveMenu = SqlFetch($sql);

		if($this->ActiveMenu){
			if($this->ActiveMenu['parent_enabled'] != 'y' || $this->ActiveMenu['enabled'] != 'y'){
				exit;
			}
		}


		if($this->ActiveMenu){
			if($this->ActiveMenu['type'] == 'board') $_BH_App->Controller = 'Board';
			else if($this->ActiveMenu['type'] == 'content') $_BH_App->Controller = 'Contents';
			else $_BH_App->Controller = $this->GetUrl[$start];

			$_BH_App->TID = $this->ActiveMenu['bid'];
			$_BH_App->Action = $this->GetUrl[$start + 1];
			$_BH_App->ID = $this->GetUrl[$start + 2];
			$_BH_App->CtrlUrl = $url.'/'.$this->GetUrl[$start];
			return true;
		}
		return false;
	}

}