<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class Reply{
	/* @var ReplyModel */
	public $model;
	/* @var BoardModel */
	public $boardModel;
	/* @var BoardManagerModel */
	public $boardManger;
	public $managerIs = false;
	public $bid = '';
	protected $MoreListIs = false;
	protected $Path = '';
	public $uploadUrl = '';

	protected function _PostModifyUpdateBefore(){}
	protected function _PostModifyUpdateAfter(){}
	protected function _PostWriteInsertBefore(){}
	protected function _PostWriteInsertAfter($insertId){}
	/** @param \BH_DB_GetListWithPage $qry */
	protected function _GetListQuery(&$qry){}
	/** @param \BH_DB_GetList $qry */
	protected function _MoreListQuery(&$qry){}
	protected function _CommonQry(&$qry, $opt = null){}

	public function __construct(){
		$this->model = App::InitModel('Reply');
		$this->boardModel = App::InitModel('Board');
		$this->boardManger = App::InitModel('BoardManager');
		$this->uploadUrl = '/reply/' .$this->bid.'/' . date('ym') . '/';
	}

	public function __init(){
		if(_POSTIS !== true) exit;
		$this->bid = App::$TID;
		App::$Data['article_seq'] = SetDBInt((string)$_POST['article_seq']);
		$this->boardManger->DBGet($this->bid);
		$this->_ReplySetting();
	}

	protected function _ReplySetting(){
		App::$Layout = null;
		if(!isset($this->bid) || $this->bid == '') exit;

		$mid = CM::GetMember('mid');
		$manager = explode(',', $this->boardManger->GetValue('manager'));
		if ($mid !== false && strlen($mid) && in_array($mid, $manager)) {
			$this->managerIs = true;
		}

		$action = App::$Action;
		if($action == 'Answer' || $action == 'Modify') $action = 'Write';
		if($action == '_DirectView') $action = 'View';
		$this->Path = '/Reply/'.App::$NativeDir.'/'.$this->boardManger->GetValue('skin').'/';
		if(file_exists(_SKINDIR.$this->Path.$action.'.html')) App::$Html = $this->Path.$action.'.html';
		else{
			$this->Path = '/Reply/'.$this->boardManger->GetValue('skin').'/';
			if(file_exists(_SKINDIR.$this->Path.$action.'.html')) App::$Html = $this->Path.$action.'.html';
			else{
				$this->Path = '/Reply/';
				App::$Html = '/Reply/' . $action.'.html';
			}
		}

		if(file_exists(_SKINDIR.$this->Path.'MoreList.html')) $this->MoreListIs = true;
	}

	public function PostIndex(){
		if(!$this->MoreListIs) $this->PostGetList();
		else JSON(true, '', App::GetView($this->model));
	}

	public function PostGetList(){
		if(!isset($_POST['article_seq']) || !strlen($_POST['article_seq'])) exit;

		if(isset($this->boardManger) && $this->boardManger->GetValue('use_reply') == 'n') return;

		$this->_GetBoardData(App::$Data['article_seq']);
		$myArticleIs = $this->_MyArticleCheck();

		// 리스트를 불러온다.
		$dbList = DB::GetListPageQryObj($this->model->table)
			->SetSort('sort1, sort2')
			->SetPageUrl('#')
			->SetPage(isset($_POST['page']) ? $_POST['page'] : 1)
			->AddWhere('article_seq='.App::$Data['article_seq'])
			->SetArticleCount(isset($this->boardManger) ? $this->boardManger->GetValue('reply_count') : 20);
		$this->_CommonQry($dbList);
		$this->_GetListQuery($dbList);
		$dbList->DrawRows();
		foreach($dbList->data as &$row){
			// 비밀번호없이 수정권한
			$row['modifyAuthDirect'] = false;
			if($this->GetAuth() && _MEMBERIS === true && ($row['muid'] == $_SESSION['member']['muid'] || CM::GetAdminIs() )) $row['modifyAuthDirect'] = true;

			// 수정 버튼 보기
			if(
				(_MEMBERIS === true && ($row['muid'] == $_SESSION['member']['muid'] || CM::GetAdminIs())) ||
				(!strlen($row['muid']))
			) $row['modifyAuth'] = true;
			else $row['modifyAuth'] = false;

			// 삭제 버튼 보기
			if( $row['modifyAuth'] || $this->managerIs ) $row['deleteAuth'] = true;
			else $row['deleteAuth'] = false;

			// 비밀글일 경우 본문작성자, 댓글작성자, 매니저, 관리자 보기권한 부여
			$row['secretIs'] = false;
			if($row['delis'] == 'y'){
				if(CM::GetAdminIs()) $row['comment'] = '[삭제됨]'.$row['comment'];
				else $row['comment'] = _MSG_DELETED_REPLY;
			}
			else if($row['secret'] == 'y'){
				if(!$myArticleIs){
					if(!(_MEMBERIS === true && ($row['muid'] == $_SESSION['member']['muid'] || CM::GetAdminIs() || $this->managerIs))){
						$row['comment'] = _MSG_SECRET_ARTICLE;
						$row['secretIs'] = true;
					}
				}
			}

			$row['kdate'] = krDate($row['reg_date'],'mdhi');
		}

		JSON(true, '', App::GetView(null, $dbList));
	}

	public function PostMoreList(){
		if(!isset($_POST['article_seq']) || !strlen($_POST['article_seq'])) JSON(false, _MSG_WRONG_CONNECTED);

		if(isset($this->boardManger) && $this->boardManger->GetValue('use_reply') == 'n') return;

		$this->_GetBoardData(App::$Data['article_seq']);
		$myArticleIs = $this->_MyArticleCheck();

		// 리스트를 불러온다.
		$dbList = DB::GetListQryObj($this->model->table)
			->SetSort('sort1, sort2')
			->AddWhere('article_seq= %d', App::$Data['article_seq'])
			->SetLimit(isset($this->boardManger) ? $this->boardManger->GetValue('reply_count') : 20);
		$this->_CommonQry($dbList);
		$this->_GetListQuery($dbList);

		if(isset($_POST['seq']) && strlen($_POST['seq'])){
			$seq = to10($_POST['seq']);
			$dbList->AddWhere('seq = %d', $seq);
		}
		else{
			if(isset($_POST['lastSeq']) && strlen($_POST['lastSeq'])){
				$qry = DB::GetQryObj($this->model->table)
					->AddWhere('article_seq = %d', App::$Data['article_seq'])
					->AddWhere('seq = %d', $_POST['lastSeq'])
					->SetKey('sort1, sort2');
				$this->_CommonQry($qry);
				$last = $qry->Get();

				if($last) $dbList->AddWhere('sort1 > %d OR (sort1 = %d AND sort2 > %d)', $last['sort1'], $last['sort1'], $last['sort2']);
				else $dbList->AddWhere('seq > %d', $_POST['lastSeq']);
			}
		}

		$dbList->DrawRows();
		foreach($dbList->data as &$row){
			// 비밀번호없이 수정권한
			$row['modifyAuthDirect'] = false;
			if($this->GetAuth() && _MEMBERIS === true && ($row['muid'] == $_SESSION['member']['muid'] || CM::GetAdminIs() )) $row['modifyAuthDirect'] = true;

			// 수정 버튼 보기
			if(
				(_MEMBERIS === true && ($row['muid'] == $_SESSION['member']['muid'] || CM::GetAdminIs())) ||
				(!strlen($row['muid']))
			) $row['modifyAuth'] = true;
			else $row['modifyAuth'] = false;

			// 삭제 버튼 보기
			if( $row['modifyAuth'] || $this->managerIs ) $row['deleteAuth'] = true;
			else $row['deleteAuth'] = false;

			// 비밀글일 경우 본문작성자, 댓글작성자, 매니저, 관리자 보기권한 부여
			$row['secretIs'] = false;
			if($row['delis'] == 'y') $row['comment'] = _MSG_DELETED_REPLY;
			else if($row['secret'] == 'y'){
				if(!$myArticleIs){
					if(!(_MEMBERIS === true && ($row['muid'] == $_SESSION['member']['muid'] || CM::GetAdminIs() || $this->managerIs))){
						$row['comment'] = _MSG_SECRET_ARTICLE;
						$row['secretIs'] = true;
					}
				}
			}

			$row['kdate'] = krDate($row['reg_date'],'mdhi');
		}

		JSON(true, '', App::GetView(null, $dbList));
	}

	public function PostWrite($answerIs = false){
		$res = $this->GetAuth();
		if(!isset($_POST['article_seq']) || !strlen($_POST['article_seq'])) JSON(false, _MSG_WRONG_CONNECTED);
		if(!$res) JSON(false, _MSG_NO_AUTH);

		$this->model->Need = array('comment', 'article_seq');
		if(_MEMBERIS !== true){
			$this->model->Need[] = 'mname';
			$this->model->Need[] = 'pwd';
		}

		$res = $this->model->SetPostValues();
		if(!$res->result) JSON(false, $res->message);

		$first_seq = '';
		$first_member_is = 'n';
		$target = '';

		if($answerIs){
			$target = SetDBInt($_POST['target_seq']);
			$dbGet = DB::GetQryObj($this->model->table)
				->AddWhere('article_seq='.$_POST['article_seq'])
				->AddWhere('seq='.$target)
				->SetKey(array('seq', 'first_seq', 'first_member_is', 'secret'));
			$this->_CommonQry($dbGet);
			$targetData = $dbGet->Get();
			$first_seq = strlen($targetData['first_seq']) ? $targetData['first_seq'] : $targetData['seq'];
			$first_member_is = $targetData['first_member_is'];
			$this->model->SetValue('secret', $targetData['secret']);
		}
		else $this->model->SetValue('secret', isset($_POST['secret']) && $_POST['secret'] == 'y' ? 'y' : 'n');

		require_once _COMMONDIR.'/FileUpload.php';

		// 파일 업로드
		if(isset($_FILES['file'])){
			$fres_em = FileUpload($_FILES['file'], App::$SettingData['IMAGE_EXT'], $this->uploadUrl);

			if(is_string($fres_em)) JSON(false, $fres_em);
			else if(is_array($fres_em)){
				$this->model->SetValue('file', $fres_em['file']);
			}
		}

		// 기본 데이타
		$this->model->SetValue('article_seq', SetDBInt($_POST['article_seq']));
		$this->model->SetValue('reg_date', date('Y-m-d H:i:s'));

		// 회원유무
		if(_MEMBERIS === true){
			$member = CM::GetMember();

			$this->model->SetValue('muid', $_SESSION['member']['muid']);
			$this->model->SetValue('mlevel', $member['level']);
			$this->model->SetValue('mname', $member['nickname']);
		}

		// 답글쓰기라면 sort 정렬
		if($answerIs){
			$qry = DB::GetQryObj($this->model->table)
				->SetKey('mname, depth, muid, sort1, sort2')
				->AddWhere('article_seq = %d', App::$Data['article_seq'])
				->AddWhere('seq = %d', $target);
			$this->_CommonQry($qry);
			$row = $qry->Get();

			$qry = DB::UpdateQryObj($this->model->table)
				->SetData('sort2', 'sort2 + 1')
				->AddWhere('article_seq = %d', App::$Data['article_seq'])
				->AddWhere('sort1 = %d', $row['sort1'])
				->AddWhere('sort2 > %d', $row['sort2'])
				->SetSort('sort2 DESC');
			$this->_CommonQry($qry);
			$res = $qry->Run();
			if(!$res->result) JSON(false, 'ERROR#201');

			$this->model->SetValue('first_seq', $first_seq);
			$this->model->SetValue('first_member_is', $first_member_is);
			$this->model->SetValue('target_mname', $row['mname']);
			$this->model->SetValue('target_muid', $row['muid'] ? $row['muid'] : 0);
			$this->model->SetValue('sort1', $row['sort1']);
			$this->model->SetValue('sort2', $row['sort2'] + 1);
			$this->model->SetValue('depth', $row['depth'] + 1);
		}else{
			$this->model->SetValue('first_member_is', _MEMBERIS === true ? 'y' : 'n');
			$this->model->SetQueryValue('sort1', '(SELECT IF(COUNT(s.sort1) = 0, 0, MIN(s.sort1))-1 FROM '.$this->model->table.' as s WHERE s.article_seq='.App::$Data['article_seq'].')');
		}

		$this->_PostWriteInsertBefore();

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)) JSON(false, $error[0]);

		$res = $this->model->DBInsert();

		if($res->result){
			$this->_PostWriteInsertAfter($res->id);
			// 댓글갯수 업데이트
			$this->model->article_count_set($this->model->GetValue('article_seq'));
			JSON(true, _MSG_COMPLETE_REGISTER);
		}
		else JSON(result, $res->message ? $res->message : 'ERROR');
	}

	public function PostAnswer(){
		$this->PostWrite(true);
	}

	public function PostViewSecret(){
		$this->_GetData(App::$Data['article_seq'], $_POST['seq']);

		if(!_password_verify($_POST['pwd'], $this->model->GetValue('pwd'))){
			$same = false;
			if($this->model->GetValue('first_seq') && $this->model->GetValue('first_member_is') == 'n'){
				$dbGet = DB::GetQryObj($this->model->table)
					->AddWhere('article_seq = %d', $_POST['article_seq'])
					->AddWhere('seq = %d', $this->model->GetValue('first_seq'))
					->SetKey('pwd');
				$this->_CommonQry($dbGet);
				$first = $dbGet->Get();
				if(_password_verify($_POST['pwd'], $first['pwd'])) $same = true;
			}
			if(!$same) JSON(false, _MSG_WRONG_PASSWORD);
		}

		JSON(true, '', nl2br(GetDBText($this->model->GetValue('comment'))));
	}

	public function PostModify(){
		$res = $this->GetAuth();
		if(!$res) JSON(false, _MSG_NO_AUTH);

		require_once _COMMONDIR.'/FileUpload.php';

		$this->model->Need = array('comment');
		if(_MEMBERIS !== true){
			$this->model->Need[] = 'mnane';
		}

		$this->_GetData(App::$Data['article_seq'], $_POST['seq']);

		$res = $this->model->SetPostValues();
		if(!$res->result) JSON(false, $res->message);

		// 회원 글 체크
		if(strlen($this->model->GetValue('muid'))){
			if(_MEMBERIS !== true) JSON(false, '#ERROR#101');
			else if($this->model->GetValue('muid') != $_SESSION['member']['muid'] && CM::GetAdminIs()) JSON(false, 'ERROR#102');
		}
		else if(_MEMBERIS !== true || !CM::GetAdminIs()){
			$qry = DB::GetQryObj($this->model->table, false)
				->SetKey('pwd')
				->AddWhere('article_seq = %d', $_POST['article_seq'])
				->AddWhere('seq = %d', $_POST['seq']);
			$this->_CommonQry($qry);
			$pwd = $qry->Get();
			if(!_password_verify($_POST['pwd'], $pwd['pwd'])) JSON(false, _MSG_WRONG_PASSWORD);
		}

		// 파일 업로드
		if(isset($_FILES['file'])){
			$fres_em = FileUpload($_FILES['file'], App::$SettingData['IMAGE_EXT'], $this->uploadUrl);

			if(is_string($fres_em)) JSON(false, $fres_em);
			else if(is_array($fres_em)){
				if($this->model->GetValue('file')) @unlink (_UPLOAD_DIR.$this->model->GetValue('file'));
				$this->model->SetValue('file', $fres_em['file']);
				$this->model->SetValue('filenm', $_FILES['file']['name']);
			}
		}

		$this->_PostModifyUpdateBefore();  // Reserved

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)) JSON(false, $error[0]);

		// 기본 데이타
		$res = $this->model->DBUpdate();

		if($res->result){
			$this->_PostModifyUpdateAfter();  // Reserved
			JSON(true, _MSG_COMPLETE_MODIFY);
		}
		else JSON(false, $res->message ? $res->message : 'ERROR');

	}

	public function PostDelete(){
		$res = $this->GetAuth('Write');
		if(!$res) JSON(false, _MSG_NO_AUTH);

		$seq = SetDBInt($_POST['seq']);
		$article_seq = SetDBInt($_POST['article_seq']);

		$res = $this->_GetData(App::$Data['article_seq'], $_POST['seq']);
		if(!$res->result) JSON(false, $res->message ? $res->message : 'ERROR#201');

		// 회원 글 체크
		if($this->model->GetValue('muid')){
			if(_MEMBERIS !== true) JSON(false, 'ERROR#101');
			else if($this->model->GetValue('muid') != $_SESSION['member']['muid'] && !CM::GetAdminIs()) JSON(false, 'ERROR#102');
		}
		else if(_MEMBERIS !== true || !CM::GetAdminIs() || !$this->managerIs){
			$qry = DB::GetQryObj($this->model->table, false)
				->SetKey('pwd')
				->AddWhere('article_seq = %d', $_POST['article_seq'])
				->AddWhere('seq = %d', $_POST['seq']);
			$this->_CommonQry($qry);
			$getpwd = $qry->Get();
			if(!_password_verify($_POST['pwd'], $getpwd['pwd'])) JSON(false, _MSG_WRONG_PASSWORD);
		}

		$this->model->SetValue('delis', 'y');
		$this->model->DBUpdate();
		$this->model->article_count_set($article_seq);

		JSON(true, _MSG_COMPLETE_DELETE);
	}


	public function GetAuth(){
		if(!isset($this->boardManger)) return true;
		$memberLevel = _MEMBERIS === true ? $_SESSION['member']['level'] : 0;
		if($this->managerIs) return true;
		if($memberLevel < $this->boardManger->GetValue('auth_reply_level')) return false;
		return true;
	}

	protected function _GetData($id){
		$args = func_get_args();
		return $this->model->DBGet($args);
	}

	protected function _GetBoardData($id){
		$args = func_get_args();
		return $this->boardModel->DBGet($args);
	}

	protected function _MyArticleCheck(){
		$myArticleIs = false;
		if($this->managerIs || CM::GetAdminIs()) $myArticleIs = true;
		else if(strlen($this->boardModel->GetValue('muid')))
			$myArticleIs = (_MEMBERIS === true && $this->boardModel->GetValue('muid') == $_SESSION['member']['muid']);
		else if(isset($this->boardModel->data['target_muid']) && strlen($this->boardModel->GetValue('target_muid')))
			$myArticleIs = (_MEMBERIS === true && $this->boardModel->GetValue('target_muid') == $_SESSION['member']['muid']);
		return $myArticleIs;
	}
}
