<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */

class ReplyModel extends \BH_Model{
	public $bid = '';
	public $boardTable = '';
	public function __Init(){
		$this->bid = $GLOBALS['_BH_App']->TID;
		$this->Key= array('article_seq', 'seq');
		$this->AddExcept('seq');
		$this->table = TABLE_FIRST.'bbs_'.$this->bid.'_reply';
		$this->boardTable = TABLE_FIRST.'bbs_'.$this->bid;

		$this->InitModelData('seq', ModelTypeInt, false, '');
		$this->data['seq']->AutoDecrement = true;
		$this->InitModelData('sort1', ModelTypeInt, false, '');
		$this->InitModelData('sort2', ModelTypeInt, false, '');
		$this->InitModelData('article_seq', ModelTypeInt, false, '');
		$this->InitModelData('article_sort', ModelTypeInt, false, '');
		$this->InitModelData('depth', ModelTypeInt, false, '');
		$this->InitModelData('mlevel', ModelTypeInt, false, '');
		$this->InitModelData('muid', ModelTypeString, false, '');
		$this->InitModelData('first_seq', ModelTypeInt, false, '');
		$this->InitModelData('first_member_is', ModelTypeEnum, false, '');
		$this->data['first_member_is']->EnumValues = array('y'=>'회원','n'=>'비회원');
		$this->InitModelData('target_muid', ModelTypeString, false, '');
		$this->InitModelData('target_mname', ModelTypeString, false, '');
		$this->InitModelData('reg_date', ModelTypeDatetime, false, '등록일');
		$this->InitModelData('delis', ModelTypeString, false, '삭제여부');
		$this->InitModelData('file', ModelTypeString, false, 'FILE');
		$this->data['delis']->DefaultValue = 'n';

		$this->InitModelData('secret', ModelTypeString, false, '비밀글');
		$this->data['secret']->HtmlType = HTMLInputRadio;
		$this->data['secret']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['secret']->DefaultValue = 'n';

		$this->InitModelData('mname', ModelTypeString, false, '이름');
		$this->data['mname']->HtmlType = HTMLInputText;
		$this->data['mname']->MaxLength = 32;

		$this->InitModelData('pwd', ModelTypePassword, false, '패스워드');
		$this->data['pwd']->HtmlType = HTMLInputPassword;
		$this->data['pwd']->MaxLength = 8;
		$this->data['pwd']->MaxLength = 16;

		$this->InitModelData('comment', ModelTypeString, false, '내용');
		$this->data['comment']->HtmlType = HTMLTextarea;
	}


	// 게시물의 리플 수 갱신
	public function article_count_set($seq){
		$sql = 'UPDATE '.$this->boardTable.' SET reply_cnt = ('
			.'SELECT COUNT(article_seq) FROM '.$this->table.' WHERE article_seq = '.$seq.' AND delis=\'n\''
			.')'
			.'WHERE seq= '.$seq;
		//echo $query;
		SqlQuery($sql);
	}
}
