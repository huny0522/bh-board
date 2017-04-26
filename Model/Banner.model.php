<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */

class BannerModel extends \BH_Model{
	public function __Init(){
		$this->Key[] = 'seq';
		$this->table = TABLE_BANNER;

		$this->InitModelData('seq', ModelType::Int, false, '');
		$this->data['seq']->AutoDecrement = true;

		$this->InitModelData('category', ModelType::String, true, '분류');
		$this->data['category']->HtmlType = HTMLType::InputText;
		$this->data['category']->MaxLength = 20;

		$this->InitModelData('subject', ModelType::String, true, '제목');
		$this->data['subject']->HtmlType = HTMLType::InputText;
		$this->data['subject']->MaxLength = 50;

		$this->InitModelData('img', ModelType::String, false, '이미지');
		$this->data['img']->HtmlType = HTMLType::InputFile;

		$this->InitModelData('contents', ModelType::String, false, '내용');
		$this->data['contents']->HtmlType = HTMLType::Textarea;

		$this->InitModelData('type', ModelType::Enum, true, '타입');
		$this->data['type']->HtmlType = HTMLType::InputRadio;
		$this->data['type']->EnumValues = array('i'=>'이미지','c'=>'컨텐츠');
		$this->data['type']->DefaultValue = 'i';

		$this->InitModelData('begin_date', ModelType::Date, true, '시작일');
		$this->data['begin_date']->HtmlType = HTMLType::InputText;

		$this->InitModelData('end_date', ModelType::Date, true, '종료일');
		$this->data['end_date']->HtmlType = HTMLType::InputText;

		$this->InitModelData('enabled', ModelType::Enum, true, '사용여부');
		$this->data['enabled']->HtmlType = HTMLType::InputRadio;
		$this->data['enabled']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['enabled']->DefaultValue = 'y';

		$this->InitModelData('new_window', ModelType::Enum, false, '새창여부');
		$this->data['new_window']->HtmlType = HTMLType::InputRadio;
		$this->data['new_window']->EnumValues = array('y'=>'새창','n'=>'현재창');
		$this->data['new_window']->DefaultValue = 'n';

		$this->InitModelData('mlevel', ModelType::Enum, true, '회원레벨');
		$this->data['mlevel']->HtmlType = HTMLType::Select;
		$this->data['mlevel']->EnumValues = $GLOBALS['_LevelArray'];
		$this->data['mlevel']->DefaultValue = '0';

		$this->InitModelData('sort', ModelType::Int, true, '정렬');
		$this->data['sort']->HtmlType = HTMLType::InputText;
		$this->data['sort']->DefaultValue = '0';

		$this->InitModelData('link_url', ModelType::String, false, '링크주소');
		$this->data['link_url']->HtmlType = HTMLType::InputText;
	}
}
