<?php
/**
 * Bang Hun.
 * 16.07.10
 */

use \BH_Common as CM;
use \BH_Application as App;

/**
 * Class MenuModel
 *
 * @property BH_ModelData[] $data
 * @property BH_ModelData $_category
 * @property BH_ModelData $_title
 * @property BH_ModelData $_sort
 * @property BH_ModelData $_type
 * @property BH_ModelData $_bid
 * @property BH_ModelData $_controller
 * @property BH_ModelData $_enabled
 * @property BH_ModelData $_parent_enabled
 */
class MenuModel extends \BH_Model{

	public $CategoryLength = _CATEGORY_LENGTH;

	public function __Init(){
		$this->Key[] = 'category';
		$this->table = TABLE_MENU;

		$this->data['category'] = new \BH_ModelData(ModelType::String, false, '키값');

		$this->data['title'] = new \BH_ModelData(ModelType::String, true, '타이틀');
		$this->data['title']->MinLength = '1';
		$this->data['title']->MaxLength = '64';

		$this->data['sort'] = new \BH_ModelData(ModelType::Int, false, '정렬');

		$this->data['type'] = new \BH_ModelData(ModelType::Enum, true, '타입', HTMLType::InputRadio);
		$this->data['type']->EnumValues = array('board' => '게시판', 'content' => '컨텐츠', 'customize' => '제작메뉴');
		$this->data['type']->DefaultValue = 'customize';

		$this->data['bid'] = new \BH_ModelData(ModelType::String, false, '아이디');
		$this->data['bid']->MaxLength = '20';

		$this->data['controller'] = new \BH_ModelData(ModelType::String, false, '컨트롤명');

		$this->data['enabled'] = new \BH_ModelData(ModelType::Enum, true, '사용여부', HTMLType::InputRadio);
		$this->data['enabled']->EnumValues = array('y' => '사용', 'n' => '사용안함');

		$this->data['parent_enabled'] = new \BH_ModelData(ModelType::Enum, false, '부모사용여부');
		$this->data['parent_enabled']->EnumValues = array('y' => '사용', 'n' => '사용안함');
	}


	// 자식레벨 가져오기
	public function GetChild($parent = ''){
		return CM::_CategoryGetChild($this->table, $parent, $this->CategoryLength);
	}

	public function SetChildEnabled($parent = NULL, $enabled = false){
		CM::_CategorySetChildEnable($this->table, $parent, $enabled);
	}

	public function GetParent($category = NULL){
		return CM::_CategoryGetParent($this->table, $category, $this->CategoryLength);
	}
}
