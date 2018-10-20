<?php

namespace Common;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class MenuHelp
{
	private $menus = array();
	private $menusLoaded = false;

	private $useDB = true;
	private $table = '';

	private $rootCategory = '';
	private $rootTitle = 'Home';
	private $activeMenu = null;
	private $routingSuccess = false;

	// HTML 옵션들
	private $tagName = 'li';
	private $class = '';
	private $attr = '';
	private $activeClass = 'active';
	private $linkWrapTag = '';
	private $head = '';
	private $tail = '';

	/**
	 * @var MenuHelp[]
	 */
	private static $instance = array();

	/* -------------------------------------------------
	 *
	 *       옵션
	 *
	------------------------------------------------- */
	private function __construct(){}

	private function __clone(){}

	/**
	 * @param string $table
	 * @return MenuHelp
	 */
	public static function GetInstance($table = ''){
		if(!$table) $table = TABLE_MENU;

		if(!isset(self::$instance[$table])){
			self::$instance[$table] = new static();
			if(defined('_MENU_CACHE_FILE') && _MENU_CACHE_FILE === true){
				self::$instance[$table]->useDB = false;
			}

			self::$instance[$table]->table = TABLE_MENU;

			self::$instance[$table]->MenusToArray();
		}
		return self::$instance[$table];
	}

	public function Reset(){
		$this->tagName = 'li';
		$this->class = '';
		$this->attr = '';
		$this->activeClass = 'active';
		$this->linkWrapTag = '';
		$this->head = '';
		$this->tail = '';
		return $this;
	}

	/**
	 * 항목의 태그명을 설정.
	 * 기본값 : li
	 *
	 * @param string $tagName
	 * @return MenuHelp
	 */
	public function SetTagName($tagName){
		$this->tagName = $tagName;
		return $this;
	}

	/**
	 * 항목의 추가 클래스를 설정.
	 *
	 * @param string $class
	 * @return MenuHelp
	 */
	public function SetClass($class){
		$this->class = $class;
		return $this;
	}

	/**
	 * 항목의 속성들을 설정.
	 *
	 * @param string $attr
	 * @return MenuHelp
	 */
	public function SetAttr($attr){
		$this->attr = $attr;
		return $this;
	}

	/**
	 * 현재 위치의 항목에 추가할 클래스 설정.
	 * 기본값 : active
	 *
	 * @param string $activeClass
	 * @return MenuHelp
	 */
	public function SetActiveClass($activeClass){
		$this->activeClass = $activeClass;
		return $this;
	}

	/**
	 * 항목의 링크에 추가할 태그 설정.
	 * 기본값 : li
	 *
	 * @param string $linkWrapTag
	 * @return MenuHelp
	 */
	public function SetLinkWrapTag($linkWrapTag){
		$this->linkWrapTag = $linkWrapTag;
		return $this;
	}

	/**
	 * 항목들 최상단에 출력할 문자열 설정.
	 *
	 * @param string $head
	 * @return MenuHelp
	 */
	public function SetHead($head){
		$this->head = $head;
		return $this;
	}

	/**
	 * 항목들 최하단에 출력할 문자열 설정.
	 *
	 * @param string $tail
	 * @return MenuHelp
	 */
	public function SetTail($tail){
		$this->tail = $tail;
		return $this;
	}

	/**
	 * 파일 또는 DB에서 메뉴를 설정
	 */
	private function MenusToArray(){
		if(!$this->menusLoaded){
			$this->menusLoaded = true;
			if($this->useDB){
				$this->DBToMenus();
			}
			else{
				$temp = App::$CFG->Sys()->menuCache->value;
				if(!file_exists(_DATADIR . '/CFG/System.php')) $this->MenusToFile();
				if(!is_array($temp)) $this->menus = array();
				else{
					foreach($temp as $v) $this->menus[$v['category']] = $v;
				}
			}
		}
	}

	private function DBToMenus(){
		$dbGet = DB::GetListQryObj($this->table)
			->AddWhere('`enabled` = \'y\'')
			->AddWhere('`parent_enabled` = \'y\'')
			->SetSort('sort');
		while($row = $dbGet->Get()){
			$this->menus[$row['category']] = $row;
		}
	}

	public function MenusToFile(){
		if($this->useDB) return;
		$this->menus = array();
		$this->DBToMenus();
		App::$CFG->Sys()->menuCache->value = $this->menus;
		App::$CFG->Sys()->DataWrite();
	}

