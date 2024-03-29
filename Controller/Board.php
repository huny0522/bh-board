<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use \Custom\ArticleAction;
use \Common\MenuHelp;
use Custom\Email;
use \DB as DB;

class Board{

	public $connName = '';

	/**
	 * @var \BoardModel
	 */
	public $model;
	/**
	 * @var \BoardManagerModel
	 */
	public $boardManger;
	public $managerIs = false;
	public $moreListIs = false;
	public $getListIs = false;
	public $path = '';
	public $adminPathIs = false;
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
	/** @param PostsCopyOrMoveParam */
	protected function _R_CheckArticleCopyInsAfter($param){}
	protected function _R_CheckArticleReplyCopyInsBefore($model){}
	/** @param PostsCopyOrMoveParam */
	protected function _R_CheckArticleReplyCopyInsAfter($param){}
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

	protected function _CheckMyMUid(&$obj, $key){
		if(is_array($obj)) return ($obj[$key] === \BHG::$session->member->muid->Get());
		else return ($obj->data[$key]->value === \BHG::$session->member->muid->Get());
	}

	/**
	 * 관리자 경로일때 권한 체크
	 */
	protected function _AdminPathAuth(){
		if(App::$settingData['GetUrl'][1] == \Paths::NameOfAdmin()){
			if(CM::GetAdminIs()) $this->adminPathIs = true;
			else{
				if(_JSONIS === true) JSON(false, App::$lang['MSG_WRONG_CONNECTED']);
				URLReplace(App::URLBase('Login'), App::$lang['MSG_WRONG_CONNECTED']);
			}
		}
	}

	protected function _ModelInit($modelName, $tid = ''){
		if(strlen($tid)) App::$tid = $tid;
		if(substr($modelName, -5) === 'Model') $modelName = substr($modelName, 0, -5);
		$this->model = App::InitModel($modelName, $this->connName);
		$this->boardManger = new \BoardManagerModel($this->connName);
	}

	protected function _IdSet(){
		$this->bid = App::$tid;
		$this->subid = App::$sub_tid;

		if(isset(App::$settingData['additionalSubid'])){
			if(is_string(App::$settingData['additionalSubid']) && strlen(App::$settingData['additionalSubid'])){
				App::$settingData['additionalSubid'] = explode(',', App::$settingData['additionalSubid']);
			}
			if(is_array(App::$settingData['additionalSubid']) && sizeof(App::$settingData['additionalSubid'])){
				$this->additionalSubId = App::$settingData['additionalSubid'];
				$this->additionalSubId[] = $this->subid;
			}
		}

		if(sizeof($this->additionalSubId)){
			if(!in_array(App::$action, array('Index', 'MoreList', 'Write')) && strlen(App::$id)){
				$dt = DB::GetQryObj($this->model->table)
					->SetConnName($this->connName)
					->SetKey('subid')
					->AddWhere('seq = %d', to10(App::$id))
					->Get();
				if(isset($dt['subid']) && strlen($dt['subid'])) $this->subid = $dt['subid'];
			}

			else if(App::$action === 'Write' && !EmptyGet('subid')){
				$this->subid = Get('subid');
			}
		}

		if(isset(App::$settingData['boardCategory'])) $this->menuCategory = trim(App::$settingData['boardCategory']);

		if(isset(App::$settingData['boardSubCategory'])) $this->menuSubCategory = trim(App::$settingData['boardSubCategory']);

		$this->userActionTable = $this->model->table . '_action';
	}

	protected function _DirSet(){
		$this->uploadDir = '/board/'.$this->bid.(strlen($this->subid) ? '-' . $this->subid  : '').'/'.date('ym').'/';
		$this->uploadImageDir = '/boardimage/'.$this->bid.(strlen($this->subid) ? '-' . $this->subid  : '').'/'.date('ym').'/';
		$this->model->uploadDir = $this->uploadDir;
	}

