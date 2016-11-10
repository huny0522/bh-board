<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */
class MenuModel extends BH_Model{

	public $CategoryLength = _CATEGORY_LENGTH;

	public function __Init(){
		$this->Key[] = 'category';
		$this->table = TABLE_MENU;

		$this->InitModelData('category', ModelTypeString, false, '키값');

		$this->InitModelData('title', ModelTypeString, true, '타이틀');
		$this->data['title']->HtmlType = HTMLInputText;
		$this->data['title']->MinLength = '1';
		$this->data['title']->MaxLength = '64';

		$this->InitModelData('sort', ModelTypeInt, false, '정렬');
		$this->data['sort']->HtmlType = HTMLInputText;

		$this->InitModelData('type', ModelTypeEnum, true, '타입');
		$this->data['type']->HtmlType = HTMLInputRadio;
		$this->data['type']->EnumValues = array('board' => '게시판', 'content' => '컨텐츠', 'customize' => '제작메뉴');
		$this->data['type']->DefaultValue = 'content';

		$this->InitModelData('bid', ModelTypeString, false, '아이디');
		$this->data['bid']->HtmlType = HTMLInputText;
		$this->data['bid']->MaxLength = '20';

		$this->InitModelData('controller', ModelTypeString, false, '컨트롤명');
		$this->data['controller']->HtmlType = HTMLInputText;

		$this->InitModelData('enabled', ModelTypeEnum, true, '사용여부');
		$this->data['enabled']->HtmlType = HTMLInputRadio;
		$this->data['enabled']->EnumValues = array('y' => '사용', 'n' => '사용안함');

		$this->InitModelData('parent_enabled', ModelTypeEnum, false, '부모사용여부');
		$this->data['parent_enabled']->EnumValues = array('y' => '사용', 'n' => '사용안함');
	}

	// 자식레벨 가져오기
	public function GetChild($parent = ''){
		$dbGet = new BH_DB_GetList($this->table);
		$dbGet->AddWhere('LEFT(category, '.strlen($parent).') = '.SetDBText($parent));
		$dbGet->AddWhere('LENGTH(category) = '.(strlen($parent) + _CATEGORY_LENGTH));
		$dbGet->sort = 'sort';
		return $dbGet;
	}

	public function SetChildEnabled($parent = NULL, $enabled = false){
		if(is_null($parent)) return;

		$dbUpdate = new BH_DB_Update();
		$dbUpdate->table = $this->table;
		$dbUpdate->AddWhere('LEFT(category, '.strlen($parent).') = '.SetDBText($parent));
		$dbUpdate->data['parent_enabled'] = SetDBText($enabled);
		//$dbUpdate->test = true;
		$dbUpdate->Run();
	}

	public function GetParent($category = NULL){
		if(is_null($category)) return false;
		$parent = substr($category, 0, strlen($category) - _CATEGORY_LENGTH);
		if(!$parent) return false;

		$dbGet = new BH_DB_Get($this->table);
		$dbGet->AddWhere('category='.SetDBText($parent));
		return $dbGet->Get();
	}

}
