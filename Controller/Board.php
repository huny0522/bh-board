<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use Common\ArticleAction;
use Common\MenuHelp;
use Custom\Email;
use \DB as DB;

class Board{
	/**
	 * @var \BoardModel
	 */
	public $model;
	/**
	 * @var \BoardManagerModel
	 */
	public $boardManger;
	public $managerIs = false;
	public $MoreListIs = false;
	public $GetListIs = false;
	public $Path = '';
	public $AdminPathIs = false;
	public $bid = '';
	public $subid = '';
	public $additionalSubId = array();
	public $menuCategory = '';
	public $menuSubCategory = '';
	public $uploadDir = '';
	public $uploadImageDir = '';
	public static $loginUrl = _DEFAULT_BOARD_LOGIN_URL;

	public $userActionTable;

	/** @param \BH_DB_GetListWithPage $qry */
	protected function _R_GetListQuery(&$qry){}
	/** @param \BH_DB_GetList $qry */
	protected function _R_MoreListQuery(&$qry){}
	/** @param \BH_DB_GetList $qry */
	protected function _R_NoticeQuery(&$qry){}
	/** @param array $data */
	protected function _R_ViewEnd($data){}
	protected function _R_WriteEnd(){}
	protected function _R_AnswerEnd(){}
	protected function _R_ModifyEnd(){}
	protected function _R_PostModifyUpdateBefore(){}
	protected function _R_PostModifyUpdateAfter(){}
	protected function _R_PostWriteInsertBefore(){}
	protected function _R_PostWriteInsertAfter($insertId){}
	protected function _R_CommonQry(&$qry, $opt = null){}
	/** @param \BH_Model */
	protected function _R_CheckArticleCopyInsBefore($model){}
	protected function _R_CheckArticleCopyInsAfter($type, $before_article_seq, $before_bid, $before_subid, $after_article_seq, $after_bid, $after_subid){}
	protected function _R_CheckArticleReplyCopyInsBefore($model){}
	protected function _R_CheckArticleReplyCopyInsAfter($type, $before_reply_seq, $before_bid, $before_subid, $after_reply_seq, $after_bid, $after_subid){}
	protected function _R_CheckArticleRemoveAfter($article_seq, $bid, $subid){}


	public function __construct(){
		$this->_AdminPathAuth();
		$this->_ModelInit('Board');

		$this->_IdSet();
		$this->_DirSet();
	}

	public function __init(){
		$this->boardManger->DBGet($this->bid, $this->subid);
		$this->_BoardSetting();
	}

	/**
	 * 관리자 경로일때 권한 체크
	 */
	protected function _AdminPathAuth(){
		if(App::$SettingData['GetUrl'][1] == _ADMINURLNAME){
			if(CM::GetAdminIs()) $this->AdminPathIs = true;
			else{
				if(_JSONIS === true) JSON(false, _MSG_WRONG_CONNECTED);
				URLReplace(App::URLBase('Login'), _MSG_WRONG_CONNECTED);
			}
		}
	}

	protected function _ModelInit($modelName, $tid = ''){
		if(strlen($tid)) App::$TID = $tid;
		if(substr($modelName, -5) === 'Model') $modelName = substr($modelName, 0, -5);
		$this->model = App::InitModel($modelName);
		$this->boardManger = new \BoardManagerModel();
	}

	protected function _IdSet(){
		$this->bid = App::$TID;
		$this->subid = App::$SUB_TID;

		if(isset(App::$SettingData['additionalSubid'])){
			if(is_string(App::$SettingData['additionalSubid']) && strlen(App::$SettingData['additionalSubid'])){
				App::$SettingData['additionalSubid'] = explode(',', App::$SettingData['additionalSubid']);
			}
			if(is_array(App::$SettingData['additionalSubid']) && sizeof(App::$SettingData['additionalSubid'])){
				$this->additionalSubId = App::$SettingData['additionalSubid'];
				$this->additionalSubId[] = $this->subid;
			}
		}

		if(sizeof($this->additionalSubId)){
			if(!in_array(App::$Action, array('Index', 'MoreList', 'Write')) && strlen(App::$ID)){
				$dt = DB::GetQryObj($this->model->table)
					->SetKey('subid')
					->AddWhere('seq = %d', to10(App::$ID))
					->Get();
				if(isset($dt['subid']) && strlen($dt['subid'])) $this->subid = $dt['subid'];
			}

			else if(App::$Action === 'Write' && !EmptyGet('subid')){
				$this->subid = Get('subid');
			}
		}

		if(isset(App::$SettingData['boardCategory'])) $this->menuCategory = trim(App::$SettingData['boardCategory']);

		if(isset(App::$SettingData['boardSubCategory'])) $this->menuSubCategory = trim(App::$SettingData['boardSubCategory']);

		$this->userActionTable = $this->model->table . '_action';
	}

	protected function _DirSet(){
		$this->uploadDir = '/board/'.$this->bid.(strlen($this->subid) ? '-' . $this->subid  : '').'/'.date('ym').'/';
		$this->uploadImageDir = '/boardimage/'.$this->bid.(strlen($this->subid) ? '-' . $this->subid  : '').'/'.date('ym').'/';
		$this->model->uploadDir = $this->uploadDir;
	}

	protected function _BoardSetting(){
		if(!isset($this->bid) || $this->bid == '') URLReplace('-1', '잘못된 접근입니다.');

		App::SetFollowQuery(array('page','stype','keyword','cate','lastSeq','scate'));

		if(CM::GetAdminIs()) $this->managerIs = true;
		else{
			$mid = CM::GetMember('mid');
			$manager = explode(',', $this->boardManger->GetValue('manager'));
			if ($mid != '' && $mid !== false && in_array($mid, $manager)) {
				$this->managerIs = true;
			}
		}

		if($this->boardManger->GetValue('attach_type') == 'image'){
			$this->model->data['file1']->HtmlType = \HTMLType::InputImageFile;
			$this->model->data['file2']->HtmlType = \HTMLType::InputImageFile;
		}

		$action = App::$Action;
		if($action == 'Answer' || $action == 'Modify') $action = 'Write';
		if($action == '_DirectView') $action = 'View';
		$this->Path = '/Board/'.App::$NativeSkinDir.'/'.$this->boardManger->GetValue('skin').'/';
		if(file_exists(_SKINDIR.$this->Path.$action.'.html')) App::$Html = $this->Path.$action.'.html';
		else{
			$this->Path = '/Board/'.App::$NativeSkinDir.'/';
			if(file_exists(_SKINDIR.$this->Path.$action.'.html')) App::$Html = $this->Path.$action.'.html';
			else{
				$this->Path = '/Board/'.$this->boardManger->GetValue('skin').'/';
				if(file_exists(_SKINDIR.$this->Path.$action.'.html')) App::$Html = $this->Path.$action.'.html';
				else{
					$this->Path = '/Board/';
					App::$Html = '/Board/' . $action.'.html';
				}
			}
		}

		if(file_exists(_SKINDIR.$this->Path.'MoreList.html')) $this->MoreListIs = true;
		else if(file_exists(_SKINDIR.$this->Path.'GetList.html')) $this->GetListIs = true;

		$layout = $this->boardManger->GetValue('layout');

		// 관리자
		if($this->AdminPathIs){
			$this->Path = '/Board/Admin/'.$this->boardManger->GetValue('skin').'/';
			if(!file_exists(_SKINDIR.$this->Path.$action.'.html')) $this->Path = '/Board/Admin/';

			App::$Html = $this->Path . $action.'.html';
			App::$Layout = '_Admin';
			$this->MoreListIs = false;
			$this->GetListIs = false;
			$this->boardManger->SetValue('article_count', 20);
		}
		else if($layout){
			$layoutPath = App::$NativeSkinDir.'/'.$layout;

			$e = explode('.', $layoutPath);
			if(sizeof($e) > 1){
				$ext = array_pop($e);
				if($ext !== 'html' && $ext !== 'php') $layoutPath = implode('.', $e) . '.html';
			}
			else{
				$layoutPath .= '.html';
			}

			if(file_exists(_SKINDIR.'/Layout/'.$layoutPath)) $layout = $layoutPath;
			App::$Layout = $layout;
		}

		$this->_SetCategory();

		if(!$this->AdminPathIs && _MEMBERIS !== true && $this->boardManger->GetValue('man_to_man') === 'y') URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
		if($this->boardManger->GetValue('man_to_man') === 'y') $this->boardManger->SetValue('use_secret', 'n');
	}

