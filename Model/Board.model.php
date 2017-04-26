<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */

class BoardModel extends \BH_Model{
	public $imageTable = '';
	public $bid = '';
	public function __Init(){
		$this->Key[] = 'seq';
		$this->Except = $this->Key;
		if(!strlen($this->bid)) $this->bid = $GLOBALS['_BH_App']->TID;

		$this->table = TABLE_FIRST.'bbs_'.$this->bid;
		$this->imageTable = $this->table.'_images';

		if(!SqlTableExists($this->table)){
			Redirect(_URL.'/', '존재하지 않는 게시판입니다.');
		}

		$this->InitModelData('seq', ModelType::Int, false, '');
		$this->data['seq']->AutoDecrement = true;
		$this->InitModelData('sort1', ModelType::Int, false, '');
		$this->InitModelData('sort2', ModelType::Int, false, '');
		$this->InitModelData('depth', ModelType::Int, false, '');
		$this->InitModelData('muid', ModelType::String, false, '');
		$this->InitModelData('mlevel', ModelType::Int, false, '');
		$this->InitModelData('target_mname', ModelType::String, false, '');
		$this->InitModelData('target_muid', ModelType::String, false, '');
		$this->InitModelData('first_seq', ModelType::Int, false, '');
		$this->InitModelData('first_member_is', ModelType::String, false, '');
		$this->data['first_member_is']->EnumValues = array('y'=>'회원','n'=>'비회원');
		$this->InitModelData('reg_date', ModelType::Datetime, false, '등록일');
		$this->InitModelData('hit', ModelType::Int, false, '조회수');
		$this->InitModelData('recommend', ModelType::Int, false, '추천수');
		$this->InitModelData('reply_cnt', ModelType::Int, false, '댓글수');
		$this->InitModelData('delis', ModelType::String, false, '삭제여부');
		$this->data['delis']->DefaultValue = 'n';
		$this->InitModelData('htmlis', ModelType::String, false, 'HTML 여부');
		$this->data['htmlis']->DefaultValue = 'n';

		$this->InitModelData('manager', ModelType::String, false, '게시판관리자');
		$this->data['manager']->HtmlType = HTMLType::InputText;

		$this->InitModelData('notice', ModelType::Enum, false, '공지글');
		$this->data['notice']->HtmlType = HTMLType::InputRadio;
		$this->data['notice']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['notice']->DefaultValue = 'n';

		$this->InitModelData('category', ModelType::String, false, '분류');
		$this->data['category']->HtmlType = HTMLType::InputText;
		$this->data['category']->MaxLength = 128;

		$this->InitModelData('secret', ModelType::String, false, '비밀글');
		$this->data['secret']->HtmlType = HTMLType::InputRadio;
		$this->data['secret']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['secret']->DefaultValue = 'n';

		$this->InitModelData('mname', ModelType::String, true, '이름');
		$this->data['mname']->HtmlType = HTMLType::InputText;
		$this->data['mname']->MaxLength = 32;

		$this->InitModelData('pwd', ModelType::Password, true, '패스워드');
		$this->data['pwd']->HtmlType = HTMLType::InputPassword;
		$this->data['pwd']->MinLength = 6;
		$this->data['pwd']->MaxLength = 16;

		$this->InitModelData('subject', ModelType::String, true, '제목');
		$this->data['subject']->HtmlType = HTMLType::InputText;
		$this->data['subject']->MaxLength = 128;

		$this->InitModelData('content', ModelType::String, false, '내용');
		$this->data['content']->HtmlType = HTMLType::Textarea;

		$this->InitModelData('thumnail', ModelType::String, false, '섬네일이미지');
		$this->data['thumnail']->HtmlType = HTMLType::InputFile;

		$this->InitModelData('file1', ModelType::String, false, '파일#1');
		$this->data['file1']->HtmlType = HTMLType::InputFile;

		$this->InitModelData('file2', ModelType::String, false, '파일#2');
		$this->data['file2']->HtmlType = HTMLType::InputFile;

		$this->InitModelData('filenm1', ModelType::String, false, '파일명#1');
		$this->InitModelData('filenm2', ModelType::String, false, '파일명#2');

		if(method_exists($this, '__Init2')){
			$this->__Init2();
		}
	}

}