	/* -------------------------------------------------
	 *
	 *       Util
	 *
	------------------------------------------------- */
	/**
	 * 하위 메뉴전체를 반환
	 * @param string $category
	 * @return array
	 */
	public function GetMenus($category = ''){
		$len = strlen($category);
		if(!$len) return $this->menus;

		$arr = array();
		foreach($this->menus as $k => $v){
			if($category == substr($k, 0, $len)) $arr[$k] = $v;
		}
		return $arr;
	}
	/**
	 * 메인메뉴를 반환
	 */
	public function GetMainMenu(){
		if(!strlen($this->rootCategory)) $this->FindRootMenuByTitle();
		return $this->GetSubMenu($this->rootCategory);
	}

	/**
	 * 루트 카테고리 반환
	 */
	public function GetRootCategory(){
		if(!strlen($this->rootCategory)) $this->FindRootMenuByTitle();
		return $this->rootCategory;
	}

	/**
	 * 루트메뉴를 선택
	 */
	public function FindRootMenuByTitle(){
		foreach($this->menus as $v){
			if(strlen($v['category']) == _CATEGORY_LENGTH){
				if(strlen($this->rootTitle)){
					if($v['controller'] == $this->rootTitle) $this->rootCategory = $v['category'];
				}
				else $this->rootCategory = $v['category'];
			}
		}
	}

	/**
	 * 루트메뉴의 이름 설정
	 * @param $name
	 */
	public function SetRootTitle($name){
		$this->rootTitle = $name;
	}

	/**
	 * 하위 메뉴 반환
	 *
	 * @param string $key
	 * @return array
	 */
	public function GetSubMenu($key){
		$menu = array();
		foreach($this->menus as $v){
			if(substr($v['category'], 0, strlen($key)) === $key && strlen($v['category']) === strlen($key) + _CATEGORY_LENGTH){
				$menu[$v['category']] = $v;
			}
		}
		return $menu;
	}

	/**
	 * 카테고리가 활성화 메뉴에 속하지는지 확인
	 *
	 * @param string $category
	 * @return bool
	 */
	public function ActiveCheck($category){
		return (!is_null($this->activeMenu) && substr($this->activeMenu['category'], 0, strlen($category)) === $category);
	}

	/**
	 * @param string $category
	 * @param string $func 하위메뉴를 반환할 함수
	 * @return string
	 */
	public function Html($category, $func = ''){
		$menuData = $this->GetSubMenu($category);

		// 바뀔수 서브함수로 바뀔수 있으므로 가져온다.
		$tagName = $this->tagName;
		$attr = $this->attr;
		$class = $this->class;
		$activeClass = $this->activeClass;
		$linkWrapTag = $this->linkWrapTag;
		$head = $this->head;
		$tail = $this->tail;

		$html = '';

		foreach($menuData as $menu){
			$html .= '<'.$tagName.$attr.' class="'.$class.($this->ActiveCheck($menu['category']) ? ' '.$activeClass : '').'">';
			if($linkWrapTag) echo '<'.$linkWrapTag.'>';
			$html .= '<a href="'._URL.'/'.urlencode(GetDBText($menu['controller'])).'">'.GetDBText($menu['title']).'</a>';
			if($linkWrapTag) echo '</'.$linkWrapTag.'>';
			if(is_callable($func)) $html .= $func($menu['category']);
			$html .= '</'.$tagName.'>';
		}

		return ($html) ? $head . $html . $tail : '';
	}

	/**
	 * 활성화 메뉴 반환
	 *
	 * @return array|null
	 */
	public function GetActiveMenu(){
		return $this->activeMenu;
	}

	/**
	 * 활성화 메뉴 타이틀 반환
	 *
	 * @return string
	 */
	public function GetActiveTitle(){
		return is_null($this->activeMenu) ? '' : $this->activeMenu['title'];
	}

	/**
	 * 활성화 메뉴 카테고리 반환
	 *
	 * @return string
	 */
	public function GetActiveCategory(){
		return is_null($this->activeMenu) ? '' : $this->activeMenu['category'];
	}

