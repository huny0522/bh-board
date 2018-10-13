<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */

use \BH_Common as CM;
use \BH_Application as App;

/**
 * Class ReplyModel
 *
 * @property BH_ModelData[] $data
 * @property BH_ModelData $_seq
 * @property BH_ModelData $_sort1
 * @property BH_ModelData $_sort2
 * @property BH_ModelData $_article_seq
 * @property BH_ModelData $_depth
 * @property BH_ModelData $_mlevel
 * @property BH_ModelData $_muid
 * @property BH_ModelData $_first_seq
 * @property BH_ModelData $_first_member_is
 * @property BH_ModelData $_target_muid
 * @property BH_ModelData $_target_mname
 * @property BH_ModelData $_reg_date
 * @property BH_ModelData $_file
 * @property BH_ModelData $_delis
 * @property BH_ModelData $_secret
 * @property BH_ModelData $_mname
 * @property BH_ModelData $_pwd
 * @property BH_ModelData $_comment
 * @property BH_ModelData $_recommend
 * @property BH_ModelData $_report
 * @property BH_ModelData $_oppose
 */
class ReplyModel extends \BH_Model
{
	public $bid = '';
	public $boardTable = '';
	public function __Init(){
		if(!strlen($this->bid)) $this->bid = App::$TID;
		$this->Key= array('article_seq', 'seq');
		$this->AddExcept('seq');
		$this->table = TABLE_FIRST.'bbs_'.$this->bid.'_reply';
		$this->boardTable = TABLE_FIRST.'bbs_'.$this->bid;

		$this->data['seq'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['sort1'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['sort2'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['article_seq'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['depth'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['mlevel'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['muid'] = new \BH_ModelData(ModelType::String, '');
		$this->data['first_seq'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['first_member_is'] = new \BH_ModelData(ModelType::Enum, '');
		$this->data['first_member_is']->EnumValues = array('y'=>'회원','n'=>'비회원');
		$this->data['target_muid'] = new \BH_ModelData(ModelType::String, '');
		$this->data['target_mname'] = new \BH_ModelData(ModelType::String, '');
		$this->data['reg_date'] = new \BH_ModelData(ModelType::Datetime, '등록일');
		$this->data['file'] = new \BH_ModelData(ModelType::String, 'FILE', HTMLType::InputFileWithName);
		$this->data['delis'] = new \BH_ModelData(ModelType::String, '삭제여부');
		$this->data['delis']->DefaultValue = 'n';

		$this->data['secret'] = new \BH_ModelData(ModelType::String, '비밀글', HTMLType::InputRadio);
		$this->data['secret']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['secret']->DefaultValue = 'n';

		$this->data['mname'] = new \BH_ModelData(ModelType::String, '이름');
		$this->data['mname']->MaxLength = 32;

		$this->data['pwd'] = new \BH_ModelData(ModelType::String, '패스워드', HTMLType::InputPassword);
		$this->data['pwd']->MaxLength = 8;
		$this->data['pwd']->MaxLength = 16;

		$this->data['comment'] = new \BH_ModelData(ModelType::Text, '내용', HTMLType::Textarea);

		$this->data['recommend'] = new \BH_ModelData(ModelType::Int, '추천수');
		$this->data['report'] = new \BH_ModelData(ModelType::Int, '신고수');
		$this->data['oppose'] = new \BH_ModelData(ModelType::Int, '반대수');
	} // 자동생성불가


	// 게시물의 리플 수 갱신
	public function article_count_set($seq){
		$qry = new \BH_DB_Update($this->boardTable);
		$qry->SetData('reply_cnt', StrToSql('(SELECT COUNT(article_seq) FROM %1 WHERE article_seq = %d AND delis=%s)', $this->table, $seq, 'n'));
		$qry->AddWhere('seq = %d', $seq);
		$qry->Run();
	}
}
