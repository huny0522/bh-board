<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */

use \BH_Common as CM;
use \BH_Application as App;

/**
 * Class PopupModel
 *
 * @property BH_ModelData[] $data
 * @property BH_ModelData $_seq
 * @property BH_ModelData $_kind
 * @property BH_ModelData $_category
 * @property BH_ModelData $_subject
 * @property BH_ModelData $_img
 * @property BH_ModelData $_contents
 * @property BH_ModelData $_type
 * @property BH_ModelData $_begin_date
 * @property BH_ModelData $_end_date
 * @property BH_ModelData $_enabled
 * @property BH_ModelData $_new_window
 * @property BH_ModelData $_mlevel
 * @property BH_ModelData $_link_url
 * @property BH_ModelData $_width
 * @property BH_ModelData $_height
 * @property BH_ModelData $_sort
 * @property BH_ModelData $_pos_x
 * @property BH_ModelData $_pos_y
 */
class PopupModel extends \BH_Model
{
	public function __Init(){
		$this->key[] = 'seq';
		$this->table = TABLE_POPUP;

		$this->data['seq'] = new \BH_ModelData(ModelType::INT, '');

		$this->data['kind'] = new \BH_ModelData(ModelType::ENUM, '접속장치별 표시', HTMLType::CHECKBOX);
		$this->data['kind']->enumValues = array(
		'pc' => 'PC',
		'mobile' => '모바일'
		);

		$this->data['category'] = new \BH_ModelData(ModelType::STRING, '분류');
		$this->data['category']->maxLength = 20;
		$this->data['category']->required = true;

		$this->data['subject'] = new \BH_ModelData(ModelType::STRING, '제목');
		$this->data['subject']->maxLength = 50;
		$this->data['subject']->required = true;

		$this->data['img'] = new \BH_ModelData(ModelType::STRING, '이미지', HTMLType::FILE_IMAGE);

		$this->data['contents'] = new \BH_ModelData(ModelType::TEXT, '내용', HTMLType::TEXTAREA);

		$this->data['type'] = new \BH_ModelData(ModelType::ENUM, '타입', HTMLType::RADIO);
		$this->data['type']->enumValues = array('i'=>'이미지','c'=>'컨텐츠');
		$this->data['type']->defaultValue = 'i';
		$this->data['type']->required = true;

		$this->data['begin_date'] = new \BH_ModelData(ModelType::DATE, '시작일', HTMLType::DATE_PICKER);
		$this->data['begin_date']->required = true;

		$this->data['end_date'] = new \BH_ModelData(ModelType::DATE, '종료일', HTMLType::DATE_PICKER);
		$this->data['end_date']->required = true;

		$this->data['enabled'] = new \BH_ModelData(ModelType::ENUM, '사용여부', HTMLType::RADIO);
		$this->data['enabled']->enumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['enabled']->defaultValue = 'y';
		$this->data['enabled']->required = true;

		$this->data['new_window'] = new \BH_ModelData(ModelType::ENUM, '새창여부', HTMLType::RADIO);
		$this->data['new_window']->enumValues = array('y'=>'새창','n'=>'현재창');
		$this->data['new_window']->defaultValue = 'n';

		$this->data['mlevel'] = new \BH_ModelData(ModelType::ENUM, '회원레벨', HTMLType::SELECT);
		$this->data['mlevel']->enumValues = App::$settingData['LevelArray'];
		$this->data['mlevel']->defaultValue = '0';
		$this->data['mlevel']->required = true;

		$this->data['link_url'] = new \BH_ModelData(ModelType::STRING, '링크주소');

		$this->data['width'] = new \BH_ModelData(ModelType::INT, '넓이');
		$this->data['width']->defaultValue = '300';
		$this->data['width']->required = true;

		$this->data['height'] = new \BH_ModelData(ModelType::INT, '높이');
		$this->data['height']->defaultValue = '400';
		$this->data['height']->required = true;

		$this->data['sort'] = new \BH_ModelData(ModelType::INT, '정렬');
		$this->data['sort']->defaultValue = '0';
		$this->data['sort']->required = true;

		$this->data['pos_x'] = new \BH_ModelData(ModelType::INT, 'X좌표');
		$this->data['pos_x']->defaultValue = 0;

		$this->data['pos_y'] = new \BH_ModelData(ModelType::INT, 'Y좌표');
		$this->data['pos_y']->defaultValue = 0;
	} // __Init

}
