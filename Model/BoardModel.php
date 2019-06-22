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
 * Class BoardModel
 *
 * @property BH_ModelData[] $data
 * @property BH_ModelData $_seq
 * @property BH_ModelData $_sort1
 * @property BH_ModelData $_sort2
 * @property BH_ModelData $_depth
 * @property BH_ModelData $_muid
 * @property BH_ModelData $_mlevel
 * @property BH_ModelData $_target_mname
 * @property BH_ModelData $_target_muid
 * @property BH_ModelData $_first_seq
 * @property BH_ModelData $_first_member_is
 * @property BH_ModelData $_reg_date
 * @property BH_ModelData $_hit
 * @property BH_ModelData $_recommend
 * @property BH_ModelData $_report
 * @property BH_ModelData $_read
 * @property BH_ModelData $_scrap
 * @property BH_ModelData $_oppose
 * @property BH_ModelData $_reply_cnt
 * @property BH_ModelData $_reply_top_recommend
 * @property BH_ModelData $_reply_top_oppose
 * @property BH_ModelData $_reply_top_report
 * @property BH_ModelData $_delis
 * @property BH_ModelData $_htmlis
 * @property BH_ModelData $_email_alarm
 * @property BH_ModelData $_email
 * @property BH_ModelData $_notice
 * @property BH_ModelData $_category
 * @property BH_ModelData $_sub_category
 * @property BH_ModelData $_subid
 * @property BH_ModelData $_secret
 * @property BH_ModelData $_mname
 * @property BH_ModelData $_pwd
 * @property BH_ModelData $_subject
 * @property BH_ModelData $_content
 * @property BH_ModelData $_thumbnail
 * @property BH_ModelData $_file1
 * @property BH_ModelData $_file2
 * @property BH_ModelData $_youtube
 * @property BH_ModelData $_link1
 * @property BH_ModelData $_link2
 */