	public function _SetCategory(){
		App::$Data['category'] = array();
		App::$Data['subCategory'] = array();

		if(strlen($this->menuCategory)) App::$Data['subCategory'] =  $this->boardManger->GetSubCategory($this->menuCategory);

		else if(!EmptyGet('cate')) App::$Data['subCategory'] = $this->boardManger->GetSubCategory(Get('cate'));

		if(!is_null($this->boardManger->GetValue('category')) && strlen($this->boardManger->GetValue('category'))){
			App::$Data['category'] = explode(',', $this->boardManger->GetValue('category'));
		}
	}

	/**
	 * 접근 권한 체크
	 *
	 * @param string $mode
	 * @return bool
	 */
	public function GetAuth($mode){
		if(CM::GetAdminIs()) return true;
		if($this->managerIs) return true;
		$memberLevel = _MEMBERIS === true ? $_SESSION['member']['level'] : 0;
		switch($mode){
			case 'Write':
			case 'Modify':
				if($memberLevel < $this->boardManger->GetValue('auth_write_level')) return false;
			break;
			case 'Answer':
				if($memberLevel < $this->boardManger->GetValue('auth_answer_level')) return false;
			break;
			case 'View':
				if($memberLevel < $this->boardManger->GetValue('auth_view_level')) return false;
			break;
			case 'List':
				if($memberLevel < $this->boardManger->GetValue('auth_list_level')) return false;
			break;
		}

		return true;
	}

	public function Index(){
		$res = $this->GetAuth('List');
		if(!$res){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}
		if($this->GetListIs || $this->MoreListIs){
			if(_JSONIS === true) JSON(true, '', App::GetView($this->model));
			else  App::View($this->model);
			return;
		}

		$this->GetList();
	}

	public function _SearchQuery($qry, $noticeIs = false){

		$s_keyword = Get('keyword');
		$s_type = Get('stype');

		App::$Data['categoryKeyword'] = strlen($this->menuCategory) ? $this->menuCategory : Get('cate');
		if(sizeof($this->additionalSubId)){
			$qry->AddWhere('`A`.`subid` IN (%s)', $this->additionalSubId);
		}
		else $qry->AddWhere('`A`.`subid` = %s', $this->subid);

		if(strlen(App::$Data['categoryKeyword'])) $qry->AddWhere('`A`.category = %s', App::$Data['categoryKeyword']);

		if(strlen($this->menuSubCategory)) $qry->AddWhere('`A`.sub_category IN (%s)', explode(',', $this->menuSubCategory));
		else if(!EmptyGet('scate')) $qry->AddWhere('`A`.sub_category = %s', Get('scate'));


		if(!$noticeIs){
			if(strlen($s_type) && strlen($s_keyword)){
				switch($s_type){
					case 's':
						$qry->AddWhere('INSTR(A.subject, %s)', $s_keyword);
					break;
					case 'c':
						$qry->AddWhere('INSTR(A.content, %s)', $s_keyword);
					break;
					case 'snc':
						$qry->AddWhere('INSTR(A.subject, %s) OR INSTR(A.content, %s)', $s_keyword, $s_keyword);
					break;
				}
			}
		}

	}

	public function GetList($viewPageIs = false){
		$res = $this->GetAuth('List');
		if(!$res){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}

		$s_keyword = Get('keyword');
		$s_page = Get('page');


		// 공지를 불러온다.
		App::$Data['notice'] = array();
		if(($s_page < 2) && !strlen($s_keyword)){
			$qry = $this->model->GetNoticeQuery();

			$this->_SearchQuery($qry);

			$this->_R_CommonQry($qry);
			$this->_R_NoticeQuery($qry);

			App::$Data['notice'] = $qry->GetRows();
			$this->_RowSet(App::$Data['notice']);
		}

		// 리스트를 불러온다.
		$dbList = $this->model->NewQryName('default')->GetSetPageListQry('A');

		if($this->boardManger->_list_show_notice->txt() == 'n' && ($s_page < 2) && !strlen($s_keyword)) $dbList->AddWhere('A.notice=\'n\'');

		$dbList->SetSort('A.sort1, A.sort2')
			->SetPage($s_page)
			->SetPageUrl(App::URLAction('').App::GetFollowQuery('page'))
			->SetArticleCount($this->boardManger->GetValue('article_count'));
		$this->_R_CommonQry($dbList);

		if(!$this->AdminPathIs){
			$dbList->AddWhere('A.delis=\'n\'');
			if(!$this->managerIs && $this->boardManger->GetValue('man_to_man') === 'y') $dbList->AddWhere('A.muid = %d OR A.target_muid = %d', $_SESSION['member']['muid'], $_SESSION['member']['muid']);
		}

		$this->_SearchQuery($dbList);

		$this->_R_GetListQuery($dbList); // Reserved
		$dbList->DrawRows();
		$this->_RowSet($dbList->data);

		App::$Html = $this->Path.'Index.html';

		if($viewPageIs) return App::GetOnlyView($this->model, $dbList);
		else if(_JSONIS === true) JSON(true, '', App::GetView($this->model, $dbList));
		else  App::View($this->model, $dbList);
	}

	public function MoreList(){
		$res = $this->GetAuth('List');
		if(!$res){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}

		$s_keyword = Get('keyword');
		$s_seq = Get('seq');
		$s_last_seq = Get('lastSeq');


		// 공지를 불러온다.
		App::$Data['notice'] = array();
		if(!strlen($s_seq) && !strlen($s_last_seq) && !strlen($s_keyword)){
			$qry = $this->model->GetNoticeQuery();

			$this->_SearchQuery($qry);

			$this->_R_CommonQry($qry);
			$this->_R_NoticeQuery($qry);

			App::$Data['notice'] = $qry->GetRows();
			$this->_RowSet(App::$Data['notice']);
		}

		// 리스트를 불러온다.
		$dbList = $this->model->NewQryName('default')->GetSetListQry('A');

		if($this->boardManger->_list_show_notice->txt() == 'n' && !strlen($s_keyword)) $dbList->AddWhere('A.notice=\'n\'');

		$dbList->SetLimit($this->boardManger->GetValue('article_count'))
			->SetSort('A.sort1, A.sort2');
		$this->_R_CommonQry($dbList);

		if(!$this->AdminPathIs){
			$dbList->AddWhere('A.delis=\'n\'');
			if(!$this->managerIs && $this->boardManger->GetValue('man_to_man') === 'y') $dbList->AddWhere('A.muid = %d OR A.target_muid = %d', $_SESSION['member']['muid'], $_SESSION['member']['muid']);
		}

		if(strlen($s_seq)){
			$seq = to10($s_seq);
			$dbList->AddWhere('A.seq = %d', $seq);
		}
		else{
			if(strlen($s_last_seq)){
				$qry = DB::GetQryObj($this->model->table.' A')
					->AddWhere('A.seq = %d', $s_last_seq)
					->SetKey('A.sort1, A.sort2');
				$this->_R_CommonQry($qry);
				$last = $qry->Get();

				if($last) $dbList->AddWhere('A.sort1 > %d OR (A.sort1 = %d AND A.sort2 > %d)', $last['sort1'], $last['sort1'], $last['sort2']);
				else $dbList->AddWhere('A.seq > %d', $s_last_seq);
			}

			$this->_SearchQuery($dbList);
		}

		$this->_R_MoreListQuery($dbList);  // Reserved
		$dbList->DrawRows();
		$this->_RowSet($dbList->data);

		$lastSeq = '';
		$lastIs = false;
		if(sizeof($dbList->data)){
			$end = end($dbList->data);
			$lastSeq = $end['seq'];
		}
		if(sizeof($dbList->data) < $this->boardManger->GetValue('article_count')) $lastIs = true;

		if(_JSONIS === true) JSON(true, '', array('list' => App::GetOnlyView($this->model, $dbList), 'lastSeq' => $lastSeq, 'lastIs' => $lastIs));
		else App::View($this->model, array('list' => $dbList, 'lastSeq' => $lastSeq));
	}

