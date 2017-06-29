<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */

use \BH_Common as CM;
use \BH_Application as App;

class ReplyModel extends \BH_Model{
	public $bid = '';
	public $boardTable = '';
	public function __Init(){
		if(!strlen($this->bid)) $this->bid = App::$TID;
		$this->Key= array('article_seq', 'seq');
		$this->AddExcept('seq');
		$this->table = TABLE_FIRST.'bbs_'.$this->bid.'_reply';
		$this->boardTable = TABLE_FIRST.'bbs_'.$this->bid;

		$this->data['seq'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['seq']->AutoDecrement = true;
		$this->data['sort1'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['sort2'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['article_seq'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['article_sort'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['depth'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['mlevel'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['muid'] = new \BH_ModelData(ModelType::String, false, '');
		$this->data['first_seq'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['first_member_is'] = new \BH_ModelData(ModelType::Enum, false, '');
		$this->data['first_member_is']->EnumValues = array('y'=>'회원','n'=>'비회원');
		$this->data['target_muid'] = new \BH_ModelData(ModelType::String, false, '');
		$this->data['target_mname'] = new \BH_ModelData(ModelType::String, false, '');
		$this->data['reg_date'] = new \BH_ModelData(ModelType::Datetime, false, '등록일');
		$this->data['file'] = new \BH_ModelData(ModelType::String, false, 'FILE');
		$this->data['delis'] = new \BH_ModelData(ModelType::String, false, '삭제여부');
		$this->data['delis']->DefaultValue = 'n';

		$this->data['secret'] = new \BH_ModelData(ModelType::String, false, '비밀글', HTMLType::InputRadio);
		$this->data['secret']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['secret']->DefaultValue = 'n';

		$this->data['mname'] = new \BH_ModelData(ModelType::String, false, '이름');
		$this->data['mname']->MaxLength = 32;

		$this->data['pwd'] = new \BH_ModelData(ModelType::String, false, '패스워드', HTMLType::InputPassword);
		$this->data['pwd']->MaxLength = 8;
		$this->data['pwd']->MaxLength = 16;

		$this->data['comment'] = new \BH_ModelData(ModelType::String, false, '내용', HTMLType::Textarea);
	}


	// 게시물의 리플 수 갱신
	public function article_count_set($seq){
		$qry = new \BH_DB_Update($this->boardTable);
		$qry->SetData('reply_cnt', StrToSql('(SELECT COUNT(article_seq) FROM %1 WHERE article_seq = %d AND delis=%s)', $this->table, $seq, 'n'));
		$qry->AddWhere('seq = %d', $seq);
		$qry->Run();
	}
}
