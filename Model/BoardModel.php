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
		$this->Key[] = 'seq';
		$this->Except = $this->Key;
		if(!strlen($this->bid)) $this->bid = App::$TID;

		$this->table = TABLE_FIRST.'bbs_'.$this->bid;
		$this->imageTable = $this->table.'_images';

		if(!\DB::SQL()->TableExists($this->table)){
			URLReplace(_URL.'/', '존재하지 않는 게시판입니다.');
		}

		$this->data['seq'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['sort1'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['sort2'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['depth'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['muid'] = new \BH_ModelData(ModelType::String, '');
		$this->data['mlevel'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['target_mname'] = new \BH_ModelData(ModelType::String, '');
		$this->data['target_muid'] = new \BH_ModelData(ModelType::String, '');
		$this->data['first_seq'] = new \BH_ModelData(ModelType::Int, '');
		$this->data['first_member_is'] = new \BH_ModelData(ModelType::String, '');
		$this->data['first_member_is']->EnumValues = array('y'=>'회원','n'=>'비회원');
		$this->data['reg_date'] = new \BH_ModelData(ModelType::Datetime, '등록일');
		$this->data['hit'] = new \BH_ModelData(ModelType::Int, '조회수');
		$this->data['recommend'] = new \BH_ModelData(ModelType::Int, '추천수');
		$this->data['report'] = new \BH_ModelData(ModelType::Int, '신고수');
		$this->data['read'] = new \BH_ModelData(ModelType::Int, '회원읽음');
		$this->data['scrap'] = new \BH_ModelData(ModelType::Int, '스크랩수');
		$this->data['oppose'] = new \BH_ModelData(ModelType::Int, '반대수');
		$this->data['reply_cnt'] = new \BH_ModelData(ModelType::Int, '댓글수');
		$this->data['delis'] = new \BH_ModelData(ModelType::String, '삭제여부', HTMLType::InputRadio);
		$this->data['delis']->EnumValues = array('n'=>'미삭제', 'y'=>'삭제');
		$this->data['delis']->DefaultValue = 'n';

		$this->data['htmlis'] = new \BH_ModelData(ModelType::Enum, 'HTML 여부');
		$this->data['htmlis']->DefaultValue = 'n';
		$this->data['htmlis']->EnumValues = array('y'=>'사용','n'=>'사용안함');

		$this->data['email_alarm'] = new \BH_ModelData(ModelType::Enum, '이메일 알림 여부', HTMLType::InputRadio);
		$this->data['email_alarm']->EnumValues = array('y'=>'알림 받음','n'=>'알림 받지 않음');
		$this->data['email_alarm']->DefaultValue = 'y';

		$this->data['email'] = new \BH_ModelData(ModelType::String, '이메일', HTMLType::InputEmail);

		$this->data['notice'] = new \BH_ModelData(ModelType::Enum, '공지글', HTMLType::InputRadio);
		$this->data['notice']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['notice']->DefaultValue = 'n';

		$this->data['category'] = new \BH_ModelData(ModelType::String, '분류');
		$this->data['category']->MaxLength = 128;

		$this->data['sub_category'] = new \BH_ModelData(ModelType::String, '하위분류');
		$this->data['sub_category']->MaxLength = 128;

		$this->data['subid'] = new \BH_ModelData(ModelType::String, '게시판 서브아이디');

		$this->data['secret'] = new \BH_ModelData(ModelType::String, '비밀글', HTMLType::InputRadio);
		$this->data['secret']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['secret']->DefaultValue = 'n';

		$this->data['mname'] = new \BH_ModelData(ModelType::String, '이름');
		$this->data['mname']->MaxLength = 32;
		$this->data['mname']->Required = true;

		$this->data['pwd'] = new \BH_ModelData(ModelType::String, '패스워드', HTMLType::InputPassword);
		$this->data['pwd']->MinLength = 6;
		$this->data['pwd']->MaxLength = 16;
		$this->data['pwd']->Required = true;

		$this->data['subject'] = new \BH_ModelData(ModelType::String, '제목');
		$this->data['subject']->MaxLength = 128;
		$this->data['subject']->Required = true;

		$this->data['content'] = new \BH_ModelData(ModelType::Text, '내용', HTMLType::Textarea);

		$this->data['thumbnail'] = new \BH_ModelData(ModelType::String, '섬네일이미지', HTMLType::InputFile);

		$this->data['file1'] = new \BH_ModelData(ModelType::String, '파일#1', HTMLType::InputFileWithName);

		$this->data['file2'] = new \BH_ModelData(ModelType::String, '파일#2', HTMLType::InputFileWithName);

		$this->data['youtube'] = new \BH_ModelData(ModelType::String, '유튜브');

		$this->data['link1'] = new \BH_ModelData(ModelType::String, '링크#1');

		$this->data['link2'] = new \BH_ModelData(ModelType::String, '링크#2');

		if(method_exists($this, '__Init2')){
			$this->__Init2();
		}
	} // 자동생성불가

	public function GetPrevPage($func = null){
		$sort1 = $this->data['sort1']->txt();
		$sort2 = $this->data['sort2']->txt();
		$qry = DB::GetQryObj($this->table)
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
		$sort1 = $this->data['sort1']->txt();
		$sort2 = $this->data['sort2']->txt();
		$qry = DB::GetQryObj($this->table)
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
		return $this->NewQryName('notice')
			->GetSetListQry('A')
			->AddWhere('A.delis=\'n\'')
			->AddWhere('A.notice=\'y\'')
			->SetSort('A.seq DESC');
	}
}