	public function _RowSet(&$data){
		$ck = strlen(App::$Data['categoryKeyword']) ? true : false;
		foreach($data as &$row){
			if($this->managerIs || $row['secret'] == 'n' || ($row['first_member_is'] == 'y' && strlen($row['muid']))) $row['possibleView'] = true;
			else $row['possibleView'] = false;
			$row['viewUrl'] = App::URLAction('View/').toBase($row['seq']).App::GetFollowQuery();
			$row['replyCount'] = $row['reply_cnt'] ? '<span class="ReplyCount">['.$row['reply_cnt'].']</span>' : '';
			$row['newArticleIs'] = (time() - strtotime($row['reg_date']) < $this->boardManger->data['new_view_day']->Value * 60 * 60 * 24);
			$row['viewCategory'] = $ck ? (strlen($row['sub_category']) ? $row['sub_category'] : $row['category']) : $row['category'];
		}
	}

	public function PostView(){
		$this->View();
	}

	public function View(){
		if($this->boardManger->GetValue('list_in_view') == 'y' && !$this->MoreListIs) App::$Data['List'] = $this->GetList(true);
		App::$Html = $this->Path.'View.html';

		if(!isset(App::$ID) || !strlen(App::$ID)) URLReplace('-1');

		$seq = to10(App::$ID);

		$viewAuth = $this->GetAuth('View');
		if(!$viewAuth){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}

		$this->_GetBoardData($seq);

		if(!$this->AdminPathIs){
			if($this->model->GetValue('delis') == 'y') URLReplace('-1', _MSG_WRONG_CONNECTED);
			if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && $this->model->GetValue('muid') != $_SESSION['member']['muid'] && $this->model->GetValue('target_muid') != $_SESSION['member']['muid']) URLReplace('-1', _MSG_WRONG_CONNECTED);
		}

		$data['answerAuth'] = $this->GetAuth('Answer');

		// 비밀번호없이 수정권한
		$data['modifyAuthDirect'] = false;
		if($this->GetAuth('Write') && _MEMBERIS === true && ($this->model->GetValue('muid') == $_SESSION['member']['muid'] || CM::GetAdminIs() )){
			$data['modifyAuthDirect'] = true;
		}

		// 비밀글일경우 권한 : 관리자 또는 게시판 매니저, 글쓴이
		if(!CM::GetAdminIs() && $this->model->GetValue('secret') == 'y'){
			$viewAuth = false;

			// first_seq 가 있으면 첫째글을 호출
			if(strlen($this->model->GetValue('first_seq'))){
				$qry = DB::GetQryObj($this->model->table)
					->AddWhere('seq=' . $this->model->GetValue('first_seq'));
				$this->_R_CommonQry($qry);
				$firstDoc = $qry->Get();

			}

			if(_MEMBERIS === true){
				// 자신의 글 권한
				if($this->model->GetValue('muid') == $_SESSION['member']['muid'] || (isset($firstDoc) && $this->model->GetValue('first_member_is') == 'y' && $firstDoc['muid'] == $_SESSION['member']['muid'])){
					$viewAuth = true;
				}
			}

			// 원글이나 현재 글이 비회원글일 경우 비밀번호를 체크
			if(!$viewAuth && (!$this->model->GetValue('muid') || $this->model->GetValue('first_member_is') == 'n')){
				if(_POSTIS !==	true || !isset($_POST['pwd'])) URLReplace('-1', _MSG_WRONG_CONNECTED);

				if(_password_verify(Post('pwd'), $this->model->GetValue('pwd')) || (isset($firstDoc) && _password_verify(Post('pwd'), $firstDoc['pwd']))){
					$viewAuth = true;
				}
				else URLReplace('-1', _MSG_WRONG_PASSWORD);
			}

			if(!$viewAuth) URLReplace('-1', _MSG_NO_AUTH);
		}

		$cookieName = $this->model->table.$seq;
		if(!isset($_COOKIE[$cookieName]) || !$_COOKIE[$cookieName]){
			$dbUpdate = DB::UpdateQryObj($this->model->table)
				->SetData('hit', 'hit + 1')
				->AddWhere('seq='.$seq);
			$this->_R_CommonQry($dbUpdate);
			$dbUpdate->Run();

			setcookie($cookieName, 'y');
		}

		App::$Data['boardActionData'] = array();
		if(_MEMBERIS == true){
			$res = ArticleAction::GetInstance($this->bid)
				->SetArticleSeq($seq)
				->SetMUid($_SESSION['member']['muid'])
				->GetAllAction();
			if($res->result) App::$Data['boardActionData'] = $res->data;

			if($this->model->GetValue('muid') != $_SESSION['member']['muid'] && !isset(App::$Data['boardActionData']['read'])){
				ArticleAction::GetInstance($this->bid)
					->SetArticleSeq($seq)
					->SetMUid($_SESSION['member']['muid'])
					->SetParentTable($this->bid)
					->Read();
			}
		}

