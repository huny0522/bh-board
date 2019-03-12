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
		if(!strlen($this->bid)) $this->bid = App::$tid;
		$this->key = array('article_seq', 'seq');
		$this->AddExcept('seq');
		$this->table = TABLE_FIRST.'bbs_'.$this->bid.'_reply';
		$this->boardTable = TABLE_FIRST.'bbs_'.$this->bid;

		$this->data['seq'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['sort1'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['sort2'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['article_seq'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['depth'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['mlevel'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['muid'] = new \BH_ModelData(ModelType::STRING, '');
		$this->data['first_seq'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['first_member_is'] = new \BH_ModelData(ModelType::ENUM, '');
		$this->data['first_member_is']->enumValues = array('y' => '회원', 'n' => '비회원');
		$this->data['target_muid'] = new \BH_ModelData(ModelType::STRING, '');
		$this->data['target_mname'] = new \BH_ModelData(ModelType::STRING, '');
		$this->data['reg_date'] = new \BH_ModelData(ModelType::DATETIME, '등록일');
		$this->data['file'] = new \BH_ModelData(ModelType::STRING, 'FILE', HTMLType::FILE_WITH_NAME);
		$this->data['delis'] = new \BH_ModelData(ModelType::STRING, '삭제여부');
		$this->data['delis']->defaultValue = 'n';

		$this->data['secret'] = new \BH_ModelData(ModelType::STRING, '비밀글', HTMLType::RADIO);
		$this->data['secret']->enumValues = array('y' => '사용', 'n' => '사용안함');
		$this->data['secret']->defaultValue = 'n';

		$this->data['mname'] = new \BH_ModelData(ModelType::STRING, '이름');
		$this->data['mname']->maxLength = 32;

		$this->data['pwd'] = new \BH_ModelData(ModelType::STRING, '패스워드', HTMLType::PASSWORD);
		$this->data['pwd']->maxLength = 8;
		$this->data['pwd']->maxLength = 16;

		$this->data['comment'] = new \BH_ModelData(ModelType::TEXT, '내용', HTMLType::TEXTAREA);

		$this->data['recommend'] = new \BH_ModelData(ModelType::INT, '추천수');
		$this->data['report'] = new \BH_ModelData(ModelType::INT, '신고수');
		$this->data['oppose'] = new \BH_ModelData(ModelType::INT, '반대수');
	} // 자동생성불가


	// 게시물의 리플 수 갱신
	public function article_count_set($seq){
		$qry = DB::UpdateQryObj($this->boardTable);
		$qry->SetData('reply_cnt', $qry->StrToPDO('(SELECT COUNT(article_seq) FROM %1 WHERE article_seq = %d AND delis=%s)', $this->table, $seq, 'n'));
		$qry->AddWhere('seq = %d', $seq);
		$qry->Run();
		$this->article_recommend_set($seq, true);
	}

	// 게시물의 리플 최고추천수 갱신
	public function article_recommend_set($seq, $isBoardSeq = false){
		if($isBoardSeq !== true){
			$res = DB::GetQryObj($this->table)
				->SetKey('article_seq')
				->AddWhere('seq = %d', $seq)
				->Get();
			if(!$res) return;
			$seq = $res['article_seq'];
		}
		$qry = DB::UpdateQryObj($this->boardTable);
		$qry->SetData('reply_top_recommend', $qry->StrToPDO('(SELECT MAX(recommend) FROM %1 WHERE article_seq = %d AND delis=%s)', $this->table, $seq, 'n'));
		$qry->SetData('reply_top_report', $qry->StrToPDO('(SELECT MAX(report) FROM %1 WHERE article_seq = %d AND delis=%s)', $this->table, $seq, 'n'));
		$qry->SetData('reply_top_oppose', $qry->StrToPDO('(SELECT MAX(oppose) FROM %1 WHERE article_seq = %d AND delis=%s)', $this->table, $seq, 'n'));
		$qry->AddWhere('seq = %d', $seq);
		$qry->Run();
	}
}
