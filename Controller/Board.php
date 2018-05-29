<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

define('_DEFAULT_BOARD_LOGIN_URL', _URL . '/Login');

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
	public $uploadDir = '';
	public $uploadImageDir = '';
	public static $loginUrl = _DEFAULT_BOARD_LOGIN_URL;

	/** @param \BH_DB_GetListWithPage $qry */
	protected function _GetListQuery(&$qry){}
	/** @param \BH_DB_GetList $qry */
	protected function _MoreListQuery(&$qry){}
	/** @param array $data */
	protected function _ViewEnd($data){}
	protected function _WriteEnd(){}
	protected function _AnswerEnd(){}
	protected function _ModifyEnd(){}
	protected function _PostModifyUpdateBefore(){}
	protected function _PostModifyUpdateAfter(){}
	protected function _PostWriteInsertBefore(){}
	protected function _PostWriteInsertAfter($insertId){}
	protected function _CommonQry(&$qry, $opt = null){}

	public function __construct(){
		if(App::$SettingData['GetUrl'][1] == _ADMINURLNAME){
			if(CM::GetAdminIs()) $this->AdminPathIs = true;
			else{
				if(_JSONIS === true) JSON(false, _MSG_WRONG_CONNECTED);
				URLReplace(App::URLBase('Login'), _MSG_WRONG_CONNECTED);
			}
		}
		$this->model = App::InitModel('Board');
		$this->boardManger = App::InitModel('BoardManager');
	}

	public function __init(){
		$this->bid = App::$TID;
		$this->boardManger->DBGet($this->bid);
		$this->_BoardSetting();
		$this->uploadDir = '/board/'.$this->bid.'/'.date('ym').'/';
		$this->uploadImageDir = '/boardimage/'.$this->bid.'/'.date('ym').'/';
	}

	protected function _BoardSetting(){
		if(!isset($this->bid) || $this->bid == '') URLReplace('-1', '잘못된 접근입니다.');

		App::SetFollowQuery(array('page','searchType','searchKeyword','category','lastSeq'));

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
		$this->Path = '/Board/'.App::$NativeDir.'/'.$this->boardManger->GetValue('skin').'/';
		if(file_exists(_SKINDIR.$this->Path.$action.'.html')) App::$Html = $this->Path.$action.'.html';
		else{
			$this->Path = '/Board/'.$this->boardManger->GetValue('skin').'/';
			if(file_exists(_SKINDIR.$this->Path.$action.'.html')) App::$Html = $this->Path.$action.'.html';
			else{
				$this->Path = '/Board/';
				App::$Html = '/Board/' . $action.'.html';
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
			if(substr($layout, -5) != '.html') $layout .= '.html';
			$layoutPath = App::$NativeDir.'/'.$layout;
			if(file_exists(_SKINDIR.'/Layout/'.$layoutPath)) $layout = $layoutPath;
			App::$Layout = $layout;
		}

		App::$Data['categorys'] = array();
		if(!is_null($this->boardManger->GetValue('category')) && strlen($this->boardManger->GetValue('category'))){
			App::$Data['categorys'] = explode(',', $this->boardManger->GetValue('category'));
		}

		if(!$this->AdminPathIs && _MEMBERIS !== true && $this->boardManger->GetValue('man_to_man') === 'y') URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
		if($this->boardManger->GetValue('man_to_man') === 'y') $this->boardManger->SetValue('use_secret', 'n');
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

	public function GetList($viewPageIs = false){
		$res = $this->GetAuth('List');
		if(!$res){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}

		// 공지를 불러온다.
		App::$Data['notice'] = array();
		$_GET['searchKeyword'] = isset($_GET['searchKeyword']) ? trim($_GET['searchKeyword']) : '';
		if((!isset($_GET['page']) || $_GET['page'] < 2) && !strlen($_GET['searchKeyword'])){
			$qry = $this->model->NewQryName('notice')->GetSetListQry('A');
			$qry->AddWhere('A.delis=\'n\'')
				->AddWhere('A.notice=\'y\'')
				->SetSort('A.seq DESC');
			$this->_CommonQry($qry);
			$qry->DrawRows();
			$this->_RowSet($qry->data);
			App::$Data['notice'] = &$qry->data;
		}

		// 리스트를 불러온다.
		$dbList = $this->model->NewQryName('default')->GetSetPageListQry('A');
		$dbList->SetSort('A.sort1, A.sort2')
			->SetPage(isset($_GET['page']) ? $_GET['page'] : 1)
			->SetPageUrl(App::URLAction('').App::GetFollowQuery('page'))
			->SetArticleCount($this->boardManger->GetValue('article_count'));
		$this->_CommonQry($dbList);

		if(!$this->AdminPathIs){
			$dbList->AddWhere('A.delis=\'n\'');
			if(!$this->managerIs && $this->boardManger->GetValue('man_to_man') === 'y') $dbList->AddWhere('A.muid = %d OR A.target_muid = %d', $_SESSION['member']['muid'], $_SESSION['member']['muid']);
		}

		if(isset($_GET['category']) && strlen($_GET['category'])) $dbList->AddWhere('category = %s', $_GET['category']);

		if(isset($_GET['searchType']) && strlen($_GET['searchType']) && isset($_GET['searchKeyword']) && strlen($_GET['searchKeyword'])){
			switch($_GET['searchType']){
				case 's':
					$dbList->AddWhere('A.subject LIKE %s', '%'.$_GET['searchKeyword'].'%');
				break;
				case 'c':
					$dbList->AddWhere('A.content LIKE %s', '%'.$_GET['searchKeyword'].'%');
				break;
				case 'snc':
					$dbList->AddWhere('A.subject LIKE %s OR A.content LIKE %s', '%'.$_GET['searchKeyword'].'%', '%'.$_GET['searchKeyword'].'%');
				break;
			}
		}

		$this->_GetListQuery($dbList); // Reserved
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

		// 공지를 불러온다.
		App::$Data['notice'] = array();
		$_GET['searchKeyword'] = isset($_GET['searchKeyword']) ? trim($_GET['searchKeyword']) : '';
		if((!isset($_GET['seq']) || !strlen($_GET['seq'])) && (!isset($_GET['lastSeq']) || !strlen($_GET['lastSeq'])) && !strlen($_GET['searchKeyword'])){
			$qry = $this->model->NewQryName('notice')->GetSetListQry('A');
			$qry->AddWhere('A.delis=\'n\'')
				->AddWhere('A.notice=\'y\'')
				->SetSort('A.seq DESC');
			$this->_CommonQry($qry);
			App::$Data['notice'] = &$qry->GetRows();
			$this->_RowSet(App::$Data['notice']);
		}

		// 리스트를 불러온다.
		$dbList = $this->model->NewQryName('default')->GetSetListQry('A');
		$dbList->SetLimit($this->boardManger->GetValue('article_count'))
			->SetSort('A.sort1, A.sort2');
		$this->_CommonQry($dbList);

		if(!$this->AdminPathIs){
			$dbList->AddWhere('A.delis=\'n\'');
			if(!$this->managerIs && $this->boardManger->GetValue('man_to_man') === 'y') $dbList->AddWhere('A.muid = %d OR A.target_muid = %d', $_SESSION['member']['muid'], $_SESSION['member']['muid']);
		}

		if(isset($_GET['seq']) && strlen($_GET['seq'])){
			$seq = to10($_GET['seq']);
			$dbList->AddWhere('A.seq = %d', $seq);
		}
		else{
			if(isset($_GET['lastSeq']) && strlen($_GET['lastSeq'])){
				$qry = DB::GetQryObj($this->model->table.' A')
					->AddWhere('A.seq = %d', $_GET['lastSeq'])
					->SetKey('A.sort1, A.sort2');
				$this->_CommonQry($qry);
				$last = $qry->Get();

				if($last) $dbList->AddWhere('A.sort1 > %d OR (A.sort1 = %d AND A.sort2 > %d)', $last['sort1'], $last['sort1'], $last['sort2']);
				else $dbList->AddWhere('A.seq > %d', $_GET['lastSeq']);
			}

			if(isset($_GET['category']) && strlen($_GET['category'])) $dbList->AddWhere('A.category = %s', $_GET['category']);

			if(isset($_GET['searchType']) && strlen($_GET['searchType']) && isset($_GET['searchKeyword']) && strlen($_GET['searchKeyword'])){
				switch($_GET['searchType']){
					case 's':
						$dbList->AddWhere('A.subject LIKE %s', '%'.$_GET['searchKeyword'].'%');
					break;
					case 'c':
						$dbList->AddWhere('A.content LIKE %s', '%'.$_GET['searchKeyword'].'%');
					break;
					case 'snc':
						$dbList->AddWhere('A.subject LIKE %s OR A.content LIKE %s', '%'.$_GET['searchKeyword'].'%', '%'.$_GET['searchKeyword'].'%');
					break;
				}
			}
		}

		$this->_MoreListQuery($dbList);  // Reserved
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
		foreach($data as &$row){
			if($this->managerIs || $row['secret'] == 'n' || ($row['first_member_is'] == 'y' && strlen($row['muid']))) $row['possibleView'] = true;
			else $row['possibleView'] = false;
			$row['viewUrl'] = App::URLAction('View/').toBase($row['seq']).App::GetFollowQuery();
			$row['replyCount'] = $row['reply_cnt'] ? '<span class="ReplyCount">['.$row['reply_cnt'].']</span>' : '';
			$row['newArticleIs'] = (time() - strtotime($row['reg_date']) < $this->boardManger->data['new_view_day']->Value * 60 * 60 * 24);
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
				$this->_CommonQry($qry);
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

				if(_password_verify($_POST['pwd'], $this->model->GetValue('pwd')) || (isset($firstDoc) && _password_verify($_POST['pwd'], $firstDoc['pwd']))){
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
			$this->_CommonQry($dbUpdate);
			$dbUpdate->Run();

			setcookie($cookieName, 'y');
		}
		$this->_ViewEnd($data);  // Reserved

		if(_JSONIS === true) JSON(true, '', App::GetView($this->model, $data));
		else App::View($this->model, $data);
	}

	public function Write(){
		$res = $this->GetAuth('Write');
		if(!$res){
			if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
			URLReplace('-1', _MSG_NO_AUTH);
		}

		$this->_WriteEnd();  // Reserved
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
		$this->_CommonQry($qry);
		$data = $qry->Get();
		if(!$this->AdminPathIs){
			if($data['delis'] == 'y') URLReplace('-1', _MSG_WRONG_CONNECTED);
			if($this->boardManger->GetValue('man_to_man') === 'y' && !$this->managerIs && $data['muid'] != $_SESSION['member']['muid'] && $data['target_muid'] != $_SESSION['member']['muid']) URLReplace('-1', _MSG_WRONG_CONNECTED);
		}

		$this->model->SetValue('subject', strpos('[답변]', $data['subject']) === false ? '[답변] '.$data['subject'] : $data['subject']);
		$this->model->SetValue('secret', $data['secret']);

		$this->_AnswerEnd();  // Reserved

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

		$this->_ModifyEnd();  // Reserved

		if(_JSONIS === true) JSON(true, '', App::GetView($this->model));
		else App::View($this->model);
	}

	public function PostModify(){
		if(isset($_POST['mode']) && $_POST['mode'] == 'view'){
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
		$this->model->SetValue('htmlis', isset($_POST['htmlis']) && $_POST['htmlis'] == 'y' ? 'y' : 'n');

		$this->_PostModifyUpdateBefore();  // Reserved

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)){
			if(_AJAXIS === true) JSON(false, $error[0]);
			App::$Data['error'] = $error[0];
			App::View($this->model);
			return;
		}

		$res2 = $this->model->DBUpdate();
		$this->_ContentImageUpate($_POST['content'], $seq, 'modify');


		if($res2->result){
			$this->_PostModifyUpdateAfter();  // Reserved
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
			if(!$res){
				if(_MEMBERIS !== true) URLReplace(self::$loginUrl, _MSG_NEED_LOGIN, 'NEED LOGIN');
				URLReplace('-1', _MSG_NO_AUTH);
			}

			$qry = DB::GetQryObj($this->model->table)
				->AddWhere('seq=%d', to10($_POST['target']))
				->SetKey('mname, depth, muid, target_muid, sort1, sort2', 'seq', 'first_seq', 'first_member_is', 'category', 'delis');
			$this->_CommonQry($qry);
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
		$this->model->SetValue('htmlis', isset($_POST['htmlis']) && $_POST['htmlis'] == 'y' ? 'y' : 'n');

		// 회원유무
		if(_MEMBERIS === true){
			$this->model->SetValue('muid', $_SESSION['member']['muid']);
			$this->model->SetValue('mlevel', $member['level']);
			$this->model->SetValue('mname', $member['nickname'] ? $member['nickname'] : ($member['mname'] ? $member['mname'] : $member['mid']));
		}

		// 답글쓰기라면 sort 정렬
		if(App::$Action == 'Answer'){
			$qry = DB::UpdateQryObj($this->model->table)
				->SetData('sort2', 'sort2 + 1')
				->AddWhere('sort1 = %d', App::$Data['targetData']['sort1'])
				->AddWhere('sort2 > %d', App::$Data['targetData']['sort2'])
				->SetSort('sort2 DESC');
			$this->_CommonQry($qry);
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
			$this->model->SetValue('target_muid', App::$Data['targetData']['muid'] ? App::$Data['targetData']['muid'] : 0);
			$this->model->SetValue('sort1', App::$Data['targetData']['sort1']);
			$this->model->SetValue('sort2', App::$Data['targetData']['sort2'] + 1);
			$this->model->SetValue('depth', App::$Data['targetData']['depth'] + 1);
		}else{
			$this->model->SetValue('first_member_is', _MEMBERIS === true ? 'y' : 'n');
			$this->model->SetQueryValue('sort1', '(SELECT IF(COUNT(s.sort1) = 0, 0, MIN(s.sort1))-1 FROM '.$this->model->table.' as s)');
		}

		$this->_PostWriteInsertBefore();  // Reserved

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)){
			if(_JSONIS === true) JSON(false, $error[0]);
			App::$Data['error'] = $error[0];
			$this->Write();
			return;
		}

		$res = $this->model->DBInsert();
		$result->result = $res->result;
		$result->message = $res->message;

		if($result->result){
			$this->_ContentImageUpate($_POST['content'], $res->id);
			$this->_PostWriteInsertAfter($res->id);  // Reserved
			if(_AJAXIS === true) JSON(true, '', '등록되었습니다.');
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
		else URLReplace(App::URLAction($this->AdminPathIs ? 'View/'.App::$ID : '').App::GetFollowQuery(), '삭제되었습니다.');
	}

	public function Undelete(){
		if(!$this->AdminPathIs) URLReplace('-1', _MSG_WRONG_CONNECTED);

		$seq = to10(App::$ID);

		$this->_GetBoardData($seq);
		$this->model->SetValue('delis', 'n');
		$this->model->DBUpdate();

		if(_AJAXIS === true) JSON(true, '', '복구되었습니다.');
		else URLReplace(App::URLAction($this->AdminPathIs ? 'View/'.App::$ID : '').App::GetFollowQuery(), '복구되었습니다.');
	}

	public function Download(){
		$seq = to10(App::$ID);
		$this->_GetBoardData($seq);
		$file = explode('*', $this->model->GetValue('file1'));
		if(sizeof($file) < 2){
			$name = explode('/', $file[0]);
			$file[1] = end($name);
		}
		Download(_UPLOAD_DIR . $file[0], $file[1]);
	}

	public function PostRemove(){
		$seq = to10(App::$ID);
		if(!$this->AdminPathIs) URLReplace(-1, _MSG_WRONG_CONNECTED);
		$qry = DB::GetQryObj($this->model->table)
			->AddWhere('seq = %d', $seq);
		$this->_CommonQry($qry);
		$row = $qry->Get();

		$dbGetList = DB::GetListQryObj($this->model->imageTable)
			->AddWhere('article_seq='.$seq);
		$this->_CommonQry($dbGetList);
		while($img = $dbGetList->Get()){
			if(file_exists(_UPLOAD_DIR.$img['image'])) @unlink(_UPLOAD_DIR.$img['image']);
			$qry = DB::DeleteQryObj($this->model->table.'_images')
				->AddWhere('article_seq = '.$img['article_seq'])
				->AddWhere('seq = '.$img['seq']);
			$this->_CommonQry($qry);
			$qry->Run();
		}

		$dbGetList = DB::GetListQryObj($this->model->table.'_reply')
			->AddWhere('article_seq='.$seq);
		$this->_CommonQry($dbGetList);
		while($rep = $dbGetList->Get()){
			if(file_exists(_UPLOAD_DIR.$rep['file'])) @unlink(_UPLOAD_DIR.$rep['file']);
			DB::DeleteQryObj($this->model->table.'_reply')
				->AddWhere('article_seq = '.$rep['article_seq'])
				->AddWhere('seq = '.$rep['seq'])
				->Run();
		}

		if($this->model->GetValue($row['file1']) && file_exists(_UPLOAD_DIR.$this->model->GetFilePathByValue($row['file1']))) @unlink(_UPLOAD_DIR.$this->model->GetFilePathByValue($row['file1']));
		if($this->model->GetValue($row['file2']) && file_exists(_UPLOAD_DIR.$this->model->GetFilePathByValue($row['file2']))) @unlink(_UPLOAD_DIR.$this->model->GetFilePathByValue($row['file2']));
		$qry = DB::DeleteQryObj($this->model->table)
			->AddWhere('seq = '.$row['seq']);
		$this->_CommonQry($qry);
		$qry->Run();
		URLReplace(App::URLAction().App::GetFollowQuery());
	}

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

	/**
	 * 이미지 등록
	 */
	protected function _ContentImageUpate($content, $seq, $mode = 'write'){
		$newcontent = $content;
		$maxImage = _MAX_IMAGE_COUNT;

		if($mode == 'modify'){
			$dbGetList = DB::GetListQryObj($this->model->imageTable)
				->AddWhere('article_seq='.$seq);
			$this->_CommonQry($dbGetList);
			while($img = $dbGetList->Get()){
				if(strpos($content,$img['image']) === false){
					// 파일이 없으면 삭제
					@unlink(_UPLOAD_DIR.$img['image']);

					if($img['image'] == $this->model->GetValue('thumnail')) $this->model->SetValue('thumnail', '');

					$qry = DB::DeleteQryObj($this->model->table.'_images')
						->AddWhere('article_seq = '.$img['article_seq'])
						->AddWhere('seq = '.$img['seq']);
					$this->_CommonQry($qry);
					$qry->Run();
				}
			}
		}

		$qry = DB::GetQryObj($this->model->imageTable)
			->AddWhere('article_seq='.$seq)
			->SetKey('COUNT(*) as cnt');
		$this->_CommonQry($qry);
		$cnt = $qry->Get();
		$imageCount = $cnt['cnt'];

		if(isset($_POST['addimg']) && is_array($_POST['addimg'])){
			foreach($_POST['addimg'] as $img){
				$exp = explode('|', $img);

				if(strpos($content, $exp[0]) !== false){
					if($imageCount >= $maxImage){
						@unlink(_UPLOAD_DIR.$exp[0]);
						continue;
					}

					$newpath = str_replace('/temp/', $this->uploadImageDir, $exp[0]);
					$uploadDir = _UPLOAD_DIR.$this->uploadImageDir;
					if(!is_dir($uploadDir)){
						mkdir($uploadDir, 0777, true);
					}
					@copy(_UPLOAD_DIR.$exp[0],_UPLOAD_DIR.$newpath);
					$newcontent = str_replace($exp[0],$newpath, $newcontent);
					// 파일이 있으면 등록

					unset($dbInsert);
					// 여기 수정
					$dbInsert = DB::InsertQryObj($this->model->imageTable)
						->SetDataNum('article_seq', $seq)
						->SetDataStr('image', $newpath)
						->SetDataStr('imagename', $exp[1])
						->SetDecrementKey('seq')
						->AddWhere('article_seq = %d', $seq);
					$this->_CommonQry($dbInsert, 'ImageInsert');
					$dbInsert->Run();
					$imageCount++;
				}
				@unlink(_UPLOAD_DIR.$exp[0]);
			}

			if($newcontent != $content){
				if(!$this->model->GetValue('thumnail')){
					$qry = DB::GetQryObj($this->model->imageTable)
						->AddWhere('article_seq='.$seq)
						->SetSort('seq');
					$this->_CommonQry($qry);
					$new = $qry->Get();
					$this->model->SetValue('thumnail', $new['image']);
				}
				$qry =DB::UpdateQryObj($this->model->table)
					->SetDataStr('thumnail', $this->model->GetValue('thumnail'))
					->SetDataStr('content', $newcontent)
					->AddWhere('seq = '.$seq);
				$this->_CommonQry($qry);
				$qry->Run();
			}
		}

		DeleteOldTempFiles(_UPLOAD_DIR.'/temp/', strtotime('-6 hours'));
		return true;
	}

	protected function _PasswordCheck(){
		if($this->model->GetValue('muid')){
			if(_MEMBERIS !== true) return 'ERROR#101';
			else if($this->model->GetValue('muid') != $_SESSION['member']['muid']) return 'ERROR#102';
		}
		else{
			if(!isset($_POST['pwd'])) return _MSG_WRONG_CONNECTED;

			$qry = DB::GetQryObj($this->model->table)->AddWhere('seq = %d', $this->model->GetValue('seq'))->SetKey('pwd');
			$this->_CommonQry($qry);
			$pwd = $qry->Get();
			if(!_password_verify($_POST['pwd'], $pwd['pwd'])){
				return _MSG_WRONG_PASSWORD;
			}
		}
		return true;
	}

	protected function _GetBoardData($id){
		$args = func_get_args();
		$this->model->DBGet($args);
	}

	public function _DirectView(){
		$this->View();
	}
}
