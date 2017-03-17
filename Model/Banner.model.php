<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */

class BannerModel extends BH_Model{
	public function __Init(){
		$this->Key[] = 'seq';
		$this->table = TABLE_BANNER;

		$this->InitModelData('seq', ModelTypeInt, false, '');
		$this->data['seq']->AutoDecrement = true;

		$this->InitModelData('category', ModelTypeString, true, '분류');
		$this->data['category']->HtmlType = HTMLInputText;
		$this->data['category']->MaxLength = 20;

		$this->InitModelData('subject', ModelTypeString, true, '제목');
		$this->data['subject']->HtmlType = HTMLInputText;
		$this->data['subject']->MaxLength = 50;

		$this->InitModelData('img', ModelTypeString, false, '이미지');
		$this->data['img']->HtmlType = HTMLInputFile;

		$this->InitModelData('contents', ModelTypeString, false, '내용');
		$this->data['contents']->HtmlType = HTMLTextarea;

		$this->InitModelData('type', ModelTypeEnum, true, '타입');
		$this->data['type']->HtmlType = HTMLInputRadio;
		$this->data['type']->EnumValues = array('i'=>'이미지','c'=>'컨텐츠');
		$this->data['type']->DefaultValue = 'i';

		$this->InitModelData('begin_date', ModelTypeDate, true, '시작일');
		$this->data['begin_date']->HtmlType = HTMLInputText;

		$this->InitModelData('end_date', ModelTypeDate, true, '종료일');
		$this->data['end_date']->HtmlType = HTMLInputText;

		$this->InitModelData('enabled', ModelTypeEnum, true, '사용여부');
		$this->data['enabled']->HtmlType = HTMLInputRadio;
		$this->data['enabled']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['enabled']->DefaultValue = 'y';

		$this->InitModelData('new_window', ModelTypeEnum, false, '새창여부');
		$this->data['new_window']->HtmlType = HTMLInputRadio;
		$this->data['new_window']->EnumValues = array('y'=>'새창','n'=>'현재창');
		$this->data['new_window']->DefaultValue = 'n';

		$this->InitModelData('mlevel', ModelTypeEnum, true, '회원레벨');
		$this->data['mlevel']->HtmlType = HTMLSelect;
		$this->data['mlevel']->EnumValues = $GLOBALS['_LevelArray'];
		$this->data['mlevel']->DefaultValue = '0';

		$this->InitModelData('sort', ModelTypeInt, true, '정렬');
		$this->data['sort']->HtmlType = HTMLInputText;
		$this->data['sort']->DefaultValue = '0';

		$this->InitModelData('link_url', ModelTypeString, false, '링크주소');
		$this->data['link_url']->HtmlType = HTMLInputText;
	}
}
