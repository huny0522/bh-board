<?php

namespace Common;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class ArticleAction
{
	protected $table = '';
	protected $parentTable = null;
	protected $mUid = null;
	protected $articleSeq = null;
	protected $replyIs = false;
	protected $type = '';
	protected $actionDBType = '';

	protected $connName = '';

	public $boardActionType = array('read', 'recommend', 'oppose', 'report', 'scrap');
	public $replyActionType = array('rp_recommend', 'rp_oppose', 'rp_report');

	/**
	 * Recommend constructor.
	 * @param string $bid
	 * @param bool $fullTableName
	 */
	private function __construct($bid, $fullTableName = false){
		$this->connName = DB::DefaultConnName;
		$this->table = $fullTableName ? $bid : TABLE_FIRST.'bbs_'.$bid.'_action';
	}

	protected function QueryGet($table){
		return DB::GetQryObj($table)->SetConnName($this->connName);
	}

	protected function QueryGetList($table){
		return DB::GetListQryObj($table)->SetConnName($this->connName);
	}

	protected function QueryDelete($table){
		return DB::DeleteQryObj($table)->SetConnName($this->connName);
	}

	protected function QueryUpdate($table){
		return DB::UpdateQryObj($table)->SetConnName($this->connName);
	}

	protected function QueryInsert($table){
		return DB::InsertQryObj($table)->SetConnName($this->connName);
	}

	/**
	 * @param string $bid
	 * @param bool $fullTableName
	 * @return ArticleAction
	 */
	public static function GetInstance($bid, $fullTableName = false){
		$static = new static($bid, $fullTableName);
		return $static;
	}

	/**
	 * @param string $connName
	 * @return ArticleAction
	 */
	public function SetConnName($connName){
		$this->connName = $connName;
		return $this;
	}

	/**
	 * @param string $bid
	 * @param bool $fullTableName
	 * @return ArticleAction
	 */
	public function SetParentTable($bid, $fullTableName = false){
		$this->parentTable = $fullTableName ? $bid : TABLE_FIRST.'bbs_'.$bid;
		return $this;
	}

	/**
	 * @return \BH_Result
	 */
	public function GetAllAction(){
		$sc = $this->SettingCheck();
		if(!$sc->result) return $sc;

		$data = array();

		$qry = $this->QueryGetList($this->table)
			->AddWhere('`article_seq` = %d', $this->articleSeq)
			->AddWhere('`action_type` IN (%s)', $this->boardActionType)
			->AddWhere('`muid` = %d', $this->mUid);
		while($row = $qry->Get()){
			$data[$row['action_type']] = true;
		}

		return \BH_Result::Init(true, '', $data);
	}


	public function GetReplyActions($articleSeqArr){
		if(!is_numeric($this->mUid)) return \BH_Result::Init(false, App::$lang['MEMBER_NUM_HAS_NOT_NUM']);

		$data = array();

		if(is_array($articleSeqArr) && sizeof($articleSeqArr)){
			$qry = $this->QueryGetList($this->table)
				->AddWhere('`article_seq` IN (%d)', $articleSeqArr)
				->AddWhere('`action_type` IN (%s)', $this->replyActionType)
				->AddWhere('`muid` = %d', $this->mUid);
			while($row = $qry->Get()){
				$data[$row['article_seq']][$row['action_type']] = true;
			}
		}

		return \BH_Result::Init(true, '', $data);
	}

	/**
	 * @param int $muid
	 * @return $this
	 */
	public function SetMUid($muid){
		$this->mUid = $muid;
		return $this;
	}

