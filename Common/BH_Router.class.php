<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CF;
use \BH_Application as App;

class BH_Router
{
	public $Member;
	public $MainMenu = array();
	public $SubMenu = array();
	public $ActiveMenu = array();
	public $RootC = '';
	public $GetUrl = array();
	public $Layout = '';
	public $AdminMenu = array();

	public function __construct(){
		if(App::$Instance->InstallIs) $this->SetMenu();
		$this->GetUrl = explode('/', isset($_GET['_bh_url']) ? $_GET['_bh_url'] : '');
		for($i = 0; $i < 10; $i++) if(!isset($this->GetUrl[$i])) $this->GetUrl[$i] = '';
	}

	public function router(){
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

		if($this->GetUrl[1] == '_Refresh'){
			if(_DEVELOPERIS === true){
				$s = BH_Common::Config('Default', 'Refresh');
				$res = BH_Common::SetConfig('Default', 'Refresh', $s+1);

				if($res->result){
					if(_REFRESH_HTML_ALL === true){
						delTree(_HTMLDIR);
						ReplaceHTMLAll(_SKINDIR, _HTMLDIR);
						ReplaceCSS2ALL(_HTMLDIR, _HTMLDIR);
						ReplaceCSS2ALL(_SKINDIR, _HTMLDIR);
					}
					if(_REFRESH_DB_CACHE_ALL === true) delTree(_DATADIR.'/temp');
					Redirect($_GET['r_url']);
				}
				else Redirect(-1, $res->message);
			}
			exit;
		}

		if(!isset($this->GetUrl[1]) || !strlen($this->GetUrl[1])) $this->GetUrl[1] = _DEFAULT_CONTROLLER;

		//----------------------
		// 메뉴
		//----------------------
		App::$Instance->BaseDir = _URL;
		App::$Instance->NativeDir = '';
		if(App::$Instance->InstallIs){
			switch($this->GetUrl[1]){
				case _ADMINURLNAME: // 관리자
					App::$Instance->NativeDir = 'Admin';
					App::$Instance->ControllerName = $this->GetUrl[2];
					App::$Instance->Action = $this->GetUrl[3];
					App::$Instance->ID = $this->GetUrl[4];
					App::$Instance->BaseDir .= '/'.$this->GetUrl[1];
					App::$Instance->CtrlUrl = _URL.'/'.$this->GetUrl[1].'/'.App::$Instance->ControllerName;

					$this->AdminMenu = array(
						'001' => array(
							'Category' => 'Config',
							'Name' => '사이트관리'
						),
						'001001' => array(
							'Category' => 'Config',
							'Name' => '환경설정'
						),
						'001002' => array(
							'Category' => 'BannerManager',
							'Name' => '배너관리'
						),
						'001003' => array(
							'Category' => 'PopupManager',
							'Name' => '팝업관리'
						),
						'002' => array(
							'Category' => 'BoardManager',
							'Name' => '게시판관리'
						),
						'003' => array(
							'Category' => 'ContentManager',
							'Name' => '컨텐츠관리'
						),
						'004' => array(
							'Category' => 'MenuManager',
							'Name' => '메뉴관리'
						),
						'005' => array(
							'Category' => 'Member',
							'Name' => '회원관리'
						)
					);
				break;

				case 'Board': // 게시판
				case 'Contents': // Contents
				case 'Reply': // 댓글
				App::$Instance->ControllerName = $this->GetUrl[1];
				App::$Instance->TID = $this->GetUrl[2];
				App::$Instance->Action = $this->GetUrl[3];
				App::$Instance->ID = $this->GetUrl[4];
				App::$Instance->CtrlUrl = _URL.'/'.App::$Instance->ControllerName.'/'.App::$Instance->TID;
				if($this->GetUrl[1] == 'Board') $this->Layout = '_Board';
				break;

				default:
					if(!$this->SetMenuRouter(_URL)){
						App::$Instance->ControllerName = $this->GetUrl[1];
						App::$Instance->Action = $this->GetUrl[2];
						App::$Instance->ID = $this->GetUrl[3];
						App::$Instance->CtrlUrl = _URL.'/'.App::$Instance->ControllerName;
					}
				break;
			}
		}else{
			if($this->GetUrl[1] == 'Install'){
				App::$Instance->ControllerName = $this->GetUrl[1];
				App::$Instance->Action = $this->GetUrl[2];
				App::$Instance->ID = $this->GetUrl[3];
				App::$Instance->CtrlUrl = _URL.'/'.App::$Instance->ControllerName;
			}
			else exit;
		}

		if(App::$Instance->ControllerName != 'Mypage'){
			$_SESSION['MyInfoView'] = false;
			unset($_SESSION['MyInfoView']);
		}
	}