	/**
	 * 접근가능 메뉴인지 체크하고 라우팅함
	 *
	 * @param string $url
	 * @param int $start
	 * @return bool
	 */
	public function SetDBMenuRouter($url, $start = 1){
		$this->FindRootMenuByTitle();
		$urlControllerName = App::$SettingData['GetUrl'][$start];
		if(!$urlControllerName) $urlControllerName = _DEFAULT_CONTROLLER;

		// 활성메뉴 찾기
		foreach($this->menus as $v){
			if($v['controller'] == $urlControllerName && substr($v['category'], 0, strlen($this->rootCategory)) === $this->rootCategory && $v['category'] !== $this->rootCategory){
				if(!is_null($this->activeMenu)){
					if(strlen($this->activeMenu['category']) < strlen($v['category'])) $this->activeMenu = $v;
				}
				else $this->activeMenu = $v;
			}
		}

		// 활성메뉴가 없을 때 비활성 메뉴인지 체크
		if(is_null($this->activeMenu)){
			$disabledData = DB::GetQryObj($this->table)
				->AddWhere('`enabled` = \'n\' OR `parent_enabled` = \'n\'')
				->AddWhere('LEFT(`category`, %d) = %s', strlen($this->rootCategory), $this->rootCategory)
				->AddWhere('`controller` = %s', $urlControllerName)
				->SetKey('`category`')
				->Get();

			if(isset($disabledData['category']) && strlen($disabledData['category'])){
				if(_DEVELOPERIS === true) URLReplace(-1, '접근이 불가능한 메뉴입니다.');
				URLReplace(-1);
			}
		}
		else{
			if($this->activeMenu['category'] === $this->rootCategory){
				$this->routingSuccess = false;
				return $this->routingSuccess;
			}

			if($this->activeMenu['type'] == 'board') App::$ControllerName = 'Board';
			else if($this->activeMenu['type'] == 'content') App::$ControllerName = 'Contents';
			else App::$ControllerName = App::$SettingData['GetUrl'][$start];

			App::$TID = $this->activeMenu['bid'];
			App::$SUB_TID = $this->activeMenu['subid'];
			App::$SettingData['additionalSubid'] = $this->activeMenu['addi_subid'];
			if(strlen($this->activeMenu['board_category'])) App::$SettingData['boardCategory'] = $this->activeMenu['board_category'];
			if(strlen($this->activeMenu['board_sub_category'])) App::$SettingData['boardSubCategory'] = $this->activeMenu['board_sub_category'];
			App::$Action = App::$SettingData['GetUrl'][$start + 1];
			App::$ID = App::$SettingData['GetUrl'][$start + 2];
			App::$CtrlUrl = $url.'/'.App::$SettingData['GetUrl'][$start];
			$this->routingSuccess = true;
			return $this->routingSuccess;
		}

		$this->routingSuccess = false;
		return $this->routingSuccess;
	}

	/**
	 * 자신을 포함한 부모 메뉴를 반환
	 *
	 * @param string $category
	 * @return array
	 */
	public function GetParentsMenuByCate($category){
		$menu = array();
		foreach($this->menus as $v){
			if(strlen($v['category']) > _CATEGORY_LENGTH && substr($category, 0, strlen($v['category'])) == $v['category']) $menu[$v['category']] = $v;
		}

		uksort($menu, function($a, $b){
			return strlen($a) - strlen($b);
		});

		return $menu;
	}

	/**
	 * 게시판 카테고리와 bid 를 이용하여 메뉴 가져오기
	 *
	 * @param string $bid
	 * @param string $subid
	 * @param string $category
	 * @return null|array
	 */
	public function GetBoardMenuByBid($bid, $subid, $category = ''){
		$temp = null;
		foreach($this->menus as $v){
			if($v['type'] == 'board'){
				if($v['bid'] === $bid && $v['subid'] === $subid){
					if($category && $v['board_category'] == $category) return $v;
					else if(!$category) return $v;
					$temp = $v;
				}
			}
		}

		return $temp;
	}

	/**
	 * 게시판 카테고리와 bid 를 이용하여 하위 메뉴 가져오기
	 *
	 * @param string $bid
	 * @param string $subid
	 * @param string $category
	 * @param bool $thisCateThenEmpty
	 * @return null|array
	 */
	public function GetSubMenusByBid($bid, $subid, $category, $thisCateThenEmpty = true){
		$temp = array();
		foreach($this->menus as $v){
			if($v['type'] == 'board' && $v['bid'] === $bid && $v['subid'] === $subid && substr($v['category'], 0, strlen($category)) === $category && strlen($v['category']) === strlen($category) + _CATEGORY_LENGTH){
				$temp[$v['category']] = $v;
			}
		}

		if($thisCateThenEmpty && !sizeof($temp) && strlen($category)){
			$category = substr($category, 0, -_CATEGORY_LENGTH);
			foreach($this->menus as $v){
				if($v['type'] == 'board' && $v['bid'] === $bid && $v['subid'] === $subid && substr($v['category'], 0, strlen($category)) === $category && strlen($v['category']) === strlen($category) + _CATEGORY_LENGTH){
					$temp[$v['category']] = $v;
				}
			}
		}

		return $temp;
	}

	/**
	 * 게시판 카테고리와 bid 를 이용하여 하위 메뉴 가져오기
	 *
	 * @param string $bid
	 * @param string $subid
	 * @param string $category
	 * @return null|array
	 */
	public function GetSubMenusAllByBid($bid, $subid, $category){
		$temp = array();
		foreach($this->menus as $v){
			if($v['type'] == 'board' && $v['bid'] === $bid && $v['subid'] === $subid && substr($v['category'], 0, strlen($category)) === $category){
				$temp[$v['category']] = $v;
			}
		}
		return $temp;
	}
}