	protected function _BoardSetting(){
		if(!isset($this->bid) || $this->bid == '') URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);

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
			$this->model->data['file1']->htmlType = \HTMLType::FILE_IMAGE;
			$this->model->data['file2']->htmlType = \HTMLType::FILE_IMAGE;
		}


		$this->_SetFilePath(App::$action);

		$path = '/Board/'.App::$nativeSkinDir.'/'.$this->boardManger->GetValue('skin').'/';
		if(file_exists(\Paths::DirOfSkin().$path.'MoreList.html')) $this->moreListIs = true;
		else if(file_exists(\Paths::DirOfSkin().$path.'GetList.html')) $this->getListIs = true;

		$this->_SetLayoutPath();

		$this->_SetCategory();

		if(!$this->adminPathIs && \BHG::$isMember !== true && $this->boardManger->GetValue('man_to_man') === 'y') URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
		if($this->boardManger->GetValue('man_to_man') === 'y') $this->boardManger->SetValue('use_secret', 'n');
	}

	public function _SetCategory(){
		App::$data['category'] = array();
		App::$data['subCategory'] = array();

		if(strlen($this->menuCategory)) App::$data['subCategory'] =  $this->boardManger->GetSubCategory($this->menuCategory);

		else if(!EmptyGet('cate')) App::$data['subCategory'] = $this->boardManger->GetSubCategory(Get('cate'));

		if(!is_null($this->boardManger->GetValue('category')) && strlen($this->boardManger->GetValue('category'))){
			$temp = explode(',', $this->boardManger->GetValue('category'));
			foreach($temp as $v){
				$v = trim($v);
				if(strlen($v)) App::$data['category'][] = $v;
			}
		}
	}

	protected function _ActionToFileName($fileName){
		if($fileName == 'Answer' || $fileName == 'Modify') $fileName = 'Write';
		else if($fileName == '_DirectView') $fileName = 'View';
		return $fileName;
	}

	protected function _SetFilePath($fileName){
		$fileName = $this->_ActionToFileName($fileName);
		if($this->adminPathIs){
			$this->path = '/Board/Admin/'.$this->boardManger->GetValue('skin').'/';
			if(!file_exists(\Paths::DirOfSkin().$this->path.$fileName.'.html')) $this->path = '/Board/Admin/';

			App::$html = $this->path . $fileName.'.html';
		}
		else{
			$this->path = '/Board/'.App::$nativeSkinDir.'/'.$this->boardManger->GetValue('skin').'/';
			if(file_exists(\Paths::DirOfSkin().$this->path.$fileName.'.html')) App::$html = $this->path.$fileName.'.html';
			else{
				$this->path = '/Board/'.App::$nativeSkinDir.'/';
				if(file_exists(\Paths::DirOfSkin().$this->path.$fileName.'.html')) App::$html = $this->path.$fileName.'.html';
				else{
					$this->path = '/Board/'.$this->boardManger->GetValue('skin').'/';
					if(file_exists(\Paths::DirOfSkin().$this->path.$fileName.'.html')) App::$html = $this->path.$fileName.'.html';
					else{
						$this->path = '/Board/';
						App::$html = '/Board/' . $fileName.'.html';
					}
				}
			}
		}

	}

	protected function _SetLayoutPath(){
		$layout = $this->boardManger->GetValue('layout');

		// 관리자
		if($this->adminPathIs){
			App::$layout = '_Admin';
			$this->moreListIs = false;
			$this->getListIs = false;
			$this->boardManger->SetValue('article_count', 20);
		}
		else if($layout){
			$layoutPath = App::$nativeSkinDir.'/'.$layout;

			$e = explode('.', $layoutPath);
			if(sizeof($e) > 1){
				$ext = array_pop($e);
				if($ext !== 'html' && $ext !== 'php') $layoutPath = implode('.', $e) . '.html';
			}
			else{
				$layoutPath .= '.html';
			}

			if(file_exists(\Paths::DirOfSkin().'/Layout/'.$layoutPath)) $layout = $layoutPath;
			App::$layout = $layout;
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
		$memberLevel = \BHG::$isMember === true ? \BHG::$session->member->level->Get() : 0;
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
			if(\BHG::$isMember !== true) URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
			URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}
		if($this->getListIs || $this->moreListIs){
			if(_JSONIS === true) JSON(true, '', App::GetView($this->model));
			else  App::View($this->model);
			return;
		}

		$this->GetList();
	}

	public function _SearchQuery($qry, $noticeIs = false){

		if(\BHG::$isMember === true){
			$blockUser = CM::GetBlockUsers();
			if(sizeof($blockUser)){
				$qry->AddWhere('`A`.`muid` NOT IN (%d)', $blockUser);
			}
		}

		$s_keyword = Get('keyword');
		$s_type = Get('stype');

		App::$data['categoryKeyword'] = strlen($this->menuCategory) ? $this->menuCategory : Get('cate');
		if(sizeof($this->additionalSubId)){
			$qry->AddWhere('`A`.`subid` IN (%s)', $this->additionalSubId);
		}
		else $qry->AddWhere('`A`.`subid` = %s', $this->subid);

		if(strlen((string)App::$data['categoryKeyword'])) $qry->AddWhere('`A`.category = %s', App::$data['categoryKeyword']);

		if(strlen($this->menuSubCategory)) $qry->AddWhere('`A`.sub_category IN (%s)', explode(',', $this->menuSubCategory));
		else if(!EmptyGet('scate')) $qry->AddWhere('`A`.sub_category = %s', Get('scate'));


		if(!$noticeIs){
			if(strlen((string)$s_type) && strlen((string)$s_keyword)){
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

	/**
	 * @param bool $viewPageIs
	 * @param callable(BH_DB_GetListWithPage) $listQueryFunc 쿼리 변형을 위한 함수
	 * @return string
	 */
	public function GetList($viewPageIs = false, $listQueryFunc = null){
		$res = $this->GetAuth('List');
		if(!$res){
			if(\BHG::$isMember !== true) URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
			URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}

		$s_keyword = Get('keyword');
		$s_page = Get('page');


		// 공지를 불러온다.
		App::$data['notice'] = array();
		if(($s_page < 2) && !strlen((string)$s_keyword)) App::$data['notice'] = $this->_GetNotice()->data;

		// 리스트를 불러온다.
		$dbList = $this->model->GetPageListQuery($s_page, $this->boardManger->GetValue('article_count'));

		if($this->boardManger->_list_show_notice->Txt() == 'n' && ($s_page < 2) && !strlen((string)$s_keyword)) $dbList->AddWhere('A.notice=\'n\'');

		$this->_R_CommonQry($dbList);

		if(!$this->adminPathIs){
			$dbList->AddWhere('A.delis=\'n\'');
			if(!$this->managerIs && $this->boardManger->GetValue('man_to_man') === 'y') $dbList->AddWhere('A.muid = %d OR A.target_muid = %d', \BHG::$session->member->muid->Get(), \BHG::$session->member->muid->Get());
		}

		$this->_SearchQuery($dbList);

		$this->_R_GetListQuery($dbList); // Reserved
		if(is_callable($listQueryFunc)) $listQueryFunc($dbList);
		$dbList->DrawRows();
		$this->_RowSet($dbList->data);

		if(App::$action !== 'Index') $this->_SetFilePath($this->getListIs ? 'GetList' : ($this->moreListIs ? 'MoreList' : 'Index'));

		if($viewPageIs) return App::GetOnlyView($this->model, $dbList);
		else if(_JSONIS === true) JSON(true, '', App::GetView($this->model, $dbList));
		else  App::View($this->model, $dbList);
	}

	/**
	 * @param bool $backIs
	 * @param callable(BH_DB_GetList) $listQueryFunc 쿼리 변형을 위한 함수
	 * @return string
	 */
	public function MoreList($backIs = false, $listQueryFunc = null){
		$res = $this->GetAuth('List');
		if(!$res){
			if(\BHG::$isMember !== true) URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
			URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}

		$s_keyword = Get('keyword');
		$s_seq = Get('seq');
		$s_last_seq = Get('lastSeq');


		// 공지를 불러온다.
		App::$data['notice'] = array();
		if(!strlen((string)$s_seq) && !strlen((string)$s_last_seq) && !strlen((string)$s_keyword)) App::$data['notice'] = $this->_GetNotice()->data;

		// 리스트를 불러온다.
		$dbList = $this->model->GetListQuery($this->boardManger->GetValue('article_count'));

		if($this->boardManger->_list_show_notice->Txt() == 'n' && !strlen((string)$s_keyword)) $dbList->AddWhere('A.notice=\'n\'');

		$this->_R_CommonQry($dbList);

		if(!$this->adminPathIs){
			$dbList->AddWhere('A.delis=\'n\'');
			if(!$this->managerIs && $this->boardManger->GetValue('man_to_man') === 'y') $dbList->AddWhere('A.muid = %d OR A.target_muid = %d', \BHG::$session->member->muid->Get(), \BHG::$session->member->muid->Get());
		}

		if(strlen((string)$s_seq)){
			$seq = to10($s_seq);
			$dbList->AddWhere('A.seq = %d', $seq);
		}
		else{
			$this->_MoreListNext($dbList, $s_last_seq, $backIs);

			$this->_SearchQuery($dbList);
		}

		$this->_R_MoreListQuery($dbList);  // Reserved
		if(is_callable($listQueryFunc)) $listQueryFunc($dbList);
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

	public function MoreListPrev(){
		$this->MoreList(true);
	}

	/**
	 * 마지막 seq값으로 다음 페이지를 찾기.
	 *
	 * @param \BH_DB_GetList $dbList
	 * @param int $s_last_seq
	 * @param bool $backIs
	 */
	protected function _MoreListNext($dbList, $s_last_seq, $backIs){
		if(strlen($s_last_seq)){
			$qry = DB::GetQryObj($this->model->table.' A')
				->SetConnName($this->connName)
				->AddWhere('A.seq = %d', $s_last_seq)
				->SetKey('A.sort1, A.sort2');
			$this->_R_CommonQry($qry);
			$last = $qry->Get();

			if($last){
				if($backIs) $dbList->AddWhere('A.sort1 < %d OR (A.sort1 = %d AND A.sort2 < %d)', $last['sort1'], $last['sort1'], $last['sort2'])->SetSort('A.sort1 DESC, A.sort2 DESC');
				else $dbList->AddWhere('A.sort1 > %d OR (A.sort1 = %d AND A.sort2 > %d)', $last['sort1'], $last['sort1'], $last['sort2']);
			}
			else{
				if($backIs) $dbList->AddWhere('A.seq < %d', $s_last_seq)->SetSort('A.seq DESC');
				else  $dbList->AddWhere('A.seq > %d', $s_last_seq);
			}
		}
	}

	public function _RowSet(&$data){
		$ck = strlen((string)App::$data['categoryKeyword']) ? true : false;
		foreach($data as &$row){
			if($this->managerIs || $row['secret'] == 'n' || ($row['first_member_is'] == 'y' && strlen($row['muid']))) $row['possibleView'] = true;
			else $row['possibleView'] = false;
			$row['viewUrl'] = App::URLAction('View/').toBase($row['seq']).App::GetFollowQuery();
			$row['replyCount'] = $row['reply_cnt'] ? '<span class="ReplyCount">['.$row['reply_cnt'].']</span>' : '';
			$row['newArticleIs'] = (time() - strtotime($row['reg_date']) < $this->boardManger->data['new_view_day']->value * 60 * 60 * 24);
			$row['viewCategory'] = $ck ? (strlen($row['sub_category']) ? $row['sub_category'] : $row['category']) : $row['category'];
		}
	}

	public function PostView(){
		$this->View();
	}

	/**
	 * @param callable(array) $printBeforeFunc
	 */
	public function View($printBeforeFunc = null){
		if($this->boardManger->GetValue('list_in_view') == 'y' && !$this->moreListIs){
			App::$data['List'] = $this->GetList(true);
			$this->_SetFilePath('View');
		}

		if(!isset(App::$id) || !strlen(App::$id)) URLReplace('-1');

		$seq = to10(App::$id);

		$viewAuth = $this->GetAuth('View');
		if(!$viewAuth){
			if(\BHG::$isMember !== true) URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
			URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}

		$this->_GetBoardData($seq);

		if(!$this->adminPathIs){
			if($this->model->GetValue('delis') == 'y') URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);
			if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && !$this->_CheckMyMUid($this->model, 'muid') && !$this->_CheckMyMUid($this->model, 'target_muid')) URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);
		}

		$data['answerAuth'] = $this->GetAuth('Answer');

		// 비밀번호없이 수정권한
		$data['modifyAuthDirect'] = false;
		if($this->GetAuth('Write') && \BHG::$isMember === true && ($this->_CheckMyMUid($this->model, 'muid')|| CM::GetAdminIs() )){
			$data['modifyAuthDirect'] = true;
		}

		// 비밀글일경우 권한 : 관리자 또는 게시판 매니저, 글쓴이
		if(!CM::GetAdminIs() && $this->model->GetValue('secret') == 'y'){
			$viewAuth = false;

			// first_seq 가 있으면 첫째글을 호출
			if(strlen($this->model->GetValue('first_seq'))){
				$qry = DB::GetQryObj($this->model->table)
					->SetConnName($this->connName)
					->AddWhere('seq=' . $this->model->GetValue('first_seq'));
				$this->_R_CommonQry($qry);
				$firstDoc = $qry->Get();

			}

			if(\BHG::$isMember === true){
				// 자신의 글 권한
				if($this->_CheckMyMUid($this->model, 'muid') || (isset($firstDoc) && $this->model->GetValue('first_member_is') == 'y' && $this->_CheckMyMUid($firstDoc, 'muid'))){
					$viewAuth = true;
				}
			}

			// 원글이나 현재 글이 비회원글일 경우 비밀번호를 체크
			if(!$viewAuth && (!$this->model->GetValue('muid') || $this->model->GetValue('first_member_is') == 'n')){
				if(_POSTIS !==	true || !isset($_POST['pwd'])) URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);

				if(_password_verify(Post('pwd'), $this->model->GetValue('pwd')) || (isset($firstDoc) && _password_verify(Post('pwd'), $firstDoc['pwd']))){
					$viewAuth = true;
				}
				else URLReplace('-1', App::$lang['MSG_WRONG_PASSWORD']);
			}

			if(!$viewAuth) URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}

		$cookieName = $this->model->table.$seq;
		if(!isset($_COOKIE[$cookieName]) || !$_COOKIE[$cookieName]){
			$dbUpdate = DB::UpdateQryObj($this->model->table)
				->SetConnName($this->connName)
				->SetData('hit', 'hit + 1')
				->AddWhere('seq='.$seq);
			$this->_R_CommonQry($dbUpdate);
			$dbUpdate->Run();

			setcookie($cookieName, 'y');
		}

		App::$data['boardActionData'] = array();
		if(\BHG::$isMember == true){
			$res = ArticleAction::GetInstance($this->bid)
				->SetConnName($this->connName)
				->SetArticleSeq($seq)
				->SetMUid(\BHG::$session->member->muid->Get())
				->GetAllAction();
			if($res->result) App::$data['boardActionData'] = $res->data;

			if(!$this->_CheckMyMUid($this->model, 'muid') && !isset(App::$data['boardActionData']['read'])){
				ArticleAction::GetInstance($this->bid)
					->SetConnName($this->connName)
					->SetArticleSeq($seq)
					->SetMUid(\BHG::$session->member->muid->Get())
					->SetParentTable($this->bid)
					->Read();
			}
		}

		App::$data['recommendButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$id) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$id) . '" data-type="recommend" class="boardActionBtn boardRecommendActionBtn' .(isset(App::$data['boardActionData']['recommend']) ? ' already' : ''). '"><b>추천</b> <span class="num">' . ($this->model->_recommend->Txt()) . '</span></a>';

		App::$data['scrapButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$id) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$id) . '" data-type="scrap" class="boardActionBtn boardSubscribeActionBtn' .(isset(App::$data['boardActionData']['scrap']) ? ' already' : ''). '"><b>스크랩</b> <span class="num">' . ($this->model->_scrap->Txt()) . '</span></a>';

		App::$data['opposeButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$id) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$id) . '" data-type="oppose" class="boardActionBtn boardOpposeActionBtn' .(isset(App::$data['boardActionData']['oppose']) ? ' already' : ''). '"><b>반대</b> <span class="num">' . ($this->model->_oppose->Txt()) . '</span></a>';

		App::$data['reportButton'] = '<a href="' . App::URLAction('JSONAction') . '/' .  GetDBText(App::$id) . '" data-cancel-href="' . App::URLAction('JSONCancelAction') . '/' . GetDBText(App::$id) . '" data-type="report" class="boardActionBtn boardReportActionBtn' .(isset(App::$data['boardActionData']['report']) ? ' already' : ''). '"><b>신고</b> <span class="num">' . ($this->model->_report->Txt()) . '</span></a>';

		\BHG::$session->boardView->bid->Set($this->bid);
		\BHG::$session->boardView->seq->Set(App::$id);

		$this->_R_ViewEnd($data);  // Reserved
		if(is_callable($printBeforeFunc)) $printBeforeFunc($data);

		if(_JSONIS === true) JSON(true, '', App::GetView($this->model, $data));
		else App::View($this->model, $data);
	}

	/**
	 * @param callable $printBeforeFunc
	 */
	public function Write($printBeforeFunc = null){
		$res = $this->GetAuth('Write');
		if(!$res){
			if(\BHG::$isMember !== true) URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
			URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}

		if(sizeof($this->additionalSubId)){
			$arr = $this->_GetAdditionalSubidList();

			if(sizeof($arr)){
				if(EmptyGet('subid')) URLReplace(-1, App::$lang['MSG_WRONG_CONNECTED']);
				else{
					$this->subid = Get('subid');
					if(!isset($arr[$this->subid])) URLRedirect(-1, App::$lang['NO_BOARD']);
				}
			}
		}

		$this->_R_WriteEnd();  // Reserved
		if(is_callable($printBeforeFunc)) $printBeforeFunc();
		if(_JSONIS === true) JSON(true, '', App::GetView($this->model));
		else App::View($this->model);
	}

	/**
	 * @param callable $printBeforeFunc
	 */
	public function Answer($printBeforeFunc = null){
		$res = $this->GetAuth('Answer');
		if(!$res){
			if(\BHG::$isMember !== true) URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
			URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}
		$seq = to10(StrLenPost('target') ? Post('target') : Get('target'));
		if(!strlen($seq)) URLReplace('-1');

		$qry = DB::GetQryObj($this->model->table)
			->SetConnName($this->connName)
			->AddWhere('seq = %d', $seq);
		$this->_R_CommonQry($qry);
		$data = $qry->Get();
		if(!$this->adminPathIs){
			if($data['delis'] == 'y') URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);
			if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && !$this->_CheckMyMUid($data, 'muid') && !$this->_CheckMyMUid($data, 'target_muid')) URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);
		}

		$this->model->SetValue('subject', strpos('[' . App::$lang['ANSWER'] . ']', $data['subject']) === false ? '[' . App::$lang['ANSWER'] . '] '.$data['subject'] : $data['subject']);
		$this->model->SetValue('secret', $data['secret']);

		$this->_R_AnswerEnd();  // Reserved
		if(is_callable($printBeforeFunc)) $printBeforeFunc();

		if(_JSONIS === true) JSON(true, '', App::GetView($this->model));
		else App::View($this->model);
	}

	/**
	 * @param callable $printBeforeFunc
	 */
	public function Modify($printBeforeFunc = null){
		if(!isset(App::$id) || !strlen(App::$id)){
			URLReplace('-1');
		}

		$res = $this->GetAuth('Modify');
		if(!$res){
			if(\BHG::$isMember !== true) URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
			URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}

		$seq = to10(App::$id);
		$this->_GetBoardData($seq);
		if(!$this->adminPathIs){
			if($this->model->GetValue('delis') == 'y') URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);
			if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && !$this->_CheckMyMUid($this->model, 'muid') && !$this->_CheckMyMUid($this->model, 'target_muid')) URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);
		}

		// 회원 글 체크
		if(\BHG::$isMember !== true || !CM::GetAdminIs()){
			$res = $this->_PasswordCheck();
			if($res !== true) URLReplace('-1', $res);
		}

		$this->_R_ModifyEnd();  // Reserved
		if(is_callable($printBeforeFunc)) $printBeforeFunc();

		if(_JSONIS === true) JSON(true, '', App::GetView($this->model));
		else App::View($this->model);
	}

	/**
	 * @param callable $dbUpdateBefore
	 * @param callable $dbUpdateAfter
	 */
	public function PostModify($dbUpdateBefore = null, $dbUpdateAfter = null){
		if(Post('mode') == 'view'){
			$this->Modify();
			return;
		}
		$res = $this->GetAuth('Modify');
		if(!$res){
			if(\BHG::$isMember !== true) URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
			URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}

		$seq = to10(App::$id);

		$this->model->need = array('subject', 'content');
		if($this->boardManger->GetValue('use_secret') === 'y') $this->model->need = 'secret';
		if(\BHG::$isMember !== true) $this->model->need = 'mnane';
		else $this->model->AddExcept('pwd');

		$this->_GetBoardData($seq);
		$beforeFile = $this->model->_file1->value;
		if(!$this->adminPathIs){
			if($this->model->GetValue('delis') == 'y') URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);
			$this->model->AddExcept('delis');
			if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && !$this->_CheckMyMUid($this->model, 'muid') && !$this->_CheckMyMUid($this->model, 'target_muid')) URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);
		}

		$res = $this->model->SetPostValuesWithFile();
		if(!$res->result){
			$res->message ? $res->message : 'ERROR#101';
			if(_JSONIS === true) JSON(false, $res->message);
			App::$data['error'] = $res->message;
			App::View($this->model);
			return;
		}
		// 회원 글 체크
		if(\BHG::$isMember !== true || !CM::GetAdminIs()){
			$res = $this->_PasswordCheck();
			if($res !== true){
				if(_JSONIS === true) JSON(false, $res);
				App::$data['error'] = $res;
				App::View($this->model);
				return;
			}
		}

		// 기본 데이타
		$this->model->SetValue('htmlis', Post('htmlis') == 'y' ? 'y' : 'n');

		// 섬네일 등록
		if(IsImageFileName(isset($this->model->_file1->value) ? $this->model->_file1->value : '')){
			$this->model->_thumbnail->SetValue($this->model->GetFilePath('file1'));
		}

		$this->_R_PostModifyUpdateBefore();  // Reserved
		if(is_callable($dbUpdateBefore)) $dbUpdateBefore();

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)){
			if(_JSONIS === true) JSON(false, $error[0]);
			App::$data['error'] = $error[0];
			App::View($this->model);
			return;
		}

		if($this->model->_htmlis->value !== 'n') $this->model->_content->value = RemoveIFrame($this->model->_content->value);

		$res2 = $this->model->DBUpdate();
		$this->_ContentImageUpdate(Post('content'), $seq, 'modify');


		if($res2->result){
			$this->_R_PostModifyUpdateAfter();  // Reserved
			if(is_callable($dbUpdateAfter)) $dbUpdateAfter();
			if(_JSONIS === true) JSON(true, '',App::$lang['MSG_COMPLETE_MODIFY']);
			else{
				if(!isset($this->model->_muid->value) || !strlen($this->model->_muid->value)){
					echo '<form action="' . App::URLAction('View/') . App::$id . App::GetFollowQuery() . '" method="post" id="redirectForm">';
					echo '<input type="hidden" name="pwd" value="' . GetDBText(Post('pwd')). '">';
					echo '</form>';
					echo '<script>document.getElementById(\'redirectForm\').submit()</script>';
				}
				URLReplace(App::URLAction('View/'.App::$id).App::GetFollowQuery(), App::$lang['MSG_COMPLETE_MODIFY']);
			}
		}
		else{
			if(_JSONIS === true) JSON(false, $res2->message ? $res2->message : 'ERROR#102');
			App::$data['error'] = $res2->message ? $res2->message : 'ERROR#102';
			App::View($this->model);
			return;
		}
	}

	/**
	 * @param callable $dbInsertBefore
	 * @param callable $dbInsertAfter
	 */
	public function PostAnswer($dbInsertBefore = null, $dbInsertAfter = null){
		$this->PostWrite($dbInsertBefore, $dbInsertAfter);
	}

	/**
	 * @param callable $dbInsertBefore
	 * @param callable $dbInsertAfter
	 */
	public function PostWrite($dbInsertBefore = null, $dbInsertAfter = null){
		if(_POSTIS !== true) URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);

		$res = $this->GetAuth('Write');
		if(!$res){
			if(\BHG::$isMember !== true) URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
			URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}

		$first_seq = '';
		$first_member_is = 'n';


		if(App::$action == 'Answer'){
			$auth = $this->GetAuth('Answer');
			if(!$auth){
				if(\BHG::$isMember !== true) URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
				URLReplace('-1', App::$lang['MSG_NO_AUTH']);
			}

			$qry = DB::GetQryObj($this->model->table)
				->SetConnName($this->connName)
				->AddWhere('seq=%d', to10(Post('target')));
			$this->_R_CommonQry($qry);
			App::$data['targetData'] = $qry->Get();
			if(!$this->adminPathIs){
				if(App::$data['targetData']['delis'] == 'y') URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);
				if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && !$this->_CheckMyMUid(App::$data['targetData'], 'muid') && !$this->_CheckMyMUid(App::$data['targetData'], 'target_muid')) URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);
			}

			$first_seq = strlen(App::$data['targetData']['first_seq']) ? App::$data['targetData']['first_seq'] : App::$data['targetData']['seq'];
			$first_member_is = App::$data['targetData']['first_member_is'];
		}


		$result = new \BH_Result();

		if(!$this->adminPathIs) $this->model->AddExcept('delis');
		$this->model->need = array('subject', 'content');
		if($this->boardManger->GetValue('use_secret') === 'y') $this->model->need = 'secret';
		if(\BHG::$isMember === true){
			$member = CM::GetMember();
			$this->model->AddExcept('pwd');
		}

		$res = $this->model->SetPostValuesWithFile();

		if(!$res->result){
			$res->message ? $res->message : 'ERROR#101';
			if(_JSONIS === true) JSON(false, $res->message);
			App::$data['error'] = $res->message;
			$this->Write();
			return;
		}

		// 기본 데이타
		$this->model->SetValue('reg_date', date('Y-m-d H:i:s'));
		$this->model->SetValue('htmlis', Post('htmlis') == 'y' ? 'y' : 'n');
		$this->model->_subid->SetValue($this->subid);


		// 회원유무
		if(\BHG::$isMember === true){
			$this->model->SetValue('muid', \BHG::$session->member->muid->Get());
			$this->model->SetValue('mlevel', $member['level']);
			$this->model->SetValue('email', $member['email']);
			$this->model->SetValue('mname', $member['nickname'] ? $member['nickname'] : ($member['mname'] ? $member['mname'] : $member['mid']));
		}

		// 답글쓰기라면 sort 정렬
		if(App::$action == 'Answer'){
			$qry = DB::UpdateQryObj($this->model->table)
				->SetConnName($this->connName)
				->SetData('sort2', 'sort2 + 1')
				->AddWhere('sort1 = %d', App::$data['targetData']['sort1'])
				->AddWhere('sort2 > %d', App::$data['targetData']['sort2'])
				->SetSort('sort2 DESC');
			$this->_R_CommonQry($qry);
			$res = $qry->Run();

			if(!$res->result){
				if(_JSONIS === true) JSON(false, 'ERROR#201');
				App::$data['error'] = 'ERROR#201';
				$this->Write();
				return;
			}
			$this->model->SetValue(array(
				'first_seq' => $first_seq,
				'first_member_is' => $first_member_is,
				'target_mname' => App::$data['targetData']['mname'],
				'category' => App::$data['targetData']['category'],
				'sub_category' => App::$data['targetData']['sub_category'],
				'subid' => App::$data['targetData']['subid'],
				'target_muid' => App::$data['targetData']['muid'] ? App::$data['targetData']['muid'] : 0,
				'sort1' => App::$data['targetData']['sort1'],
				'sort2' => App::$data['targetData']['sort2'] + 1,
				'depth' => App::$data['targetData']['depth'] + 1,
			));
		}else{
			$this->model->SetValue('first_member_is', \BHG::$isMember === true ? 'y' : 'n');
			$this->model->SetQueryValue('sort1', '(SELECT IF(COUNT(s.sort1) = 0, 0, MIN(s.sort1))-1 FROM '.$this->model->table.' as s)');
			if(sizeof($this->additionalSubId) && !EmptyPost('subid')){
				$this->model->_subid->SetValue(Post('subid'));
			}
		}

		$this->_R_PostWriteInsertBefore();  // Reserved
		if(is_callable($dbInsertBefore)) $dbInsertBefore();

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)){
			if(_JSONIS === true) JSON(false, $error[0]);
			App::$data['error'] = $error[0];
			$this->Write();
			return;
		}

		// 섬네일 등록
		if(IsImageFileName(isset($this->model->_file1->value) ? $this->model->_file1->value : '')){
			$this->model->_thumbnail->SetValue($this->model->GetFilePath('file1'));
		}

		if($this->model->_htmlis->value !== 'n') $this->model->_content->value = RemoveIFrame($this->model->_content->value);

		$res = $this->model->DBInsert();
		$result->result = $res->result;
		$result->message = $res->message;

		if($result->result){
			$this->_ContentImageUpdate(Post('content'), $res->id);
			$this->_R_PostWriteInsertAfter($res->id);  // Reserved
			if(is_callable($dbInsertAfter)) $dbInsertAfter($res->id);

			// 알람
			if(class_exists('PHPMailer\\PHPMailer\\PHPMailer') && App::$action == 'Answer' && App::$data['targetData']['email_alarm'] == 'y' && strlen(App::$data['targetData']['email']) && App::$cfg->Def()->sendEmail->value){
				$mail = new Email();
				$mail->AddMail(App::$data['targetData']['email'], App::$data['targetData']['mname']);
				if($this->adminPathIs) $url = \Paths::Url() . '/Board/' . $this->bid . '-'. $this->subid . '/View/' . toBase($res->id);
				else $url = App::URLAction('View/' . toBase($res->id));
				$mail->SendMailByAnswerAlarm(App::$data['targetData']['mname'], $url, $this->model->_mname->value, $this->model->_subject->value, ($this->model->_htmlis->value === 'y' ? $this->model->_content->SafeRaw() : $this->model->_content->SafeBr()));
			}

			if(_JSONIS === true){
				JSON(true, '', App::$lang['MSG_COMPLETE_REGISTER']);
			}
			else URLReplace(App::URLAction(), App::$lang['MSG_COMPLETE_REGISTER']);
		}else{
			if(_JSONIS === true) JSON(false, $result->message ? $result->message : 'ERROR');
			App::$data['error'] = $result->message ? $result->message : 'ERROR';
			$this->Write();
			return;
		}
	}

	/**
	 * @param callable $deleteBefore
	 * @param callable $deleteAfter
	 */
	public function PostDelete($deleteBefore = null, $deleteAfter = null){
		$res = $this->GetAuth('Write');
		if(!$res){
			if(\BHG::$isMember !== true) URLReplace(self::$loginUrl, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
			URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}

		$seq = to10(App::$id);

		$this->_GetBoardData($seq);

		if(!$this->adminPathIs){
			if($this->model->GetValue('delis') == 'y') URLReplace('-1', App::$lang['MSG_DELETED_ARTICLE']);
			if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && !$this->_CheckMyMUid($this->model, 'muid')) URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);
		}

		// 회원 글 체크
		if(\BHG::$isMember !== true || !$this->managerIs){
			$res = $this->_PasswordCheck();
			if($res !== true){
				URLReplace('-1', $res);
			}
		}

		$this->model->SetValue('delis', 'y');
		if(is_callable($deleteBefore)) $deleteBefore();
		$res = $this->model->DBUpdate();
		if(is_callable($deleteAfter)) $deleteAfter($res);

		if(_JSONIS === true) JSON(true, '', App::$lang['MSG_COMPLETE_DELETE']);
		else URLReplace(App::URLAction('').App::GetFollowQuery(), App::$lang['MSG_COMPLETE_DELETE']);
	}

	public function Undelete(){
		if(!$this->adminPathIs) URLReplace('-1', App::$lang['MSG_WRONG_CONNECTED']);

		$seq = to10(App::$id);

		$this->_GetBoardData($seq);
		$this->model->SetValue('delis', 'n');
		$this->model->DBUpdate();

		if(_JSONIS === true) JSON(true, '', App::$lang['COMPLETE_RESTORE']);
		else URLReplace(App::URLAction('').App::GetFollowQuery(), App::$lang['COMPLETE_RESTORE']);
	}

	public function Download(){
		if(strpos(App::$id, '-') !== false){
			$temp = explode('-', App::$id);
			$md = $temp[0];
			$seq = to10($temp[1]);
			$n = isset($temp[2]) ? $temp[2] : 0;
		}
		else{
			$seq = to10(App::$id);
			$md = 'file1';
			$n = 0;
		}
		$this->_GetBoardData($seq);
		$fileExplode = explode(';', $this->model->GetValue($md));
		if(!isset($fileExplode[$n])) $n = 0;
		$file = explode('*', $fileExplode[$n]);
		if(sizeof($file) < 2){
			$name = explode('/', $file[0]);
			$file[1] = end($name);
		}
		if(file_exists(\Paths::DirOfUpload() . $file[0]) && !is_dir(\Paths::DirOfUpload() . $file[0])) Download(\Paths::DirOfUpload() . $file[0], $file[1]);
		else URLRedirect(-1, App::$lang['TXT_FILE_NOT_EXIST']);
	}

	public function RepAttachDownload(){
		$repData = DB::GetQryObj($this->model->table . '_reply')
			->SetConnName($this->connName)
			->AddWhere('seq = %d', to10(App::$id))
			->SetKey('`file`')
			->Get();
		$file = explode('*', $repData['file']);
		if(sizeof($file) < 2){
			$name = explode('/', $file[0]);
			$file[1] = end($name);
		}
		if(file_exists(\Paths::DirOfUpload() . $file[0]) && !is_dir(\Paths::DirOfUpload() . $file[0])) Download(\Paths::DirOfUpload() . $file[0], $file[1]);
		else URLRedirect(-1, App::$lang['TXT_FILE_NOT_EXIST']);
	}

	/**
	 * 게시물을 DB에서 완전히 삭제
	 */
	public function PostRemove(){
		$seq = to10(App::$id);
		if(!$this->adminPathIs) URLReplace(-1, App::$lang['MSG_WRONG_CONNECTED']);
		$this->_CheckArticleRemove($seq);

		URLReplace(App::URLAction().App::GetFollowQuery());
	}

	/**
	 * 공지사항을 반환
	 *
	 * @param callable(\BH_DB_GetList) $func
	 * @return \BH_DB_GetList
	 */
	public function _GetNotice($func = null){
		$qry = $this->model->GetNoticeQuery();

		$this->_SearchQuery($qry);

		$this->_R_CommonQry($qry);
		$this->_R_NoticeQuery($qry);

		if(is_callable($func)) $func($qry);

		$qry->DrawRows();

		$this->_RowSet($qry->data);
		return $qry;
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
				->SetConnName($this->connName)
				->AddWhere('article_seq='.$seq);
			$this->_R_CommonQry($dbGetList);
			while($img = $dbGetList->Get()){
				if(strpos($content,$img['image']) === false){
					// 파일이 없으면 삭제
					@UnlinkImage(\Paths::DirOfUpload().$img['image']);

					if($img['image'] == $this->model->GetValue('thumbnail')) $this->model->SetValue('thumbnail', '');

					$qry = DB::DeleteQryObj($this->model->table.'_images')
						->SetConnName($this->connName)
						->AddWhere('article_seq = '.$img['article_seq'])
						->AddWhere('seq = '.$img['seq']);
					$this->_R_CommonQry($qry);
					$qry->Run();
				}
			}
		}

		$qry = DB::GetQryObj($this->model->imageTable)
			->SetConnName($this->connName)
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
					$uploadDir = \Paths::DirOfUpload().$this->uploadImageDir;
					if(!is_dir($uploadDir)){
						mkdir($uploadDir, 0777, true);
					}

					// 복사 전에 파일의 용량 체크 여기서 가능
					@copy(\Paths::DirOfUpload().$exp[0],\Paths::DirOfUpload().$newpath);
					$newContent = str_replace($exp[0],$newpath, $newContent);
					// 파일이 있으면 등록

					unset($dbInsert);
					// 여기 수정
					$dbInsert = DB::InsertQryObj($this->model->imageTable)
						->SetConnName($this->connName)
						->SetDataStr('image', $newpath)
						->SetDataStr('imagename', $exp[1])
						->SetDataDecrement('seq', array('article_seq' => $seq));
					$this->_R_CommonQry($dbInsert, 'ImageInsert');
					$dbInsert->Run();
					$imageCount++;
				}
				@UnlinkImage(\Paths::DirOfUpload().$exp[0]);
			}

			if($newContent != $content || !$this->model->GetValue('thumbnail')){
				if(!$this->model->GetValue('thumbnail')){
					$qry = DB::GetQryObj($this->model->imageTable)
						->SetConnName($this->connName)
						->AddWhere('article_seq='.$seq)
						->SetSort('seq');
					$this->_R_CommonQry($qry);
					$new = $qry->Get();
					$this->model->SetValue('thumbnail', $new['image']);
				}
				$qry =DB::UpdateQryObj($this->model->table)
					->SetConnName($this->connName)
					->SetDataStr('thumbnail', $this->model->GetValue('thumbnail'))
					->SetDataStr('content', $newContent)
					->AddWhere('seq = '.$seq);
				$this->_R_CommonQry($qry);
				$qry->Run();
			}
		}

		DeleteOldTempFiles(\Paths::DirOfUpload().'/temp/', strtotime('-6 hours'));
		return true;
	}

	/**
	 * 게시물 비밀번호 체크
	 * @return bool|string
	 */
	protected function _PasswordCheck(){
		if($this->model->GetValue('muid')){
			if(\BHG::$isMember !== true) return 'ERROR#101';
			else if(!$this->_CheckMyMUid($this->model, 'muid')) return 'ERROR#102';
		}
		else{
			if(!isset($_POST['pwd'])) return App::$lang['MSG_WRONG_CONNECTED'];

			$qry = DB::GetQryObj($this->model->table)->SetConnName($this->connName)->AddWhere('seq = %d', $this->model->GetValue('seq'))->SetKey('pwd');
			$this->_R_CommonQry($qry);
			$pwd = $qry->Get();
			if(!_password_verify(Post('pwd'), $pwd['pwd'])){
				return App::$lang['MSG_WRONG_PASSWORD'];
			}
		}
		return true;
	}

	/**
	 * JSON 게시물 액션 실행
	 */
	public function PostJSONAction(){
		if(\BHG::$isMember !== true) JSON(false, App::$lang['MSG_NEED_LOGIN']);

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
				JSON(false, App::$lang['MSG_WRONG_CONNECTED']);
			break;
		}
	}

	/**
	 * JSON 게시물 액션 취소
	 */
	public function PostJSONCancelAction(){
		if(\BHG::$isMember !== true) JSON(false, App::$lang['MSG_NEED_LOGIN']);

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
				JSON(false, App::$lang['MSG_WRONG_CONNECTED']);
			break;
		}
	}

	/**
	 * JSON 서브아이디 가져오기
	 */
	public function PostGetSubCategory(){
		$res = $this->boardManger->DBGet(Post('bid'), Post('subid'));
		if(!$res->result) JSON(false, $res->message ? $res->message : App::$lang['MSG_WRONG_CONNECTED']);
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
		App::$data['categoryHtml'] = '';

		if(!strlen($this->menuSubCategory) && strlen($this->menuCategory) && sizeof(App::$data['subCategory'])){
			App::$data['categoryHtml'] .= '<div class="categoryTab categoryTabC categoryTabC1"><ul>';
			App::$data['categoryHtml'] .= '<li class="all ' . (EmptyGet('scate') ? 'active' : '') . '"><a href="' . App::URLAction() . '">' . App::$lang['ALL'] . '</a></li>';
			foreach(App::$data['subCategory'] as $v){
				$active = $v == Get('scate') ? ' class="active"' : '';
				App::$data['categoryHtml'] .= '<li' . $active . '><a href="' . App::URLAction() . '?scate=' . urlencode($v) . '">' . GetDBText($v) . '</a></li>';
			}
			App::$data['categoryHtml'] .= '</ul></div>';
		}

		else if(!strlen($this->menuSubCategory)){
			if(!EmptyGet('cate') && sizeof(App::$data['subCategory'])){
				App::$data['categoryHtml'] .= '<div class="categoryTab categoryTabC categoryTabC1"><ul>';
				App::$data['categoryHtml'] .= '<li class="parent"><a href="' . App::URLAction() . '">' . App::$lang['PARENT_CATEGORY'] . '</a></li>';
				App::$data['categoryHtml'] .= '<li class="all ' . (EmptyGet('scate') ? 'active' : '') . '"><a href="' . App::URLAction() . '?cate=' . urlencode(Get('cate')) . '">' . App::$lang['ALL'] . '</a></li>';
				foreach(App::$data['subCategory'] as $v){
					$active = $v == Get('scate') ? ' class="active"' : '';
					App::$data['categoryHtml'] .= '<li' . $active . '><a href="' . App::URLAction() . '?cate=' . urlencode(Get('cate')) . '&scate=' . urlencode($v) . '">' . GetDBText($v) . '</a></li>';
				}
				App::$data['categoryHtml'] .= '</ul></div>';
			}
			else if(sizeof(App::$data['category'])){
				App::$data['categoryHtml'] .= '<div class="categoryTab categoryTabC categoryTabC2"><ul>';
				App::$data['categoryHtml'] .= '<li class="all ' . (EmptyGet('cate') ? 'active' : '') . '"><a href="' . App::URLAction() . '">' . App::$lang['ALL'] . '</a></li>';
				foreach(App::$data['category'] as $v){
					$active = $v == Get('cate') ? ' class="active"' : '';
					App::$data['categoryHtml'] .= '<li' . $active . '><a href="' . App::URLAction() . '?cate=' . urlencode($v) . '">' . GetDBText($v) . '</a></li>';
				}
				App::$data['categoryHtml'] .= '</ul></div>';
			}
		}


		return App::$data['categoryHtml'];
	}

	/**
	 * 체크 항목 삭제
	 */
	public function PostSysDel(){
		if(!$this->managerIs) JSON(false, App::$lang['MSG_WRONG_CONNECTED']);
		if(EmptyPost('seq')) JSON(false, App::$lang['SELECT_DELETE_POST']);
		$chk = explode(',', Post('seq'));

		DB::UpdateQryObj($this->model->table)
			->SetConnName($this->connName)
			->AddWhere('seq IN (%d)', $chk)
			->SetDataStr('delis','y')
			->Run();
		JSON(true);
	}

	/**
	 * 체크 항목 복구
	 */
	public function PostSysUnDel(){
		if(!$this->managerIs) JSON(false, App::$lang['MSG_WRONG_CONNECTED']);
		if(EmptyPost('seq')) JSON(false, App::$lang['SELECT_RESTORE_POST']);
		$chk = explode(',', Post('seq'));

		DB::UpdateQryObj($this->model->table)
			->SetConnName($this->connName)
			->AddWhere('seq IN (%d)', $chk)
			->SetDataStr('delis','n')
			->Run();
		JSON(true);
	}

	/**
	 * 체크 항목 이동
	 */
	public function PostSysMove(){
		if(!$this->managerIs) JSON(false, App::$lang['MSG_WRONG_CONNECTED']);
		if(EmptyPost('seq')) JSON(false, App::$lang['SELECT_MOVE_POST']);
		if(EmptyPost('bid') || EmptyPost('subid')) JSON(false, App::$lang['SELECT_MOVE_BOARD']);
		$chk = explode(',', Post('seq'));

		$moveParam = PostsCopyOrMoveParam::GetInst()->SetDestBId(Post('bid'), Post('subid'))->SetMoveType()->SetPosts($chk);
		$resChk = $this->_CheckArticleCopy($moveParam);
		if(sizeof($resChk)){
			$this->_CheckArticleRemove($resChk);
		}

		JSON(true);
	}

	/**
	 * 체크 항목 복사
	 */
	public function PostSysCopy(){
		if(!$this->managerIs) JSON(false, App::$lang['MSG_WRONG_CONNECTED']);
		if(EmptyPost('seq')) JSON(false, App::$lang['SELECT_COPY_POST']);
		if(EmptyPost('bid') || EmptyPost('subid')) JSON(false, App::$lang['SELECT_COPY_BOARD']);
		$chk = explode(',', Post('seq'));

		$moveParam = PostsCopyOrMoveParam::GetInst()->SetDestBId(Post('bid'), Post('subid'))->SetPosts($chk);
		$chk = $this->_CheckArticleCopy($moveParam);
		JSON(true);
	}

	/**
	 * @param PostsCopyOrMoveParam $param
	 * @param callable(\BoardModel) $copyInsBefore
	 * @param callable(PostsCopyOrMoveParam) $copyInsAfter
	 * @param callable(\ReplyModel) $replyCopyInsBefore
	 * @param callable(PostsCopyOrMoveParam) $replyCopyInsAfter
	 * @return int[]
	 */
	protected function _CheckArticleCopy($param, $copyInsBefore = null, $copyInsAfter = null, $replyCopyInsBefore = null, $replyCopyInsAfter = null){
		$returnArray = array();
		if(!is_array($param->posts)) $param->posts = array($param->posts);

		$newTable = TABLE_FIRST . 'bbs_' . $param->destBid;
		$action = $param->destBid.(strlen($param->destSubId) ? '-' . $param->destSubId  : '');
		$newUploadDir = '/board/'.$action.'/'.date('ym').'/';
		$newRepUploadDir = '/reply/'.$action.'/'.date('ym').'/';
		$newUploadImageDir = '/boardimage/'.$action.'/'.date('ym').'/';

		$boardModel = new \BoardModel();
		$boardModel->SetConnName($this->connName);
		$boardModel->bid = $param->destBid;
		$boardModel->table = TABLE_FIRST.'bbs_'.$boardModel->bid;
		$boardModel->_pwd->htmlType = \HTMLType::TEXT;

		$except = array('file1', 'file2', 'thumbnail', 'seq', 'reg_date', 'sort1', 'sort2', 'subid', 'category', 'sub_category');
		if($param->type == 'copy'){
			$except = array_merge($except, array('hit','reply_cnt', 'recommend', 'report', 'read', 'scrap', 'oppose'));
		}
		$repExcept = array('article_seq', 'file');
		$sort1Arr = array();
		$qry = DB::GetListQryObj($this->model->table)
			->SetConnName($this->connName)
			->AddWhere('seq IN (%d)', $param->posts)
			->SetSort('sort1 DESC, sort2 DESC');
		$this->_R_CommonQry($qry);
		while($row = $qry->Get()){
			// 게시물 복사
			$fcRes1 = $this->_FileCopy($newUploadDir, $row['file1']);
			$fcRes2 = $this->_FileCopy($newUploadDir, $row['file2']);
			$fcRes3 = $this->_FileCopy($newUploadDir, $row['thumbnail']);

			foreach($boardModel->data as $k => $d){
				if(isset($row[$k]) && !in_array($k, $except)){
					$boardModel->data[$k]->SetValue($row[$k]);
				}
			}

			$boardModel->SetValue('subid', $param->destSubId);
			$boardModel->SetValue('category', Post('category'));
			$boardModel->SetValue('sub_category', Post('sub_category'));

			if($fcRes1['path']) $boardModel->SetValue('file1', $fcRes1['path'].($fcRes1['name'] ? '*' . $fcRes1['name'] : ''));
			if($fcRes2['path']) $boardModel->SetValue('file2', $fcRes2['path'].($fcRes2['name'] ? '*' . $fcRes2['name'] : ''));
			if($fcRes3['path']) $boardModel->SetValue('thumbnail', $fcRes3['path'].($fcRes3['name'] ? '*' . $fcRes3['name'] : ''));
			if(isset($sort1Arr[$row['sort1']]) && strlen($sort1Arr[$row['sort1']])){
				$boardModel->SetValue('sort1', $sort1Arr[$row['sort1']]);
			}
			else{
				$boardModel->SetQueryValue('sort1', '(SELECT sort1 FROM (SELECT IF(COUNT(B.sort1) < 1, 0, MIN(B.sort1)) -1 as sort1 FROM ' . $boardModel->table . ' as B) as C)');
			}
			$boardModel->SetValue('sort2', $row['sort2']);
			$boardModel->SetValue('reg_date', date('Y-m-d H:i:s'));


			$this->_R_CheckArticleCopyInsBefore($boardModel); // Reserved
			if(is_callable($copyInsBefore)) $copyInsBefore($boardModel);

			$res = $boardModel->DBInsert();
			if(!$res->result) return $returnArray;
			$article_seq = $res->id;

			$newRow = DB::GetQryObj($newTable)
				->SetConnName($this->connName)
				->SetKey('sort1')
				->AddWhere('seq = %d', $article_seq)
				->Get();
			$sort1Arr[] = array($row['sort1'] => $newRow['sort1']);

			$returnArray[] = $row['seq'];


			// 이미지 복사
			$dbGetList = DB::GetListQryObj($this->model->imageTable)
				->SetConnName($this->connName)
				->AddWhere('article_seq='.$row['seq']);
			$this->_R_CommonQry($dbGetList);
			$imgCopyCnt = 0;
			while($img = $dbGetList->Get()){
				$fcRes = $this->_FileCopy($newUploadImageDir, $img['image']);

				DB::InsertQryObj($newTable . '_images')
					->SetConnName($this->connName)
					->SetDataStr('imagename', $img['imagename'])
					->SetDataNum('article_seq', $article_seq)
					->SetDataStr('image', $fcRes['path'])
					->SetDecrementKey('seq')
					->Run();
				if($row['thumbnail'] == $img['image']){
					DB::UpdateQryObj($newTable)
						->SetConnName($this->connName)
						->SetDataStr('thumbnail', $fcRes['path'])
						->AddWhere('seq = %d', $article_seq)
						->Run();
				}
				$imgCopyCnt++;
				$row['content'] = str_replace($img['image'], $fcRes['path'], $row['content']);
			}
			if($imgCopyCnt){
				DB::UpdateQryObj($newTable)
					->SetConnName($this->connName)
					->SetDataStr('content', $row['content'])
					->AddWhere('seq = %d', $article_seq)
					->Run();
			}

			$param->SetSourceBId($this->bid, $row['subid'])->SetSourceSeq($row['seq'])->SetDestSeq($article_seq);
			$this->_R_CheckArticleCopyInsAfter($param); // Reserved
			if(is_callable($copyInsAfter)) $copyInsAfter($param);

			if($param->type == 'move'){
				// 액션 복사
				DB::SQL($this->connName)->Query('INSERT INTO %1 (SELECT `action_type`, %d as `article_seq`, `muid`, `reg_date` FROM %1 WHERE article_seq = %d)', $newTable . '_action', $article_seq, $this->model->table . '_action', $row['seq']);

				// 리플 복사
				$dbGetList = DB::GetListQryObj($this->model->table.'_reply')
					->SetConnName($this->connName)
					->AddWhere('article_seq='.$row['seq']);
				$this->_R_CommonQry($dbGetList);
				while($rep = $dbGetList->Get()){


					$replyModel = new \ReplyModel($this->connName);
					$replyModel->bid = $param->destBid;
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
					if(is_callable($replyCopyInsBefore)) $replyCopyInsBefore($replyModel);
					$res = $replyModel->DBInsert();
					if($res->result){
						$param->SetSourceBId($this->bid, $row['subid'])->SetSourceSeq($rep['seq'])->SetDestSeq($res->id);
						$this->_R_CheckArticleReplyCopyInsAfter($param); // Reserved
						if(is_callable($replyCopyInsAfter)) $replyCopyInsAfter($param);
					}
				}
			}
		}

		return $returnArray;
	}

	protected function _FileCopy($dir, $file){
		$filePath = $this->model->GetFilePathByValue($file);
		$fileName = $this->model->GetFileNameByValue($file);
		if($filePath && file_exists(\Paths::DirOfUpload().$filePath)){

			if(!is_dir(\Paths::DirOfUpload() . $dir)) mkdir(\Paths::DirOfUpload() . $dir, 0777, true);

			$temp = explode('.', $filePath);
			$ext = end($temp);
			$newFilePath = $dir . \_ModelFunc::RandomFileName() . '.' . $ext;
			if(file_exists(\Paths::DirOfUpload().$filePath)) copy(\Paths::DirOfUpload().$filePath, \Paths::DirOfUpload().$newFilePath);
			return array('name' => $fileName, 'path' => $newFilePath);
		}
		return array('name' => '', 'path' => '');
	}

	/**
	 * @param array $arr
	 * @param callable(int,string,string) $removeAfter param : $seq, $bid, $subid
	 */
	protected function _CheckArticleRemove($arr, $removeAfter = null){
		if(!is_array($arr)) $arr = array($arr);

		foreach($arr as $seq){
			$qry = DB::GetQryObj($this->model->table)
				->SetConnName($this->connName)
				->AddWhere('seq = %d', $seq);
			$this->_R_CommonQry($qry);
			$row = $qry->Get();

			// 이미지 삭제
			$dbGetList = DB::GetListQryObj($this->model->imageTable)
				->SetConnName($this->connName)
				->AddWhere('article_seq='.$seq);
			$this->_R_CommonQry($dbGetList);
			while($img = $dbGetList->Get()){
				if(file_exists(\Paths::DirOfUpload().$img['image'])) @UnlinkImage(\Paths::DirOfUpload().$img['image']);
				$qry = DB::DeleteQryObj($this->model->table.'_images')
					->SetConnName($this->connName)
					->AddWhere('article_seq = '.$img['article_seq'])
					->AddWhere('seq = '.$img['seq']);
				$this->_R_CommonQry($qry);
				$qry->Run();
			}

			// 댓글 삭제
			$dbGetList = DB::GetListQryObj($this->model->table.'_reply')
				->SetConnName($this->connName)
				->AddWhere('article_seq='.$seq);
			$this->_R_CommonQry($dbGetList);
			while($rep = $dbGetList->Get()){
				$f = $this->model->GetFilePathByValue($rep['file']);
				if(file_exists(\Paths::DirOfUpload().$f)) @UnlinkImage(\Paths::DirOfUpload().$f);
				DB::DeleteQryObj($this->model->table.'_reply')
					->SetConnName($this->connName)
					->AddWhere('article_seq = '.$rep['article_seq'])
					->AddWhere('seq = '.$rep['seq'])
					->Run();
			}


			// 액션 삭제
			DB::DeleteQryObj($this->model->table . '_action')
				->SetConnName($this->connName)
				->AddWhere('article_seq = '.$row['seq'])
				->Run();

			// 게시물 삭제
			$f = $this->model->GetFilePathByValue($row['file1']);
			if($f && file_exists(\Paths::DirOfUpload().$f)){
				@UnlinkImage(\Paths::DirOfUpload().$f);
			}
			$f = $this->model->GetFilePathByValue($row['file2']);
			if($f && file_exists(\Paths::DirOfUpload().$f)){
				@UnlinkImage(\Paths::DirOfUpload().$f);
			}
			$qry = DB::DeleteQryObj($this->model->table)
				->SetConnName($this->connName)
				->AddWhere('seq = '.$row['seq']);
			$this->_R_CommonQry($qry);
			$qry->Run();

			$this->_R_CheckArticleRemoveAfter($seq, $this->bid, $row['subid']);
			if(is_callable($removeAfter)) $removeAfter($seq, $this->bid, $row['subid']);
		}
	}

	/**
	 * 게시물 액션 가져오기
	 *
	 * @return ArticleAction
	 */
	protected function GetArticleAction(){
		if(!strlen(App::$id)) JSON(false,  App::$lang['MSG_WRONG_CONNECTED']);
		if(!\BHG::$session->boardView->bid->Get() || !\BHG::$session->boardView->seq->Get() || \BHG::$session->boardView->bid->Get() != $this->bid || \BHG::$session->boardView->seq->Get() != App::$id) JSON(false, App::$lang['POSSIBLE_LAST_POST']);

		$seq = to10(App::$id);
		return ArticleAction::GetInstance($this->bid)
			->SetConnName($this->connName)
			->SetArticleSeq($seq)
			->SetMUid(\BHG::$session->member->muid->Get())
			->SetParentTable($this->bid);
	}

	protected function _GetBoardData($id){
		$args = func_get_args();
		$res = $this->model->DBGet($args);
		if(!$res->result) URLRedirect(-1, App::$lang['MSG_WRONG_CONNECTED']);
		if(\BHG::$isMember === true && strlen($this->model->_muid->value)){
			$blockUser = CM::GetBlockUsers();
			if(in_array($this->model->_muid->value, $blockUser)) URLRedirect(-1, App::$lang['BLOCKED_USER_POST']);
		}
	}

	public function _DirectView(){
		$this->View();
	}

	public function _GetAdditionalSubidList(){
		$temp = array();
		if(sizeof($this->additionalSubId)){
			$qry = \BoardManagerModel::GetBoardListQry()
				->SetConnName($this->connName)
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
}

class PostsCopyOrMoveParam
{
	public $type = 'copy';
	public $posts = array();

	public $sourceBId = '';
	public $sourceSubId = '';
	public $sourceSeq = '';

	public $destBid = '';
	public $destSubId = '';
	public $destSeq = '';

	public static function GetInst(){
		$static = new static();
		return $static;
	}

	public function SetMoveType(){
		$this->type = 'move';
		return $this;
	}

	public function SetCopyType(){
		$this->type = 'copy';
		return $this;
	}

	public function SetPosts($arr){
		$this->posts = $arr;
		return $this;
	}

	public function SetSourceBId($str, $subid = ''){
		$this->sourceBId = $str;
		if(strlen($subid)) $this->sourceSubId = $subid;
		return $this;
	}

	public function SetSourceSeq($int){
		$this->sourceSeq = $int;
		return $this;
	}

	public function SetDestBId($str, $subid = ''){
		$this->destBid = $str;
		if(strlen($subid)) $this->destSubId = $subid;
		return $this;
	}

	public function SetDestSeq($int){
		$this->destSeq = $int;
		return $this;
	}
}
