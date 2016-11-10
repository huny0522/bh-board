<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */
class ContentModel extends BH_Model{

	public function __Init(){
		$this->Key[] = 'bid';
		$this->table = TABLE_CONTENT;

		$this->InitModelData('subject', ModelTypeString, true, '제목');
		$this->data['subject']->HtmlType = HTMLInputText;
		$this->data['subject']->MaxLength = 128;

		$this->InitModelData('bid', ModelTypeEngNum, true, '아이디');
		$this->data['bid']->HtmlType = HTMLInputText;
		$this->data['bid']->MinLength = '1';
		$this->data['bid']->MaxLength = '20';

		$this->InitModelData('html', ModelTypeString, true, '컨텐츠파일');
		$this->data['html']->HtmlType = HTMLInputText;
		$this->data['html']->MinLength = '1';
		$this->data['html']->MaxLength = '20';

		$this->InitModelData('layout', ModelTypeString, false, '레이아웃');
		$this->data['layout']->HtmlType = HTMLInputText;
		$this->data['layout']->MinLength = '1';
		$this->data['layout']->MaxLength = '50';

		$this->InitModelData('hit', ModelTypeInt, false, '조회수');

		$this->InitModelData('recommend', ModelTypeInt, false, '추천수');
		$this->InitModelData('reg_date', ModelTypeDatetime, false, '등록일');
	}

}
