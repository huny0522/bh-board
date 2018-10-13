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
 * @property BH_ModelData $_subid
 * @property BH_ModelData $_addi_subid
 * @property BH_ModelData $_board_category
 * @property BH_ModelData $_board_sub_category
 */
class MenuModel extends \BH_Model
{

	public $CategoryLength = _CATEGORY_LENGTH;

	public function __Init(){
		$this->Key[] = 'category';
		$this->table = TABLE_MENU;

		$this->data['category'] = new \BH_ModelData(ModelType::String, '키값');

		$this->data['title'] = new \BH_ModelData(ModelType::String, '타이틀');
		$this->data['title']->MinLength = '1';
		$this->data['title']->MaxLength = '64';
		$this->data['title']->Required = true;

		$this->data['sort'] = new \BH_ModelData(ModelType::Int, '정렬');

		$this->data['type'] = new \BH_ModelData(ModelType::Enum, '타입', HTMLType::InputRadio);
		$this->data['type']->EnumValues = array('board' => '게시판', 'content' => '컨텐츠', 'customize' => '제작메뉴');
		$this->data['type']->DefaultValue = 'customize';
		$this->data['type']->Required = true;

		$this->data['bid'] = new \BH_ModelData(ModelType::String, '아이디');
		$this->data['bid']->MaxLength = '20';

		$this->data['controller'] = new \BH_ModelData(ModelType::String, '컨트롤명');

		$this->data['enabled'] = new \BH_ModelData(ModelType::Enum, '사용여부', HTMLType::InputRadio);
		$this->data['enabled']->EnumValues = array('y' => '사용', 'n' => '사용안함');
		$this->data['enabled']->Required = true;

		$this->data['parent_enabled'] = new \BH_ModelData(ModelType::Enum, '부모사용여부');
		$this->data['parent_enabled']->EnumValues = array('y' => '사용', 'n' => '사용안함');

		$this->data['subid'] = new \BH_ModelData(ModelType::String, '서브아이디');
		$this->data['subid']->MaxLength = '20';

		$this->data['addi_subid'] = new \BH_ModelData(ModelType::String, '추가 서브아이디');
		$this->data['addi_subid']->MaxLength = '256';

		$this->data['board_category'] = new \BH_ModelData(ModelType::String, '게시판 분류');
		$this->data['board_category']->MaxLength = '32';

		$this->data['board_sub_category'] = new \BH_ModelData(ModelType::String, '게시판 세부분류');
		$this->data['board_sub_category']->MaxLength = '256';
	} // __Init


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