class BoardModel extends \BH_Model
{
	public $imageTable = '';
	public $bid = '';
	public function __Init(){
		$this->key[] = 'seq';
		$this->except = $this->key;
		if(!strlen($this->bid)) $this->bid = App::$tid;

		$this->table = TABLE_FIRST.'bbs_'.$this->bid;
		$this->imageTable = $this->table.'_images';

		if(!\DB::SQL($this->connName)->TableExists($this->table)){
			URLReplace(\Paths::Url().'/', '존재하지 않는 게시판입니다.');
		}

		$this->data['seq'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['sort1'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['sort2'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['depth'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['muid'] = new \BH_ModelData(ModelType::STRING, '');
		$this->data['mlevel'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['target_mname'] = new \BH_ModelData(ModelType::STRING, '');
		$this->data['target_muid'] = new \BH_ModelData(ModelType::STRING, '');
		$this->data['first_seq'] = new \BH_ModelData(ModelType::INT, '');
		$this->data['first_member_is'] = new \BH_ModelData(ModelType::STRING, '');
		$this->data['first_member_is']->enumValues = array('y'=>'회원','n'=>'비회원');
		$this->data['reg_date'] = new \BH_ModelData(ModelType::DATETIME, '등록일');
		$this->data['hit'] = new \BH_ModelData(ModelType::INT, '조회수');
		$this->data['recommend'] = new \BH_ModelData(ModelType::INT, '추천수');
		$this->data['report'] = new \BH_ModelData(ModelType::INT, '신고수');
		$this->data['read'] = new \BH_ModelData(ModelType::INT, '회원읽음');
		$this->data['scrap'] = new \BH_ModelData(ModelType::INT, '스크랩수');
		$this->data['oppose'] = new \BH_ModelData(ModelType::INT, '반대수');
		$this->data['reply_cnt'] = new \BH_ModelData(ModelType::INT, '댓글수');
		$this->data['reply_top_recommend'] = new \BH_ModelData(ModelType::INT, '댓글 최고 추천수');
		$this->data['reply_top_oppose'] = new \BH_ModelData(ModelType::INT, '댓글 최고 반대수');
		$this->data['reply_top_report'] = new \BH_ModelData(ModelType::INT, '댓글 최고 신고수');
		$this->data['delis'] = new \BH_ModelData(ModelType::STRING, '삭제여부', HTMLType::RADIO);
		$this->data['delis']->enumValues = array('n'=>'미삭제', 'y'=>'삭제');
		$this->data['delis']->defaultValue = 'n';

		$this->data['htmlis'] = new \BH_ModelData(ModelType::ENUM, 'HTML 여부');
		$this->data['htmlis']->defaultValue = 'n';
		$this->data['htmlis']->enumValues = array('y'=>'사용','n'=>'사용안함');

		$this->data['email_alarm'] = new \BH_ModelData(ModelType::ENUM, '이메일 알림 여부', HTMLType::RADIO);
		$this->data['email_alarm']->enumValues = array('y'=>'알림 받음','n'=>'알림 받지 않음');
		$this->data['email_alarm']->defaultValue = 'y';

		$this->data['email'] = new \BH_ModelData(ModelType::STRING, '이메일', HTMLType::EMAIL);

		$this->data['notice'] = new \BH_ModelData(ModelType::ENUM, '공지글', HTMLType::RADIO);
		$this->data['notice']->enumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['notice']->defaultValue = 'n';

		$this->data['category'] = new \BH_ModelData(ModelType::STRING, '분류');
		$this->data['category']->maxLength = 128;

		$this->data['sub_category'] = new \BH_ModelData(ModelType::STRING, '하위분류');
		$this->data['sub_category']->maxLength = 128;

		$this->data['subid'] = new \BH_ModelData(ModelType::STRING, '게시판 서브아이디');

		$this->data['secret'] = new \BH_ModelData(ModelType::STRING, '비밀글', HTMLType::RADIO);
		$this->data['secret']->enumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['secret']->defaultValue = 'n';

		$this->data['mname'] = new \BH_ModelData(ModelType::STRING, '이름');
		$this->data['mname']->maxLength = 32;
		$this->data['mname']->required = true;

		$this->data['pwd'] = new \BH_ModelData(ModelType::STRING, '패스워드', HTMLType::PASSWORD);
		$this->data['pwd']->minLength = 6;
		$this->data['pwd']->maxLength = 16;
		$this->data['pwd']->required = true;

		$this->data['subject'] = new \BH_ModelData(ModelType::STRING, '제목');
		$this->data['subject']->maxLength = 128;
		$this->data['subject']->required = true;

		$this->data['content'] = new \BH_ModelData(ModelType::TEXT, '내용', HTMLType::TEXTAREA);

		$this->data['thumbnail'] = new \BH_ModelData(ModelType::STRING, '섬네일이미지', HTMLType::FILE);

		$this->data['file1'] = new \BH_ModelData(ModelType::STRING, '파일#1', HTMLType::FILE_WITH_NAME);

		$this->data['file2'] = new \BH_ModelData(ModelType::STRING, '파일#2', HTMLType::FILE_WITH_NAME);

		$this->data['youtube'] = new \BH_ModelData(ModelType::STRING, '유튜브');

		$this->data['link1'] = new \BH_ModelData(ModelType::STRING, '링크#1');

		$this->data['link2'] = new \BH_ModelData(ModelType::STRING, '링크#2');

		if(method_exists($this, '__Init2')){
			$this->__Init2();
		}
	} // 자동생성불가

	public function GetPrevPage($func = null){
		$sort1 = $this->data['sort1']->Txt();
		$sort2 = $this->data['sort2']->Txt();
		$qry = DB::GetQryObj($this->table)
			->SetConnName($this->connName)
			->AddWhere('secret = \'n\'')
			->AddWhere('delis = \'n\'')
			->AddWhere('(sort1 = %d AND sort2 > %d) OR sort1 > %d', $sort1, $sort2, $sort1)
			->SetSort('sort1 ASC, sort2 ASC')
			->SetKey('seq, subject');
		if(is_callable($func)) $func($qry);
		$data = $qry->Get();
		if($data) $data['linkUrl'] = App::URLAction('View/' . toBase($data['seq'])) . App::GetFollowQuery();

		return $data;
	}

	public function GetNextPage($func = null){
		$sort1 = $this->data['sort1']->Txt();
		$sort2 = $this->data['sort2']->Txt();
		$qry = DB::GetQryObj($this->table)
			->SetConnName($this->connName)
			->AddWhere('secret = \'n\'')
			->AddWhere('delis = \'n\'')
			->AddWhere('(sort1 = %d AND sort2 < %d) OR sort1 < %d', $sort1, $sort2, $sort1)
			->SetSort('sort1 DESC, sort2 DESC')
			->SetKey('seq, subject');
		if(is_callable($func)) $func($qry);
		$data = $qry->Get();
		if($data) $data['linkUrl'] = App::URLAction('View/' . toBase($data['seq'])) . App::GetFollowQuery();

		return $data;
	}

	/**
	* @return BH_DB_GetList
	*/
	public function GetNoticeQuery(){
		return DB::GetListQryObj($this->table . ' A')
			->SetConnName($this->connName)
			->AddWhere('A.delis=\'n\'')
			->AddWhere('A.notice=\'y\'')
			->SetSort('A.seq DESC');
	}

	/**
	 * 추천수나 신고수등이 높은 게시물 쿼리 반환
	 *
	 * @param string $type <'hit', 'recommend', 'report', 'read', 'scrap', 'oppose', 'reply_cnt'>
	 * @param int $day
	 * @param callable $func
	 * @return BH_DB_GetList
	 */
	public function GetHighArticleQuery($type = 'recommend', $day = 1, $func = null){
		$qry = DB::GetListQryObj($this->table . ' A')
			->SetConnName($this->connName)
			->AddWhere('`A`.`delis`=\'n\'')
			->AddWhere('`A`.`reg_date` >= %s', date('Y-m-d H:i:s', strtotime('-' . $day . ' day', time())))
			->SetSort('`A`.`%1` DESC', $type);

		if(is_callable($func)) $func($qry);
		return $qry;
	}
}