	/**
	 * @param int $seq
	 * @return $this
	 */
	public function SetArticleSeq($seq){
		$this->articleSeq = $seq;
		return $this;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function SetReplyIs($bool = true){
		$this->replyIs = $bool;
		return $this;
	}

	/**
	 * 필수 설정항목
	 * @return \BH_Result
	 */
	protected function SettingCheck(){
		if(!is_numeric($this->mUid)) return \BH_Result::Init(false, App::$lang['MEMBER_NUM_HAS_NOT_NUM']);
		if(!is_numeric($this->articleSeq)) return \BH_Result::Init(false, App::$lang['POST_NUM_HAS_NOT_NUM']);
		return \BH_Result::Init(true);
	}

	/**
	 * 읽음
	 * @return \BH_Result
	 */
	public function Read(){
		$sc = $this->SettingCheck();
		if(!$sc->result) return $sc;

		$this->type = 'read';
		$res = $this->DBCheck();
		if($res->result && !$res->message) $res = $this->RunInsert();
		return $res;
	}

	/**
	 * 추천
	 * @return \BH_Result
	 */
	public function Recommend(){
		$sc = $this->SettingCheck();
		if(!$sc->result) return $sc;

		$this->type = 'recommend';
		$res = $this->DBCheck();
		if($res->result && !$res->message) $res = $this->RunInsert();
		return $res;
	}

	/**
	 * 반대
	 * @return \BH_Result
	 */
	public function Oppose(){
		$sc = $this->SettingCheck();
		if(!$sc->result) return $sc;

		$this->type = 'oppose';
		$res = $this->DBCheck();
		if($res->result && !$res->message) $res = $this->RunInsert();
		return $res;
	}

	/**
	 * 신고
	 * @return \BH_Result
	 */
	public function Report(){
		$sc = $this->SettingCheck();
		if(!$sc->result) return $sc;

		$this->type = 'report';
		$res = $this->DBCheck();
		if($res->result && !$res->message) $res = $this->RunInsert();
		return $res;

	}

	/**
	 * 스크랩
	 * @return \BH_Result
	 */
	public function Subscribe(){
		$sc = $this->SettingCheck();
		if(!$sc->result) return $sc;

		$this->type = 'scrap';
		$res = $this->DBCheck();
		if($res->result && !$res->message) $res = $this->RunInsert();
		return $res;
	}

	/**
	 * 추천 취소
	 * @return \BH_Result
	 */
	public function CancelRecommend(){
		$sc = $this->SettingCheck();
		if(!$sc->result) return $sc;

		$this->type = 'recommend';
		return $this->DBDelete();
	}

	/**
	 * 반대 취소
	 * @return \BH_Result
	 */
	public function CancelOppose(){
		$sc = $this->SettingCheck();
		if(!$sc->result) return $sc;

		$this->type = 'oppose';
		return $this->DBDelete();
	}

	/**
	 * 신고 취소
	 * @return \BH_Result
	 */
	public function CancelReport(){
		$sc = $this->SettingCheck();
		if(!$sc->result) return $sc;

		$this->type = 'report';
		return $this->DBDelete();
	}

	/**
	 * 스크랩 취소
	 * @return \BH_Result
	 */
	public function CancelSubscribe(){
		$sc = $this->SettingCheck();
		if(!$sc->result) return $sc;

		$this->type = 'scrap';
		return $this->DBDelete();
	}

	/**
	 * 중복 행동 체크
	 * @return \BH_Result
	 */
	protected function DBCheck(){
		$res = $this->TypeCheck();
		if(!$res->result) return $res;


		$res = $this->DBCheckQry()->Get();
		if($res){
			return \BH_Result::Init(false, '', $res['action_type']);
		}

		return \BH_Result::Init(true);
	}

	/**
	 * 중복 행동 체크 쿼리
	 * 쿼리를 반환하는 이유는 해당 쿼리로 추후 가공이 가능하도록 하기 위함.
	 * @return \BH_DB_Get
	 */
	protected function DBCheckQry(){
		$qry = $this->QueryGet($this->table)
			->AddWhere('`muid` = %d', $this->mUid)
			->AddWhere('`article_seq` = %d', $this->articleSeq);

		// 아래 타입은 한가지만 가능
		$onlyOne = $this->replyIs ? array('rp_recommend', 'rp_oppose', 'rp_report') : array('recommend', 'oppose', 'report');


		if($this->type === 'read' || $this->type == 'scrap') $qry->AddWhere('`action_type` = %s', $this->actionDBType);
		else if(in_array($this->actionDBType, $onlyOne)) $qry->AddWhere('`action_type` IN (%s)', $onlyOne);
		return $qry;
	}

	/**
	 * 행동 기록 쿼리 반환.
	 * 쿼리를 반환하는 이유는 해당 쿼리로 추후 가공이 가능하도록 하기 위함.
	 * @return \BH_DB_Insert
	 */
	protected function InsertQuery(){
		return $this->QueryInsert($this->table)
			->SetDataNum('muid', $this->mUid)
			->SetDataNum('article_seq', $this->articleSeq)
			->SetDataStr('action_type', $this->actionDBType)
			->SetDataStr('reg_date', date('Y-m-d H:i:s'));
	}

	/**
	 * 해당 게시물에 행동 갯수를 업데이트 쿼리 반환.
	 * 쿼리를 반환하는 이유는 해당 쿼리로 추후 가공이 가능하도록 하기 위함.
	 * @return \BH_DB_Update|null
	 */
	protected function UpdateTableQuery(){
		if(!$this->parentTable) return null;
		$qry = $this->QueryUpdate($this->parentTable)
			->AddWhere('`seq` = %d', $this->articleSeq);
		$sql = $qry->StrToPDO('(SELECT COUNT(*) FROM %1 WHERE `action_type` = %s AND `article_seq` = %d)', $this->table, $this->actionDBType, $this->articleSeq);
		$qry->SetData($this->type, $sql);
		return $qry;
	}

	/**
	 * 행동 기록 실행.
	 * @return \BH_InsertResult|\BH_Result
	 */
	protected function RunInsert(){
		if(!$this->actionDBType) return \BH_Result::Init(false, App::$lang['DUPLICATE_CHECK_IS_REQUIRED']);
		$res = $this->InsertQuery()->Run();
		if($res->result) $this->RunUpdateTable();
		else if(!$res->message) $res->message = App::$lang['ERROR_INSERT_DB'];
		return $res;
	}

	/**
	 * 해당 게시물에 행동 갯수를 업데이트 실행.
	 * @return \BH_InsertResult|\BH_Result
	 */
	protected function RunUpdateTable(){
		if(!$this->actionDBType) return \BH_Result::Init(false, App::$lang['DUPLICATE_CHECK_IS_REQUIRED']);
		$qry = $this->UpdateTableQuery();
		if(!is_null($qry)) return $qry->Run();
		return null;
	}



	/**
	 * 행동 삭제 삭제.
	 * @return \BH_Result
	 */
	protected function DBDelete(){
		$res = $this->TypeCheck();
		if(!$res->result) return $res;

		if(in_array($this->type, array('recommend', 'oppose', 'report'))){
			$data = $this->QueryGet($this->table)
				->AddWhere('`muid` = %d', $this->mUid)
				->AddWhere('`article_seq` = %d', $this->articleSeq)
				->AddWhere('`action_type` = %s', $this->actionDBType)
				->SetKey('reg_date')
				->Get();

			if(!$data) return \BH_Result::Init(true, '', $this->actionDBType);

			if($data['reg_date'] <= date('Y-m-d H:i:s', strtotime('-1 day', time()))){
				return \BH_Result::Init(false, App::$lang['CANT_CANCEL_AFTER_A_DAY']);
			}
		}

		$res = $this->QueryDelete($this->table)
			->AddWhere('`muid` = %d', $this->mUid)
			->AddWhere('`article_seq` = %d', $this->articleSeq)
			->AddWhere('`action_type` = %s', $this->actionDBType)
			->Run();
		if(!$res) return \BH_Result::Init(false, App::$lang['ERROR_CANCEL_DB']);

		$this->RunUpdateTable();

		return \BH_Result::Init(true, '', $this->actionDBType);
	}

	/**
	 * 타입을 체크하고 DB에 들어갈 타입을 설정.
	 * @return \BH_Result
	 */
	protected function TypeCheck(){
		$this->actionDBType = $this->type;
		if($this->type === 'read'){
			if($this->replyIs === true) return \BH_Result::Init(false, App::$lang['COMMENT_NO_READ']);
		}
		else if($this->type === 'scrap'){
			if($this->replyIs === true) return \BH_Result::Init(false, App::$lang['COMMENT_NO_SCRAP']);
		}
		else if($this->type === 'recommend') $this->actionDBType = $this->replyIs ? 'rp_recommend' : 'recommend';
		else if($this->type === 'oppose') $this->actionDBType = $this->replyIs ? 'rp_oppose' : 'oppose';
		else if($this->type === 'report') $this->actionDBType = $this->replyIs ? 'rp_report' : 'report';
		else return \BH_Result::Init(false, App::$lang['UNKNOWN_TYPE']);
		return \BH_Result::Init(true);
	}

}