		App::$Data['recommendButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$ID) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$ID) . '" data-type="recommend" class="boardActionBtn boardRecommendActionBtn' .(isset(App::$Data['boardActionData']['recommend']) ? ' already' : ''). '"><b>추천</b> <span class="num">' . ($this->model->_recommend->txt()) . '</span></a>';

		App::$Data['scrapButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$ID) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$ID) . '" data-type="scrap" class="boardActionBtn boardSubscribeActionBtn' .(isset(App::$Data['boardActionData']['scrap']) ? ' already' : ''). '"><b>스크랩</b> <span class="num">' . ($this->model->_scrap->txt()) . '</span></a>';

		App::$Data['opposeButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$ID) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$ID) . '" data-type="oppose" class="boardActionBtn boardOpposeActionBtn' .(isset(App::$Data['boardActionData']['oppose']) ? ' already' : ''). '"><b>반대</b> <span class="num">' . ($this->model->_oppose->txt()) . '</span></a>';

		App::$Data['reportButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$ID) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$ID) . '" data-type="report" class="boardActionBtn boardReportActionBtn' .(isset(App::$Data['boardActionData']['report']) ? ' already' : ''). '"><b>신고</b> <span class="num">' . ($this->model->_report->txt()) . '</span></a>';


		$_SESSION['boardView']['bid'] = $this->bid;
		$_SESSION['boardView']['seq'] = App::$ID;

		$this->_R_ViewEnd($data);  // Reserved

		if(_JSONIS === true) JSON(true, '', App::GetView($this->model, $data));
		else App::View($this->model, $data);
	}

	public function Write(){
		$res = $this->GetAuth('Write');
		if(!$res){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}

		if(sizeof($this->additionalSubId)){
			$arr = $this->_GetAdditionalSubidList();

			if(sizeof($arr)){
				if(EmptyGet('subid')) URLReplace(-1, _MSG_WRONG_CONNECTED);
				else{
					$this->subid = Get('subid');
					if(!isset($arr[$this->subid])) URLRedirect(-1, '게시판이 존재하지 않습니다.');
				}
			}
		}

		$this->_R_WriteEnd();  // Reserved
		if(_JSONIS === true) JSON(true, '', App::GetView($this->model));
		else App::View($this->model);
	}

	public function Answer(){
		$res = $this->GetAuth('Answer');
		if(!$res){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}
		$seq = to10(strlen(Post('target')) ? Post('target') : Get('target'));
		if(!strlen($seq)) URLReplace('-1');

		$qry = DB::GetQryObj($this->model->table)
			->AddWhere('seq = %d', $seq);
		$this->_R_CommonQry($qry);
		$data = $qry->Get();
		if(!$this->AdminPathIs){
			if($data['delis'] == 'y') URLReplace('-1', _MSG_WRONG_CONNECTED);
			if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && $data['muid'] != $_SESSION['member']['muid'] && $data['target_muid'] != $_SESSION['member']['muid']) URLReplace('-1', _MSG_WRONG_CONNECTED);
		}

		$this->model->SetValue('subject', strpos('[답변]', $data['subject']) === false ? '[답변] '.$data['subject'] : $data['subject']);
		$this->model->SetValue('secret', $data['secret']);

		$this->_R_AnswerEnd();  // Reserved

		if(_JSONIS === true) JSON(true, '', App::GetView($this->model));
		else App::View($this->model);
	}

	public function Modify(){
		if(!isset(App::$ID) || !strlen(App::$ID)){
			URLReplace('-1');
		}

		$res = $this->GetAuth('Modify');
		if(!$res){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}

		$seq = to10(App::$ID);
		$this->_GetBoardData($seq);
		if(!$this->AdminPathIs){
			if($this->model->GetValue('delis') == 'y') URLReplace('-1', _MSG_WRONG_CONNECTED);
			if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && $this->model->GetValue('muid') != $_SESSION['member']['muid'] && $this->model->GetValue('target_muid') != $_SESSION['member']['muid']) URLReplace('-1', _MSG_WRONG_CONNECTED);
		}

		// 회원 글 체크
		if(_MEMBERIS !== true || !CM::GetAdminIs()){
			$res = $this->_PasswordCheck();
			if($res !== true) URLReplace('-1', $res);
		}

		$this->_R_ModifyEnd();  // Reserved

		if(_JSONIS === true) JSON(true, '', App::GetView($this->model));
		else App::View($this->model);
	}

	public function PostModify(){
		if(Post('mode') == 'view'){
			$this->Modify();
			return;
		}
		$res = $this->GetAuth('Modify');
		if(!$res){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}

		$seq = to10(App::$ID);

		$this->model->Need = array('subject', 'content');
		if($this->boardManger->GetValue('use_secret') === 'y') $this->model->Need = 'secret';
		if(_MEMBERIS !== true) $this->model->Need = 'mnane';
		else $this->model->AddExcept('pwd');

		$this->_GetBoardData($seq);
		$beforeFile = $this->model->_file1->Value;
		if(!$this->AdminPathIs){
			if($this->model->GetValue('delis') == 'y') URLReplace('-1', _MSG_WRONG_CONNECTED);
			$this->model->AddExcept('delis');
			if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && $this->model->GetValue('muid') != $_SESSION['member']['muid'] && $this->model->GetValue('target_muid') != $_SESSION['member']['muid']) URLReplace('-1', _MSG_WRONG_CONNECTED);
		}

		$res = $this->model->SetPostValuesWithFile();
		if(!$res->result){
			$res->message ? $res->message : 'ERROR#101';
			if(_AJAXIS === true) JSON(false, $res->message);
			App::$Data['error'] = $res->message;
			App::View($this->model);
			return;
		}
		// 회원 글 체크
		if(_MEMBERIS !== true || !CM::GetAdminIs()){
			$res = $this->_PasswordCheck();
			if($res !== true){
				if(_AJAXIS === true) JSON(false, $res);
				App::$Data['error'] = $res;
				App::View($this->model);
				return;
			}
		}

		// 기본 데이타
		$this->model->SetValue('htmlis', Post('htmlis') == 'y' ? 'y' : 'n');

		// 섬네일 등록
		if(IsImageFileName($this->model->_file1->Value)){
			$this->model->_thumbnail->SetValue($this->model->GetFilePath('file1'));
		}

		$this->_R_PostModifyUpdateBefore();  // Reserved

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)){
			if(_AJAXIS === true) JSON(false, $error[0]);
			App::$Data['error'] = $error[0];
			App::View($this->model);
			return;
		}

		if($this->model->_htmlis->Value !== 'n') $this->model->_content->Value = RemoveIFrame($this->model->_content->Value);

		$res2 = $this->model->DBUpdate();
		$this->_ContentImageUpdate(Post('content'), $seq, 'modify');


		if($res2->result){
			$this->_R_PostModifyUpdateAfter();  // Reserved
			if(_AJAXIS === true) JSON(true, '',_MSG_COMPLETE_MODIFY);
			else URLReplace(App::URLAction('View/'.App::$ID).App::GetFollowQuery(), _MSG_COMPLETE_MODIFY);
		}
		else{
			if(_AJAXIS === true) JSON(false, $res2->message ? $res2->message : 'ERROR#102');
			App::$Data['error'] = $res2->message ? $res2->message : 'ERROR#102';
			App::View($this->model);
			return;
		}
	}

	public function PostAnswer(){
		$this->PostWrite();
	}

	public function PostWrite(){
		if(_POSTIS !== true) URLReplace('-1', _MSG_WRONG_CONNECTED);

		$res = $this->GetAuth('Write');
		if(!$res){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}

		$first_seq = '';
		$first_member_is = 'n';


		if(App::$Action == 'Answer'){
			$auth = $this->GetAuth('Answer');
			if(!$auth){
				if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
				URLReplace('-1', _MSG_NO_AUTH);
			}

			$qry = DB::GetQryObj($this->model->table)
				->AddWhere('seq=%d', to10(Post('target')))
				->SetKey('mname, email, email_alarm, depth, muid, target_muid, sort1, sort2', 'seq', 'first_seq', 'first_member_is', 'category', 'sub_category', 'delis', 'subid');
			$this->_R_CommonQry($qry);
			App::$Data['targetData'] = $qry->Get();
			if(!$this->AdminPathIs){
				if(App::$Data['targetData']['delis'] == 'y') URLReplace('-1', _MSG_WRONG_CONNECTED);
				if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && App::$Data['targetData']['muid'] != $_SESSION['member']['muid'] && App::$Data['targetData']['target_muid'] != $_SESSION['member']['muid']) URLReplace('-1', _MSG_WRONG_CONNECTED);
			}

			$first_seq = strlen(App::$Data['targetData']['first_seq']) ? App::$Data['targetData']['first_seq'] : App::$Data['targetData']['seq'];
			$first_member_is = App::$Data['targetData']['first_member_is'];
		}


		$result = new \BH_Result();

		if(!$this->AdminPathIs) $this->model->AddExcept('delis');
		$this->model->Need = array('subject', 'content');
		if($this->boardManger->GetValue('use_secret') === 'y') $this->model->Need = 'secret';
		if(_MEMBERIS === true){
			$member = CM::GetMember();
			$this->model->AddExcept('pwd');
		}

		$res = $this->model->SetPostValuesWithFile();

		if(!$res->result){
			$res->message ? $res->message : 'ERROR#101';
			if(_JSONIS === true) JSON(false, $res->message);
			App::$Data['error'] = $res->message;
			$this->Write();
			return;
		}

		// 기본 데이타
		$this->model->SetValue('reg_date', date('Y-m-d H:i:s'));
		$this->model->SetValue('htmlis', Post('htmlis') == 'y' ? 'y' : 'n');
		$this->model->_subid->SetValue($this->subid);


		// 회원유무
		if(_MEMBERIS === true){
			$this->model->SetValue('muid', $_SESSION['member']['muid']);
			$this->model->SetValue('mlevel', $member['level']);
			$this->model->SetValue('email', $member['email']);
			$this->model->SetValue('mname', $member['nickname'] ? $member['nickname'] : ($member['mname'] ? $member['mname'] : $member['mid']));
		}

		// 답글쓰기라면 sort 정렬
		if(App::$Action == 'Answer'){
			$qry = DB::UpdateQryObj($this->model->table)
				->SetData('sort2', 'sort2 + 1')
				->AddWhere('sort1 = %d', App::$Data['targetData']['sort1'])
				->AddWhere('sort2 > %d', App::$Data['targetData']['sort2'])
				->SetSort('sort2 DESC');
			$this->_R_CommonQry($qry);
			$res = $qry->Run();

			if(!$res->result){
				if(_JSONIS === true) JSON(false, 'ERROR#201');
				App::$Data['error'] = 'ERROR#201';
				$this->Write();
				return;
			}
			$this->model->SetValue('first_seq', $first_seq);
			$this->model->SetValue('first_member_is', $first_member_is);
			$this->model->SetValue('target_mname', App::$Data['targetData']['mname']);
			$this->model->SetValue('category', App::$Data['targetData']['category']);
			$this->model->SetValue('sub_category', App::$Data['targetData']['sub_category']);
			$this->model->SetValue('subid', App::$Data['targetData']['subid']);
			$this->model->SetValue('target_muid', App::$Data['targetData']['muid'] ? App::$Data['targetData']['muid'] : 0);
			$this->model->SetValue('sort1', App::$Data['targetData']['sort1']);
			$this->model->SetValue('sort2', App::$Data['targetData']['sort2'] + 1);
			$this->model->SetValue('depth', App::$Data['targetData']['depth'] + 1);
		}else{
			$this->model->SetValue('first_member_is', _MEMBERIS === true ? 'y' : 'n');
			$this->model->SetQueryValue('sort1', '(SELECT IF(COUNT(s.sort1) = 0, 0, MIN(s.sort1))-1 FROM '.$this->model->table.' as s)');
			if(sizeof($this->additionalSubId) && !EmptyPost('subid')){
				$this->model->_subid->SetValue(Post('subid'));
			}
		}

		$this->_R_PostWriteInsertBefore();  // Reserved

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)){
			if(_JSONIS === true) JSON(false, $error[0]);
			App::$Data['error'] = $error[0];
			$this->Write();
			return;
		}

		// 섬네일 등록
		if(IsImageFileName($this->model->_file1->Value)){
			$this->model->_thumbnail->SetValue($this->model->GetFilePath('file1'));
		}

		if($this->model->_htmlis->Value !== 'n') $this->model->_content->Value = RemoveIFrame($this->model->_content->Value);

		$res = $this->model->DBInsert();
		$result->result = $res->result;
		$result->message = $res->message;

		if($result->result){
			$this->_ContentImageUpdate(Post('content'), $res->id);
			$this->_R_PostWriteInsertAfter($res->id);  // Reserved

			// 알람
			if(class_exists('PHPMailer\\PHPMailer\\PHPMailer') && App::$Action == 'Answer' && App::$Data['targetData']['email_alarm'] == 'y' && strlen(App::$Data['targetData']['email']) && CM::Config('Default', 'SendEmail')){
				$mail = new Email();
				$mail->AddMail(App::$Data['targetData']['email'], App::$Data['targetData']['mname']);
				if($this->AdminPathIs) $url = _URL . '/Board/' . $this->bid . '-'. $this->subid . '/View/' . toBase($res->id);
				else $url = App::URLAction('View/' . toBase($res->id));
				$mail->SendMailByAnswerAlarm(App::$Data['targetData']['mname'], $url, $this->model->_mname->Value, $this->model->_subject->Value, ($this->model->_htmlis->Value === 'y' ? $this->model->_content->safeRaw() : $this->model->_content->safeBr()));
			}

			if(_AJAXIS === true){
				JSON(true, '', '등록되었습니다.');
			}
			else URLReplace(App::URLAction(), '등록되었습니다.');
		}else{
			if(_AJAXIS === true) JSON(false, $result->message ? $result->message : 'ERROR');
			App::$Data['error'] = $result->message ? $result->message : 'ERROR';
			$this->Write();
			return;
		}
	}

	public function PostDelete(){
		$res = $this->GetAuth('Write');
		if(!$res){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}

		$seq = to10(App::$ID);

		$this->_GetBoardData($seq);

		if(!$this->AdminPathIs){
			if($this->model->GetValue('delis') == 'y') URLReplace('-1', '이미 삭제된 글입니다.');
			if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && $this->model->GetValue('muid') != $_SESSION['member']['muid']) URLReplace('-1', _MSG_WRONG_CONNECTED);
		}

		// 회원 글 체크
		if(_MEMBERIS !== true || !$this->managerIs){
			$res = $this->_PasswordCheck();
			if($res !== true){
				URLReplace('-1', $res);
			}
		}

		$this->model->SetValue('delis', 'y');
		$this->model->DBUpdate();

		if(_AJAXIS === true) JSON(true, '', '삭제되었습니다.');
		else URLReplace(App::URLAction('').App::GetFollowQuery(), '삭제되었습니다.');
	}

	public function Undelete(){
		if(!$this->AdminPathIs) URLReplace('-1', _MSG_WRONG_CONNECTED);

		$seq = to10(App::$ID);

		$this->_GetBoardData($seq);
		$this->model->SetValue('delis', 'n');
		$this->model->DBUpdate();

		if(_AJAXIS === true) JSON(true, '', '복구되었습니다.');
		else URLReplace(App::URLAction('').App::GetFollowQuery(), '복구되었습니다.');
	}

	public function Download(){
		if(strpos(App::$ID, '-') !== false){
			$temp = explode('-', App::$ID);
			$md = $temp[0];
			$seq = to10($temp[1]);
		}
		else{
			$seq = to10(App::$ID);
			$md = 'file1';
		}
		$this->_GetBoardData($seq);
		$file = explode('*', $this->model->GetValue($md));
		if(sizeof($file) < 2){
			$name = explode('/', $file[0]);
			$file[1] = end($name);
		}
		if(file_exists(_UPLOAD_DIR . $file[0]) && !is_dir(_UPLOAD_DIR . $file[0])) Download(_UPLOAD_DIR . $file[0], $file[1]);
		else URLRedirect(-1, '파일이 존재하지 않습니다.');
	}

	public function RepAttachDownload(){
		$repData = DB::GetQryObj($this->model->table . '_reply')
			->AddWhere('seq = %d', to10(App::$ID))
			->SetKey('`file`')
			->Get();
		$file = explode('*', $repData['file']);
		if(sizeof($file) < 2){
			$name = explode('/', $file[0]);
			$file[1] = end($name);
		}
		if(file_exists(_UPLOAD_DIR . $file[0]) && !is_dir(_UPLOAD_DIR . $file[0])) Download(_UPLOAD_DIR . $file[0], $file[1]);
		else URLRedirect(-1, '파일이 존재하지 않습니다.');
	}

	/**
	 * 게시물을 DB에서 완전히 삭제
	 */
	public function PostRemove(){
		$seq = to10(App::$ID);
		if(!$this->AdminPathIs) URLReplace(-1, _MSG_WRONG_CONNECTED);
		$this->_CheckArticleRemove($seq);

		URLReplace(App::URLAction().App::GetFollowQuery());
	}

	/**
	 * 게시물 내용의 이미지를 삭제, 신규 이미지 이동
	 *
	 * @param string $content
	 * @param int $seq
	 * @param string $mode
	 * @return bool
	 */
	protected function _ContentImageUpdate($content, $seq, $mode = 'write'){
		$newContent = $content;

		if($mode == 'modify'){
			$dbGetList = DB::GetListQryObj($this->model->imageTable)
				->AddWhere('article_seq='.$seq);
			$this->_R_CommonQry($dbGetList);
			while($img = $dbGetList->Get()){
				if(strpos($content,$img['image']) === false){
					// 파일이 없으면 삭제
					@UnlinkImage(_UPLOAD_DIR.$img['image']);

					if($img['image'] == $this->model->GetValue('thumbnail')) $this->model->SetValue('thumbnail', '');

					$qry = DB::DeleteQryObj($this->model->table.'_images')
						->AddWhere('article_seq = '.$img['article_seq'])
						->AddWhere('seq = '.$img['seq']);
					$this->_R_CommonQry($qry);
					$qry->Run();
				}
			}
		}

		$qry = DB::GetQryObj($this->model->imageTable)
			->AddWhere('article_seq='.$seq)
			->SetKey('COUNT(*) as cnt');
		$this->_R_CommonQry($qry);
		$cnt = $qry->Get();
		$imageCount = $cnt['cnt'];

		if(is_array(Post('addimg'))){
			foreach(Post('addimg') as $img){
				$exp = explode('|', $img);

				if(strpos($content, $exp[0]) !== false){

					$newpath = str_replace('/temp/', $this->uploadImageDir, $exp[0]);
					$uploadDir = _UPLOAD_DIR.$this->uploadImageDir;
					if(!is_dir($uploadDir)){
						mkdir($uploadDir, 0777, true);
					}

					// 복사 전에 파일의 용량 체크 여기서 가능
					@copy(_UPLOAD_DIR.$exp[0],_UPLOAD_DIR.$newpath);
					$newContent = str_replace($exp[0],$newpath, $newContent);
					// 파일이 있으면 등록

					unset($dbInsert);
					// 여기 수정
					$dbInsert = DB::InsertQryObj($this->model->imageTable)
						->SetDataNum('article_seq', $seq)
						->SetDataStr('image', $newpath)
						->SetDataStr('imagename', $exp[1])
						->SetDecrementKey('seq')
						->AddWhere('article_seq = %d', $seq);
					$this->_R_CommonQry($dbInsert, 'ImageInsert');
					$dbInsert->Run();
					$imageCount++;
				}
				@UnlinkImage(_UPLOAD_DIR.$exp[0]);
			}

			if($newContent != $content || !$this->model->GetValue('thumbnail')){
				if(!$this->model->GetValue('thumbnail')){
					$qry = DB::GetQryObj($this->model->imageTable)
						->AddWhere('article_seq='.$seq)
						->SetSort('seq');
					$this->_R_CommonQry($qry);
					$new = $qry->Get();
					$this->model->SetValue('thumbnail', $new['image']);
				}
				$qry =DB::UpdateQryObj($this->model->table)
					->SetDataStr('thumbnail', $this->model->GetValue('thumbnail'))
					->SetDataStr('content', $newContent)
					->AddWhere('seq = '.$seq);
				$this->_R_CommonQry($qry);
				$qry->Run();
			}
		}

		DeleteOldTempFiles(_UPLOAD_DIR.'/temp/', strtotime('-6 hours'));
		return true;
	}

	/**
	 * 게시물 비밀번호 체크
	 * @return bool|string
	 */
	protected function _PasswordCheck(){
		if($this->model->GetValue('muid')){
			if(_MEMBERIS !== true) return 'ERROR#101';
			else if($this->model->GetValue('muid') != $_SESSION['member']['muid']) return 'ERROR#102';
		}
		else{
			if(!isset($_POST['pwd'])) return _MSG_WRONG_CONNECTED;

			$qry = DB::GetQryObj($this->model->table)->AddWhere('seq = %d', $this->model->GetValue('seq'))->SetKey('pwd');
			$this->_R_CommonQry($qry);
			$pwd = $qry->Get();
			if(!_password_verify(Post('pwd'), $pwd['pwd'])){
				return _MSG_WRONG_PASSWORD;
			}
		}
		return true;
	}

	/**
	 * JSON 게시물 액션 실행
	 */
	public function PostJSONAction(){
		if(_MEMBERIS !== true) JSON(false, _MSG_NEED_LOGIN);

		$articleAction = $this->GetArticleAction();
		switch(Post('type')){
			case 'recommend':
				$res = $articleAction->Recommend();
				echo json_encode($res);
			break;
			case 'oppose':
				$res = $articleAction->Oppose();
				echo json_encode($res);
			break;
			case 'report':
				$res = $articleAction->Report();
				echo json_encode($res);
			break;
			case 'scrap':
				$res = $articleAction->Subscribe();
				echo json_encode($res);
			break;
			default:
				JSON(false, _MSG_WRONG_CONNECTED);
			break;
		}
	}

	/**
	 * JSON 게시물 액션 취소
	 */
	public function PostJSONCancelAction(){
		if(_MEMBERIS !== true) JSON(false, _MSG_NEED_LOGIN);

		$articleAction = $this->GetArticleAction();
		switch(Post('type')){
			case 'recommend':
				$res = $articleAction->CancelRecommend();
				echo json_encode($res);
			break;
			case 'oppose':
				$res = $articleAction->CancelOppose();
				echo json_encode($res);
			break;
			case 'report':
				$res = $articleAction->CancelReport();
				echo json_encode($res);
			break;
			case 'scrap':
				$res = $articleAction->CancelSubscribe();
				echo json_encode($res);
			break;
			default:
				JSON(false, _MSG_WRONG_CONNECTED);
			break;
		}
	}

	/**
	 * JSON 서브아이디 가져오기
	 */
	public function PostGetSubCategory(){
		$res = $this->boardManger->DBGet(Post('bid'), Post('subid'));
		if(!$res->result) JSON(false, $res->message ? $res->message : _MSG_WRONG_CONNECTED);
		$res = $this->boardManger->GetSubCategory(Post('cate'));
		JSON(true, '', $res);
	}

	/**
	 * 게시판의 분류를 html로 반환
	 * 서브분류가 있으면 서브분류 반환
	 *
	 * @return string
	 */
	public function _CategoryHtml(){
		App::$Data['categoryHtml'] = '';

		if(!strlen($this->menuSubCategory) && strlen($this->menuCategory) && sizeof(App::$Data['subCategory'])){
			App::$Data['categoryHtml'] .= '<div class="categoryTab categoryTabC categoryTabC1"><ul>';
			App::$Data['categoryHtml'] .= '<li class="all ' . (EmptyGet('scate') ? 'active' : '') . '"><a href="' . App::URLAction() . '">전체</a></li>';
			foreach(App::$Data['subCategory'] as $v){
				$active = $v == Get('scate') ? ' class="active"' : '';
				App::$Data['categoryHtml'] .= '<li' . $active . '><a href="' . App::URLAction() . '?scate=' . GetDBText($v) . '">' . GetDBText($v) . '</a></li>';
			}
			App::$Data['categoryHtml'] .= '</ul></div>';
		}

		else if(!strlen($this->menuSubCategory)){
			if(!EmptyGet('cate') && sizeof(App::$Data['subCategory'])){
				App::$Data['categoryHtml'] .= '<div class="categoryTab categoryTabC categoryTabC1"><ul>';
				App::$Data['categoryHtml'] .= '<li class="parent"><a href="' . App::URLAction() . '">상위</a></li>';
				App::$Data['categoryHtml'] .= '<li class="all ' . (EmptyGet('scate') ? 'active' : '') . '"><a href="' . App::URLAction() . '?cate=' . GetDBText(Get('cate')) . '">전체</a></li>';
				foreach(App::$Data['subCategory'] as $v){
					$active = $v == Get('scate') ? ' class="active"' : '';
					App::$Data['categoryHtml'] .= '<li' . $active . '><a href="' . App::URLAction() . '?cate=' . GetDBText(Get('cate')) . '&scate=' . GetDBText($v) . '">' . GetDBText($v) . '</a></li>';
				}
				App::$Data['categoryHtml'] .= '</ul></div>';
			}
			else if(sizeof(App::$Data['category'])){
				App::$Data['categoryHtml'] .= '<div class="categoryTab categoryTabC categoryTabC2"><ul>';
				App::$Data['categoryHtml'] .= '<li class="all ' . (EmptyGet('cate') ? 'active' : '') . '"><a href="' . App::URLAction() . '">전체</a></li>';
				foreach(App::$Data['category'] as $v){
					$active = $v == Get('cate') ? ' class="active"' : '';
					App::$Data['categoryHtml'] .= '<li' . $active . '><a href="' . App::URLAction() . '?cate=' . GetDBText($v) . '">' . GetDBText($v) . '</a></li>';
				}
				App::$Data['categoryHtml'] .= '</ul></div>';
			}
		}


		return App::$Data['categoryHtml'];
	}

	/**
	 * 체크 항목 삭제
	 */
	public function PostSysDel(){
		if(!$this->managerIs) JSON(false, _MSG_WRONG_CONNECTED);
		if(EmptyPost('seq')) JSON(false, '삭제할 게시물을 선택하여 주세요.');
		$chk = explode(',', Post('seq'));

		DB::UpdateQryObj($this->model->table)
			->AddWhere('seq IN (%d)', $chk)
			->SetDataStr('delis','y')
			->Run();
		JSON(true);
	}

	/**
	 * 체크 항목 복구
	 */
	public function PostSysUnDel(){
		if(!$this->managerIs) JSON(false, _MSG_WRONG_CONNECTED);
		if(EmptyPost('seq')) JSON(false, '복구할 게시물을 선택하여 주세요.');
		$chk = explode(',', Post('seq'));

		DB::UpdateQryObj($this->model->table)
			->AddWhere('seq IN (%d)', $chk)
			->SetDataStr('delis','n')
			->Run();
		JSON(true);
	}

	/**
	 * 체크 항목 이동
	 */
	public function PostSysMove(){
		if(!$this->managerIs) JSON(false, _MSG_WRONG_CONNECTED);
		if(EmptyPost('seq')) JSON(false, '이동할 게시물을 선택하여 주세요.');
		if(EmptyPost('bid') || EmptyPost('subid')) JSON(false, '이동할 게시판을 선택하여 주세요.');
		$chk = explode(',', Post('seq'));

		$resChk = $this->_CheckArticleCopy($chk, Post('bid'), Post('subid'), 'move');
		if(sizeof($resChk)){
			$this->_CheckArticleRemove($resChk);
		}

		JSON(true);
	}

	/**
	 * 체크 항목 복사
	 */
	public function PostSysCopy(){
		if(!$this->managerIs) JSON(false, _MSG_WRONG_CONNECTED);
		if(EmptyPost('seq')) JSON(false, '복사할 게시물을 선택하여 주세요.');
		if(EmptyPost('bid') || EmptyPost('subid')) JSON(false, '이동할 게시판을 선택하여 주세요.');
		$chk = explode(',', Post('seq'));

		$chk = $this->_CheckArticleCopy($chk, Post('bid'), Post('subid'));
		JSON(true);
	}

	/**
	 * @param array $arr
	 * @param string $bid
	 * @param string $subid
	 * @param string $type
	 * @return int[]
	 */
	protected function _CheckArticleCopy($arr, $bid, $subid, $type = 'copy'){
		$returnArray = array();
		if(!is_array($arr)) $arr = array($arr);

		$newTable = TABLE_FIRST . 'bbs_' . $bid;
		$newUploadDir = '/board/'.$bid.(strlen($subid) ? '-' . $subid  : '').'/'.date('ym').'/';
		$newRepUploadDir = '/reply/'.$bid.(strlen($subid) ? '-' . $subid  : '').'/'.date('ym').'/';
		$newUploadImageDir = '/boardimage/'.$bid.(strlen($subid) ? '-' . $subid  : '').'/'.date('ym').'/';

		$boardModel = new \BoardModel();
		$boardModel->bid = $bid;
		$boardModel->table = TABLE_FIRST.'bbs_'.$boardModel->bid;
		$boardModel->_pwd->HtmlType = \HTMLType::InputText;

		$except = array('file1', 'file2', 'thumbnail', 'seq', 'reg_date', 'sort1', 'sort2', 'subid', 'category', 'sub_category');
		if($type == 'copy'){
			$except = array_merge($except, array('hit','reply_cnt', 'recommend', 'report', 'read', 'scrap', 'oppose'));
		}
		$repExcept = array('article_seq', 'file');
		$sort1Arr = array();

		foreach($arr as $seq){
			$qry = DB::GetQryObj($this->model->table)
				->AddWhere('seq = %d', $seq)
				->SetSort('sort1 DESC, sort2 DESC');
			$this->_R_CommonQry($qry);
			$row = $qry->Get();

			// 게시물 복사
			$fcRes1 = $this->_FileCopy($newUploadDir, $row['file1']);
			$fcRes2 = $this->_FileCopy($newUploadDir, $row['file2']);

			foreach($boardModel->data as $k => $d){
				if(isset($row[$k]) && !in_array($k, $except)){
					$boardModel->data[$k]->SetValue($row[$k]);
				}
			}

			$boardModel->SetValue('subid', $subid);
			$boardModel->SetValue('category', Post('category'));
			$boardModel->SetValue('sub_category', Post('sub_category'));

			if($fcRes1['path']) $boardModel->SetValue('file1', $fcRes1['path'].($fcRes1['name'] ? '*' . $fcRes1['name'] : ''));
			if($fcRes2['path']) $boardModel->SetValue('file2', $fcRes2['path'].($fcRes2['name'] ? '*' . $fcRes2['name'] : ''));
			if(isset($sort1Arr[$row['sort1']]) && strlen($sort1Arr[$row['sort1']])){
				$boardModel->SetValue('sort1', $sort1Arr[$row['sort1']]);
			}
			else{
				$boardModel->SetQueryValue('sort1', '(SELECT sort1 FROM (SELECT IF(COUNT(B.sort1) < 1, 0, MIN(B.sort1)) -1 as sort1 FROM ' . $boardModel->table . ' as B) as C)');
			}
			$boardModel->SetValue('sort2', $row['sort2']);
			$boardModel->SetValue('reg_date', date('Y-m-d H:i:s'));


			$this->_R_CheckArticleCopyInsBefore($boardModel); // Reserved

			$res = $boardModel->DBInsert();
			if(!$res->result) return $returnArray;
			$article_seq = $res->id;

			$newRow = DB::GetQryObj($newTable)
				->SetKey('sort1')
				->AddWhere('seq = %d', $article_seq)
				->Get();
			$sort1Arr[] = array($row['sort1'] => $newRow['sort1']);

			$returnArray[] = $row['seq'];


			// 이미지 복사
			$dbGetList = DB::GetListQryObj($this->model->imageTable)
				->AddWhere('article_seq='.$seq);
			$this->_R_CommonQry($dbGetList);
			$imgCopyCnt = 0;
			while($img = $dbGetList->Get()){
				$fcRes = $this->_FileCopy($newUploadImageDir, $img['image']);

				DB::InsertQryObj($newTable . '_images')
					->SetDataStr('imagename', $img['imagename'])
					->SetDataNum('article_seq', $article_seq)
					->SetDataStr('image', $fcRes['path'])
					->SetDecrementKey('seq')
					->Run();
				if($row['thumbnail'] == $img['image']){
					DB::UpdateQryObj($newTable)
						->SetDataStr('thumbnail', $fcRes['path'])
						->AddWhere('seq = %d', $article_seq)
						->Run();
				}
				$imgCopyCnt++;
				$row['content'] = str_replace($img['image'], $fcRes['path'], $row['content']);
			}
			if($imgCopyCnt){
				DB::UpdateQryObj($newTable)
					->SetDataStr('content', $row['content'])
					->AddWhere('seq = %d', $article_seq)
					->Run();
			}

			$this->_R_CheckArticleCopyInsAfter($type, $row['seq'], $this->bid, $row['subid'], $article_seq, $bid, $subid); // Reserved

			if($type == 'move'){
				// 액션 복사
				DB::SQL()->Query('INSERT INTO %1 (SELECT `action_type`, %d as `article_seq`, `muid`, `reg_date` FROM %1 WHERE article_seq = %d)', $newTable . '_action', $article_seq, $this->model->table . '_action', $row['seq']);

				// 리플 복사
				$dbGetList = DB::GetListQryObj($this->model->table.'_reply')
					->AddWhere('article_seq='.$seq);
				$this->_R_CommonQry($dbGetList);
				while($rep = $dbGetList->Get()){


					$replyModel = new \ReplyModel();
					$replyModel->bid = $bid;
					$replyModel->table = TABLE_FIRST.'bbs_'.$replyModel->bid.'_reply';

					$fcRes = $this->_FileCopy($newRepUploadDir, $rep['file']);
					if($fcRes['path']) $replyModel->SetValue('file', $fcRes['path'].($fcRes['name'] ? '*' . $fcRes['name'] : ''));

					foreach($replyModel->data as $k => $d){
						if(isset($rep[$k]) && !in_array($k, $repExcept)){
							$replyModel->data[$k]->SetValue($rep[$k]);
						}
					}

					$replyModel->SetValue('article_seq', $article_seq);
					$this->_R_CheckArticleReplyCopyInsBefore($replyModel); // Reserved
					$res = $replyModel->DBInsert();
					if($res->result) $this->_R_CheckArticleReplyCopyInsAfter($type, $rep['seq'], $this->bid, $row['subid'], $res->id, $bid, $subid); // Reserved
				}
			}
		}

		return $returnArray;
	}

	protected function _FileCopy($dir, $file){
		$filePath = $this->model->GetFilePathByValue($file);
		$fileName = $this->model->GetFileNameByValue($file);
		if($filePath && file_exists(_UPLOAD_DIR.$filePath)){

			if(!is_dir(_UPLOAD_DIR . $dir)) mkdir(_UPLOAD_DIR . $dir, 0777, true);

			$temp = explode('.', $filePath);
			$ext = end($temp);
			$newFilePath = $dir . \_ModelFunc::RandomFileName() . '.' . $ext;
			if(file_exists(_UPLOAD_DIR.$filePath)) copy(_UPLOAD_DIR.$filePath, _UPLOAD_DIR.$newFilePath);
			return array('name' => $fileName, 'path' => $newFilePath);
		}
		return array('name' => '', 'path' => '');
	}

	/**
	 * @param array $arr
	 */
	protected function _CheckArticleRemove($arr){
		if(!is_array($arr)) $arr = array($arr);

		foreach($arr as $seq){
			$qry = DB::GetQryObj($this->model->table)
				->AddWhere('seq = %d', $seq);
			$this->_R_CommonQry($qry);
			$row = $qry->Get();

			// 이미지 삭제
			$dbGetList = DB::GetListQryObj($this->model->imageTable)
				->AddWhere('article_seq='.$seq);
			$this->_R_CommonQry($dbGetList);
			while($img = $dbGetList->Get()){
				if(file_exists(_UPLOAD_DIR.$img['image'])) @UnlinkImage(_UPLOAD_DIR.$img['image']);
				$qry = DB::DeleteQryObj($this->model->table.'_images')
					->AddWhere('article_seq = '.$img['article_seq'])
					->AddWhere('seq = '.$img['seq']);
				$this->_R_CommonQry($qry);
				$qry->Run();
			}

			// 댓글 삭제
			$dbGetList = DB::GetListQryObj($this->model->table.'_reply')
				->AddWhere('article_seq='.$seq);
			$this->_R_CommonQry($dbGetList);
			while($rep = $dbGetList->Get()){
				$f = $this->model->GetFilePathByValue($rep['file']);
				if(file_exists(_UPLOAD_DIR.$f)) @UnlinkImage(_UPLOAD_DIR.$f);
				DB::DeleteQryObj($this->model->table.'_reply')
					->AddWhere('article_seq = '.$rep['article_seq'])
					->AddWhere('seq = '.$rep['seq'])
					->Run();
			}


			// 액션 삭제
			DB::DeleteQryObj($this->model->table . '_action')
				->AddWhere('article_seq = '.$row['seq'])
				->Run();

			// 게시물 삭제
			$f = $this->model->GetFilePathByValue($row['file1']);
			if($f && file_exists(_UPLOAD_DIR.$f)){
				@UnlinkImage(_UPLOAD_DIR.$f);
			}
			$f = $this->model->GetFilePathByValue($row['file2']);
			if($f && file_exists(_UPLOAD_DIR.$f)){
				@UnlinkImage(_UPLOAD_DIR.$f);
			}
			$qry = DB::DeleteQryObj($this->model->table)
				->AddWhere('seq = '.$row['seq']);
			$this->_R_CommonQry($qry);
			$qry->Run();

			$this->_R_CheckArticleRemoveAfter($seq, $this->bid, $row['subid']);
		}
	}

	/**
	 * 게시물 액션 가져오기
	 *
	 * @return ArticleAction
	 */
	protected function GetArticleAction(){
		if(!strlen(App::$ID)) JSON(false,  _MSG_WRONG_CONNECTED);
		if(!isset($_SESSION['boardView']['bid']) || !isset($_SESSION['boardView']['seq']) || $_SESSION['boardView']['bid'] != $this->bid || $_SESSION['boardView']['seq'] != App::$ID) JSON(false, '마지막으로 본 게시물만 가능합니다.');

		$seq = to10(App::$ID);
		return ArticleAction::GetInstance($this->bid)
			->SetArticleSeq($seq)
			->SetMUid($_SESSION['member']['muid'])
			->SetParentTable($this->bid);
	}

	protected function _GetBoardData($id){
		$args = func_get_args();
		$this->model->DBGet($args);
	}

	public function _DirectView(){
		$this->View();
	}

	public function _GetAdditionalSubidList(){
		$temp = array();
		if(sizeof($this->additionalSubId)){
			$qry = \BoardManagerModel::GetBoardListQry()
				->AddWhere('A.bid = %s', $this->bid)
				->AddWhere('A.subid IN (%s)', $this->additionalSubId)
				->SetGroup('');
			$data = array();
			while($row = $qry->Get()){
				if(isset($temp[$row['subid']]) && strlen($temp[$row['subid']]) < strlen($row['category'])){
					$temp[$row['subid']] = $row['category'];
					$data[$row['subid']] = $row['title'] ? $row['title'] : $row['subject'];
				}
				else $data[$row['subid']] = $row['title'] ? $row['title'] : $row['subject'];
			}
			return $data;
		}
		return array();
	}

	public function _CheckActionModal(){
		if($this->managerIs){
			$bmList = DB::GetListQryObj(TABLE_BOARD_MNG)
				->SetSort('`bid`, `subject`')
				->GetRows();
			$html = '<div id="checkActionModal" class="modal_layer" data-close-type="hidden">
		<div class="modal_wrap">
			<header class="modal_header">
				<h1>123</h1>
				<button type="button" class="close"><i class="cross"></i></button>
			</header>
			<div class="modal_contents">
				<form id="cActForm" name="cActForm" method="post" action="" data-move-url="' . App::URLAction('SysMove') . '" data-copy-url="' . App::URLAction('SysCopy') . '">
					<input type="hidden" name="bid" value="">
					<input type="hidden" name="subid" value="">
					<input type="hidden" name="seq" value="">
					<div class="selected" id="boardActionSelected"><span>선택게시판 : </span><b></b></div>
					<div class="selectedCategory" id="boardActionCategory"></div>
					<ul>';
			foreach($bmList as $v){
				$html .= '<li>
								<button type="button" class="boardActionArticleBtn" data-bid="' . GetDBText($v['bid']) .'" data-subid="' . GetDBText($v['subid']) .'" id="btn-' . GetDBText($v['bid']) .'-'. GetDBText($v['subid']) . '" data-category="'. GetDBText($v['category']) . '" data-sub-category="'. GetDBText($v['sub_category']) . '">'. GetDBText($v['bid']) . ' - '. GetDBText($v['subject']) .'('. GetDBText($v['subid']).')' . '</button>
							</li>';
			}

			$html .= '</ul>
							<div class="bottomBtn">
								<button type="submit" class="mBtn btn2">확인</button>
								<button type="button" class="mBtn close">닫기</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		
			<script>
				AppBoard.CheckActionInit();
			</script>';
			return $html;
		}
		return '';
	}
}