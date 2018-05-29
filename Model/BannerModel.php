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
 * Class BannerModel
 * @property BH_ModelData[] $data
 * @property BH_ModelData $_seq
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
 * @property BH_ModelData $_sort
 * @property BH_ModelData $_link_url
 */
class BannerModel extends \BH_Model{
	public function __Init(){
		$this->Key[] = 'seq';
		$this->table = TABLE_BANNER;

		$this->data['seq'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['seq']->AutoDecrement = true;

		$this->data['category'] = new \BH_ModelData(ModelType::String, true, '분류');
		$this->data['category']->MaxLength = 20;

		$this->data['subject'] = new \BH_ModelData(ModelType::String, true, '제목');
		$this->data['subject']->MaxLength = 50;

		$this->data['img'] = new \BH_ModelData(ModelType::String, false, '이미지', HTMLType::InputFile);

		$this->data['contents'] = new \BH_ModelData(ModelType::String, false, '내용', HTMLType::Textarea);

		$this->data['type'] = new \BH_ModelData(ModelType::Enum, true, '타입', HTMLType::InputRadio);
		$this->data['type']->EnumValues = array('i'=>'이미지','c'=>'컨텐츠');
		$this->data['type']->DefaultValue = 'i';

		$this->data['begin_date'] = new \BH_ModelData(ModelType::Date, true, '시작일', HTMLType::InputDatePicker);

		$this->data['end_date'] = new \BH_ModelData(ModelType::Date, true, '종료일', HTMLType::InputDatePicker);

		$this->data['enabled'] = new \BH_ModelData(ModelType::Enum, true, '사용여부', HTMLType::InputRadio);
		$this->data['enabled']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['enabled']->DefaultValue = 'y';

		$this->data['new_window'] = new \BH_ModelData(ModelType::Enum, false, '새창여부', HTMLType::InputRadio);
		$this->data['new_window']->EnumValues = array('y'=>'새창','n'=>'현재창');
		$this->data['new_window']->DefaultValue = 'n';

		$this->data['mlevel'] = new \BH_ModelData(ModelType::Enum, true, '회원레벨', HTMLType::Select);
		$this->data['mlevel']->EnumValues = App::$SettingData['LevelArray'];
		$this->data['mlevel']->DefaultValue = '0';

		$this->data['sort'] = new \BH_ModelData(ModelType::Int, true, '정렬');
		$this->data['sort']->DefaultValue = '0';

		$this->data['link_url'] = new \BH_ModelData(ModelType::String, false, '링크주소');
	} // __Init
}
