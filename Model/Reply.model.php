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

		$this->InitModelData('seq', ModelType::Int, false, '');
		$this->data['seq']->AutoDecrement = true;
		$this->InitModelData('sort1', ModelType::Int, false, '');
		$this->InitModelData('sort2', ModelType::Int, false, '');
		$this->InitModelData('article_seq', ModelType::Int, false, '');
		$this->InitModelData('article_sort', ModelType::Int, false, '');
		$this->InitModelData('depth', ModelType::Int, false, '');
		$this->InitModelData('mlevel', ModelType::Int, false, '');
		$this->InitModelData('muid', ModelType::String, false, '');
		$this->InitModelData('first_seq', ModelType::Int, false, '');
		$this->InitModelData('first_member_is', ModelType::Enum, false, '');
		$this->data['first_member_is']->EnumValues = array('y'=>'회원','n'=>'비회원');
		$this->InitModelData('target_muid', ModelType::String, false, '');
		$this->InitModelData('target_mname', ModelType::String, false, '');
		$this->InitModelData('reg_date', ModelType::Datetime, false, '등록일');
		$this->InitModelData('delis', ModelType::String, false, '삭제여부');
		$this->InitModelData('file', ModelType::String, false, 'FILE');
		$this->data['delis']->DefaultValue = 'n';

		$this->InitModelData('secret', ModelType::String, false, '비밀글');
		$this->data['secret']->HtmlType = HTMLType::InputRadio;
		$this->data['secret']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['secret']->DefaultValue = 'n';

		$this->InitModelData('mname', ModelType::String, false, '이름');
		$this->data['mname']->HtmlType = HTMLType::InputText;
		$this->data['mname']->MaxLength = 32;

		$this->InitModelData('pwd', ModelType::Password, false, '패스워드');
		$this->data['pwd']->HtmlType = HTMLType::InputPassword;
		$this->data['pwd']->MaxLength = 8;
		$this->data['pwd']->MaxLength = 16;

		$this->InitModelData('comment', ModelType::String, false, '내용');
		$this->data['comment']->HtmlType = HTMLType::Textarea;
	}


	// 게시물의 리플 수 갱신
	public function article_count_set($seq){
		$qry = new \BH_DB_Update($this->boardTable);
		$qry->SetData('reply_cnt', StrToSql('(SELECT COUNT(article_seq) FROM %1 WHERE article_seq = %d AND delis=%s)', $this->table, $seq, 'n'));
		$qry->AddWhere('seq = %d', $seq);
		$qry->Run();
	}
}
