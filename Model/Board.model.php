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

		$this->InitModelData('seq', ModelTypeInt, false, '');
		$this->data['seq']->AutoDecrement = true;
		$this->InitModelData('sort1', ModelTypeInt, false, '');
		$this->InitModelData('sort2', ModelTypeInt, false, '');
		$this->InitModelData('depth', ModelTypeInt, false, '');
		$this->InitModelData('muid', ModelTypeString, false, '');
		$this->InitModelData('mlevel', ModelTypeInt, false, '');
		$this->InitModelData('target_mname', ModelTypeString, false, '');
		$this->InitModelData('target_muid', ModelTypeString, false, '');
		$this->InitModelData('first_seq', ModelTypeInt, false, '');
		$this->InitModelData('first_member_is', ModelTypeString, false, '');
		$this->data['first_member_is']->EnumValues = array('y'=>'회원','n'=>'비회원');
		$this->InitModelData('reg_date', ModelTypeDatetime, false, '등록일');
		$this->InitModelData('hit', ModelTypeInt, false, '조회수');
		$this->InitModelData('recommend', ModelTypeInt, false, '추천수');
		$this->InitModelData('reply_cnt', ModelTypeInt, false, '댓글수');
		$this->InitModelData('delis', ModelTypeString, false, '삭제여부');
		$this->data['delis']->DefaultValue = 'n';
		$this->InitModelData('htmlis', ModelTypeString, false, 'HTML 여부');
		$this->data['htmlis']->DefaultValue = 'n';

		$this->InitModelData('manager', ModelTypeString, false, '게시판관리자');
		$this->data['manager']->HtmlType = HTMLInputText;

		$this->InitModelData('notice', ModelTypeEnum, false, '공지글');
		$this->data['notice']->HtmlType = HTMLInputRadio;
		$this->data['notice']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['notice']->DefaultValue = 'n';

		$this->InitModelData('category', ModelTypeString, false, '분류');
		$this->data['category']->HtmlType = HTMLInputText;
		$this->data['category']->MaxLength = 128;

		$this->InitModelData('secret', ModelTypeString, false, '비밀글');
		$this->data['secret']->HtmlType = HTMLInputRadio;
		$this->data['secret']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['secret']->DefaultValue = 'n';

		$this->InitModelData('mname', ModelTypeString, true, '이름');
		$this->data['mname']->HtmlType = HTMLInputText;
		$this->data['mname']->MaxLength = 32;

		$this->InitModelData('pwd', ModelTypePassword, true, '패스워드');
		$this->data['pwd']->HtmlType = HTMLInputPassword;
		$this->data['pwd']->MinLength = 6;
		$this->data['pwd']->MaxLength = 16;

		$this->InitModelData('subject', ModelTypeString, true, '제목');
		$this->data['subject']->HtmlType = HTMLInputText;
		$this->data['subject']->MaxLength = 128;

		$this->InitModelData('content', ModelTypeString, false, '내용');
		$this->data['content']->HtmlType = HTMLTextarea;

		$this->InitModelData('thumnail', ModelTypeString, false, '섬네일이미지');
		$this->data['thumnail']->HtmlType = HTMLInputFile;

		$this->InitModelData('file1', ModelTypeString, false, '파일#1');
		$this->data['file1']->HtmlType = HTMLInputFile;

		$this->InitModelData('file2', ModelTypeString, false, '파일#2');
		$this->data['file2']->HtmlType = HTMLInputFile;

		$this->InitModelData('filenm1', ModelTypeString, false, '파일명#1');
		$this->InitModelData('filenm2', ModelTypeString, false, '파일명#2');

		if(method_exists($this, '__Init2')){
			$this->__Init2();
		}
	}

}
