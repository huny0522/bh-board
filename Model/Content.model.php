<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */
class ContentModel extends \BH_Model{

	public function __Init(){
		$this->Key[] = 'bid';
		$this->table = TABLE_CONTENT;

		$this->InitModelData('subject', ModelType::String, true, '제목');
		$this->data['subject']->HtmlType = HTMLType::InputText;
		$this->data['subject']->MaxLength = 128;

		$this->InitModelData('bid', ModelType::EngNum, true, '아이디');
		$this->data['bid']->HtmlType = HTMLType::InputText;
		$this->data['bid']->MinLength = '1';
		$this->data['bid']->MaxLength = '20';

		$this->InitModelData('html', ModelType::String, true, '컨텐츠파일');
		$this->data['html']->HtmlType = HTMLType::InputText;
		$this->data['html']->MinLength = '1';
		$this->data['html']->MaxLength = '20';

		$this->InitModelData('layout', ModelType::String, false, '레이아웃');
		$this->data['layout']->HtmlType = HTMLType::InputText;
		$this->data['layout']->MinLength = '1';
		$this->data['layout']->MaxLength = '50';

		$this->InitModelData('hit', ModelType::Int, false, '조회수');

		$this->InitModelData('recommend', ModelType::Int, false, '추천수');
		$this->InitModelData('reg_date', ModelType::Datetime, false, '등록일');
	}

}