	public function SetMenu($Title = 'Home'){
		if(sizeof($this->MainMenu)) return;
		$Menu = $this->GetRootMenu($Title);
		if($Menu){
			$this->RootC = $Menu['category'];
			$menu = $this->GetSubMenu($this->RootC);
			foreach($menu as $row){
				if(strlen($row['category']) == strlen($this->RootC) + _CATEGORY_LENGTH) $this->MainMenu[] = $row;
				else $this->SubMenu[substr($row['category'], 0, strlen($row['category']) - _CATEGORY_LENGTH)][] = $row;
			}
		}
	}

	public function GetRootMenu($title = ''){
		$dbGet = new \BH_DB_Get(TABLE_MENU);
		$dbGet->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		$dbGet->AddWhere('enabled = \'y\'');
		$dbGet->AddWhere('parent_enabled = \'y\'');
		if($title) $dbGet->AddWhere('controller='.SetDBText($title));
		return $dbGet->Get();
	}

	public function GetSubMenu($key){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LEFT(category,'.strlen($key).') ='. SetDBText($key));
		$dbGetList->AddWhere('LENGTH(category) IN ('. (strlen($key) + _CATEGORY_LENGTH).','. (strlen($key) + _CATEGORY_LENGTH + _CATEGORY_LENGTH).')');
		$dbGetList->AddWhere('enabled = \'y\'');
		$dbGetList->AddWhere('parent_enabled = \'y\'');
		$dbGetList->sort = 'sort';
		$menu = array();
		while($row = $dbGetList->Get()) $menu[$row['category']] = $row;
		return $menu;
	}

	public function SetMenuRouter($url, $start = 1){
		$cont = $this->GetUrl[$start];
		if(!$cont) $cont = _DEFAULT_CONTROLLER;

		$qry = new \BH_DB_GetList(TABLE_MENU);
		$qry->AddWhere('controller = %s', $cont);
		$qry->AddWhere('LEFT(category, %d) = %s', strlen($this->RootC), $this->RootC);
		$qry->sort = 'LENGTH(category) DESC';

		$cnt = 0;
		while($row = $qry->Get()){
			if($row['parent_enabled'] == 'y' && $row['enabled'] == 'y'){
				$this->ActiveMenu = $row;
				break;
			}
			$cnt ++;
		}

		if(!$this->ActiveMenu && $cnt){
			if(_DEVELOPERIS === true) Redirect(-1, '접근이 불가능한 메뉴입니다.');
			Redirect(-1);
		}

		if($this->ActiveMenu){
			if($this->ActiveMenu['type'] == 'board'){
				App::$Instance->ControllerName = 'Board';
				App::$Instance->BaseDir = _URL;
			}
			else if($this->ActiveMenu['type'] == 'content'){
				App::$Instance->ControllerName = 'Contents';
				App::$Instance->BaseDir = _URL;
			}
			else App::$Instance->ControllerName = $this->GetUrl[$start];

			App::$Instance->TID = $this->ActiveMenu['bid'];
			App::$Instance->Action = $this->GetUrl[$start + 1];
			App::$Instance->ID = $this->GetUrl[$start + 2];
			App::$Instance->CtrlUrl = $url.'/'.$this->GetUrl[$start];
			return true;
		}
		return false;
	}

}