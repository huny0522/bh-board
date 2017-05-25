<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */
use \BH_Common as CF;
use \BH_Application as App;

class PopupModel extends \BH_Model{
	public function __Init(){
		$this->Key[] = 'seq';
		$this->table = TABLE_POPUP;

		$this->data['seq'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['seq']->AutoDecrement = true;

		$this->data['subject'] = new \BH_ModelData(ModelType::String, true, '제목');
		$this->data['subject']->HtmlType = HTMLType::InputText;
		$this->data['subject']->MaxLength = 50;

		$this->data['img'] = new \BH_ModelData(ModelType::String, false, '이미지');
		$this->data['img']->HtmlType = HTMLType::InputFile;

		$this->data['contents'] = new \BH_ModelData(ModelType::String, false, '내용');
		$this->data['contents']->HtmlType = HTMLType::Textarea;

		$this->data['type'] = new \BH_ModelData(ModelType::Enum, true, '타입');
		$this->data['type']->HtmlType = HTMLType::InputRadio;
		$this->data['type']->EnumValues = array('i'=>'이미지','c'=>'컨텐츠');
		$this->data['type']->DefaultValue = 'i';

		$this->data['begin_date'] = new \BH_ModelData(ModelType::Date, true, '시작일');
		$this->data['begin_date']->HtmlType = HTMLType::InputText;

		$this->data['end_date'] = new \BH_ModelData(ModelType::Date, true, '종료일');
		$this->data['end_date']->HtmlType = HTMLType::InputText;

		$this->data['enabled'] = new \BH_ModelData(ModelType::Enum, true, '사용여부');
		$this->data['enabled']->HtmlType = HTMLType::InputRadio;
		$this->data['enabled']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['enabled']->DefaultValue = 'y';

		$this->data['new_window'] = new \BH_ModelData(ModelType::Enum, false, '새창여부');
		$this->data['new_window']->HtmlType = HTMLType::InputRadio;
		$this->data['new_window']->EnumValues = array('y'=>'새창','n'=>'현재창');
		$this->data['new_window']->DefaultValue = 'n';

		$this->data['mlevel'] = new \BH_ModelData(ModelType::Enum, true, '회원레벨');
		$this->data['mlevel']->HtmlType = HTMLType::Select;
		$this->data['mlevel']->EnumValues = App::$SettingData['LevelArray'];
		$this->data['mlevel']->DefaultValue = '0';

		$this->data['link_url'] = new \BH_ModelData(ModelType::String, false, '링크주소');
		$this->data['link_url']->HtmlType = HTMLType::InputText;

		$this->data['width'] = new \BH_ModelData(ModelType::Int, true, '넓이');
		$this->data['width']->HtmlType = HTMLType::InputText;
		$this->data['width']->DefaultValue = '300';

		$this->data['height'] = new \BH_ModelData(ModelType::Int, true, '높이');
		$this->data['height']->HtmlType = HTMLType::InputText;
		$this->data['height']->DefaultValue = '400';

		$this->data['sort'] = new \BH_ModelData(ModelType::Int, true, '정렬');
		$this->data['sort']->HtmlType = HTMLType::InputText;
		$this->data['sort']->DefaultValue = '0';

	}

}
