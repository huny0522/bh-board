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
		$this->key[] = 'category';
		$this->table = TABLE_MENU;

		$this->data['category'] = new \BH_ModelData(ModelType::STRING, '키값');

		$this->data['title'] = new \BH_ModelData(ModelType::STRING, '타이틀');
		$this->data['title']->minLength = '1';
		$this->data['title']->maxLength = '64';
		$this->data['title']->required = true;

		$this->data['sort'] = new \BH_ModelData(ModelType::INT, '정렬');

		$this->data['type'] = new \BH_ModelData(ModelType::ENUM, '타입', HTMLType::RADIO);
		$this->data['type']->enumValues = array('board' => '게시판', 'content' => '컨텐츠', 'customize' => '제작메뉴');
		$this->data['type']->defaultValue = 'customize';
		$this->data['type']->required = true;

		$this->data['bid'] = new \BH_ModelData(ModelType::STRING, '아이디');
		$this->data['bid']->maxLength = '20';

		$this->data['controller'] = new \BH_ModelData(ModelType::STRING, '컨트롤명');

		$this->data['enabled'] = new \BH_ModelData(ModelType::ENUM, '사용여부', HTMLType::RADIO);
		$this->data['enabled']->enumValues = array('y' => '사용', 'n' => '사용안함');
		$this->data['enabled']->required = true;

		$this->data['parent_enabled'] = new \BH_ModelData(ModelType::ENUM, '부모사용여부');
		$this->data['parent_enabled']->enumValues = array('y' => '사용', 'n' => '사용안함');

		$this->data['subid'] = new \BH_ModelData(ModelType::STRING, '서브아이디');
		$this->data['subid']->maxLength = '20';

		$this->data['addi_subid'] = new \BH_ModelData(ModelType::STRING, '추가 서브아이디');
		$this->data['addi_subid']->maxLength = '256';

		$this->data['board_category'] = new \BH_ModelData(ModelType::STRING, '게시판 분류');
		$this->data['board_category']->maxLength = '32';

		$this->data['board_sub_category'] = new \BH_ModelData(ModelType::STRING, '게시판 세부분류');
		$this->data['board_sub_category']->maxLength = '256';

		$this->data['show_level'] = new \BH_ModelData(ModelType::ENUM, '메뉴 표시 레벨', HTMLType::SELECT);
		$this->data['show_level']->enumValues = App::$settingData['LevelArray'];
		$this->data['show_level']->defaultValue = 0;

		$this->data['con_level'] = new \BH_ModelData(ModelType::ENUM, '메뉴 접근 레벨', HTMLType::SELECT);
		$this->data['con_level']->enumValues = App::$settingData['LevelArray'];
		$this->data['con_level']->defaultValue = 0;

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
