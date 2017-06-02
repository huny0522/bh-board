<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Application as App;
use \BH_Common as CF;

class ReplyController{
	/** @var ReplyModel */
	public $model;
	/** @var BoardModel */
	public $boardModel;
	/** @var BoardManagerModel */
	public $boardManger;
	public $managerIs = false;

	public function __construct(){
		$this->model = App::GetModel('Reply');
		$this->boardModel = App::GetModel('Board');
		$this->boardManger = App::GetModel('BoardManager');
	}

	public function __init(){
		if(_POSTIS !== true) exit;
		App::$Data['article_seq'] = SetDBInt((string)$_POST['article_seq']);

		if(!isset(App::$TID) || App::$TID == ''){
			exit;
		}
		App::$Layout = null;

		$this->boardManger->DBGet(App::$TID);

		$mid = CF::GetMember('mid');
		$manager = explode(',', $this->boardManger->GetValue('manager'));
		if ($mid !== false && strlen($mid) && in_array($mid, $manager)) {
			$this->managerIs = true;
		}
	}

	public function PostIndex(){
		if(!isset($_POST['article_seq']) || !strlen($_POST['article_seq'])){
			exit;
		}

		if(isset($this->boardManger) && $this->boardManger->GetValue('use_reply') == 'n') return;

		$this->boardModel->DBGet(App::$Data['article_seq']);
		$myArticleIs = $this->MyArticleCheck();

		// 리스트를 불러온다.
		$dbList = new \BH_DB_GetListWithPage($this->model->table);
		$dbList->page = isset($_POST['page']) ? $_POST['page'] : 1;
		//$dbList->pageUrl = App::URLAction('').App::GetFollowQuery('page');
		$dbList->pageUrl = '#';
		$dbList->articleCount = isset($this->boardManger) ? $this->boardManger->GetValue('article_count') : 20;
		$dbList->AddWhere('article_seq='.App::$Data['article_seq']);
		$dbList->sort = 'sort1, sort2';
		if(method_exists($this, 'IndexAddQuery')) $this->IndexAddQuery($dbList);
		$dbList->DrawRows();
		foreach($dbList->data as &$row){
			// 비밀번호없이 수정권한
			$row['modifyAuthDirect'] = false;
			if($this->GetAuth() && _MEMBERIS === true && ($row['muid'] == $_SESSION['member']['muid'] || $_SESSION['member']['level'] == _SADMIN_LEVEL )){
				$row['modifyAuthDirect'] = true;
			}

			// 수정 버튼 보기
			if(
				(_MEMBERIS === true && ($row['muid'] == $_SESSION['member']['muid'] || $_SESSION['member']['level'] == _SADMIN_LEVEL)) ||
				(!strlen($row['muid']))
			) $row['modifyAuth'] = true;
			else $row['modifyAuth'] = false;

			// 삭제 버튼 보기
			if(
				$row['modifyAuth'] || $this->managerIs
			) $row['deleteAuth'] = true;
			else $row['deleteAuth'] = false;

			// 비밀글일 경우 본문작성자, 댓글작성자, 매니저, 관리자 보기권한 부여
			$row['secretIs'] = false;
			if($row['delis'] == 'y') $row['comment'] = '삭제된 댓글입니다.';
			else if($row['secret'] == 'y'){
				if(!$myArticleIs){
					if(!(_MEMBERIS === true && ($row['muid'] == $_SESSION['member']['muid'] || $_SESSION['member']['level'] === _SADMIN_LEVEL || $this->managerIs))){
						$row['comment'] = '비밀글입니다.';
						$row['secretIs'] = true;
					}
				}
			}

			$row['kdate'] = krDate($row['reg_date'],'mdhi');
		}

		if(isset($this->boardManger)){
			$html = '/'.App::$ControllerName.'/'.$this->boardManger->GetValue('reply_skin').'/Index.html';
			if(file_exists(_SKINDIR.$html)) App::$Html = $html;
		}

		JSON(true, '', App::GetView($this, null, $dbList));
	}

	public function PostWrite($answerIs = false){
		$res = $this->GetAuth();
		if(!$res){
			echo json_encode(array('result' => false));
			exit;
		}

		if(!isset($_POST['article_seq']) || !strlen($_POST['article_seq'])){
			exit;
		}

		$this->model->Need = array('comment', 'article_seq');
		if(_MEMBERIS !== true){
			$this->model->Need[] = 'mname';
			$this->model->Need[] = 'pwd';
		}

		$res = $this->model->SetPostValues();
		if(!$res->result){
			echo json_encode(array('result' => false, 'message' => $res->message));
			exit;
		}

		$first_seq = '';
		$first_member_is = 'n';
		$target = '';

		if($answerIs){
			$target = SetDBInt($_POST['target_seq']);
			$dbGet = new \BH_DB_Get($this->model->table);
			$dbGet->AddWhere('article_seq='.$_POST['article_seq']);
			$dbGet->AddWhere('seq='.$target);
			$dbGet->SetKey(array('seq', 'first_seq', 'first_member_is', 'secret'));
			$targetData = $dbGet->Get();
			$first_seq = strlen($targetData['first_seq']) ? $targetData['first_seq'] : $targetData['seq'];
			$first_member_is = $targetData['first_member_is'];
			$this->model->SetValue('secret', $targetData['secret']);
		}
		else $this->model->SetValue('secret', isset($_POST['secret']) && $_POST['secret'] == 'y' ? 'y' : 'n');

		require_once _COMMONDIR.'/FileUpload.php';

		// 파일 업로드
		if(isset($_FILES['file'])){
			$fres_em = FileUpload($_FILES['file'], App::$SettingData['IMAGE_EXT'], '/reply/' . date('ym') . '/');

			if($fres_em === 'noext'){
				echo json_encode(array('result' => false, 'message' => '등록 불가능한 파일입니다.'));
				exit;
			}else if(is_array($fres_em)){
				$this->model->SetValue('file', $fres_em['file']);
			}
		}

		// 기본 데이타
		$this->model->SetValue('article_seq', SetDBInt($_POST['article_seq']));
		$this->model->SetValue('reg_date', date('Y-m-d H:i:s'));

		// 회원유무
		if(_MEMBERIS === true){
			$member = CF::GetMember();

			$this->model->SetValue('muid', $_SESSION['member']['muid']);
			$this->model->SetValue('mlevel', $member['level']);
			$this->model->SetValue('mname', $member['nickname']);
		}

		// 답글쓰기라면 sort 정렬
		if($answerIs){
			$qry = new \BH_DB_Get($this->model->table);
			$qry->SetKey('mname, depth, muid, sort1, sort2');
			$qry->AddWhere('article_seq = %d', App::$Data['article_seq']);
			$qry->AddWhere('seq = %d', $target);
			$row = $qry->Get();

			$qry = new \BH_DB_Update($this->model->table);
			$qry->SetData('sort2', 'sort2 + 1');
			$qry->AddWhere('article_seq = %d', App::$Data['article_seq']);
			$qry->AddWhere('sort1 = %d', $row['sort1']);
			$qry->AddWhere('sort2 > %d', $row['sort2']);
			$qry->sort = 'sort2 DESC';
			$res = $qry->Run();
			if(!$res->result){
				echo json_encode(array('result' => false, 'message' => 'ERROR#201'));
				exit;
			}
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

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)){
			echo json_encode(array('result' => false, 'message' => $error[0]));
			exit;
		}

		if(method_exists($this, 'WriteAddQuery'))$this->WriteAddQuery();

		$res = $this->model->DBInsert();

		if($res->result){
			// 댓글갯수 업데이트
			$this->model->article_count_set($this->model->GetValue('article_seq'));
			echo json_encode(array('result' => true, 'message' => '등록되었습니다.'));
		}else{
			echo json_encode(array('result' => false, $res->message ? $res->message : 'ERROR'));
		}
	}

	public function PostAnswer(){
		$this->PostWrite(true);
	}

	public function PostViewSecret(){
		$this->GetData();

		if(!_password_verify($_POST['pwd'], $this->model->GetValue('pwd'))){
			$same = false;
			if($this->model->GetValue('first_seq') && $this->model->GetValue('first_member_is') == 'n'){
				$dbGet = new \BH_DB_Get($this->model->table);
				$dbGet->AddWhere('article_seq = '.SetDBInt($_POST['article_seq']));
				$dbGet->AddWhere('seq = '.$this->model->GetValue('first_seq'));
				$dbGet->SetKey('pwd');
				$first = $dbGet->Get();
				if(_password_verify($_POST['pwd'], $first['pwd'])) $same = true;
			}
			if(!$same){
				echo json_encode(array('result' => false, 'message' => '비밀번호가 일치하지 않습니다.'));
				exit;
			}
		}

		echo json_encode(array('result' => true, 'data' => nl2br(GetDBText($this->model->GetValue('comment')))));

	}

	public function PostModify(){
		$res = $this->GetAuth();
		if(!$res){
			echo json_encode(array('result' => false));
			exit;
		}

		require_once _COMMONDIR.'/FileUpload.php';

		$result = new \BH_Result();

		$this->model->Need = array('comment');
		if(_MEMBERIS !== true){
			$this->model->Need[] = 'mnane';
		}

		$this->GetData();

		$res = $this->model->SetPostValues();
		if(!$res->result){
			echo json_encode(array('result' => false, 'message' => $res->message));
			exit;
		}

		// 회원 글 체크
		if(strlen($this->model->GetValue('muid'))){
			if(_MEMBERIS !== true){
				echo json_encode(array('result' => false, 'message' => 'ERROR#101'));
				exit;
			}
			else if($this->model->GetValue('muid') != $_SESSION['member']['muid'] && $_SESSION['member']['level'] < _SADMIN_LEVEL){
				echo json_encode(array('result' => false, 'message' => 'ERROR#102'));
				exit;
			}
		}
		else if(_MEMBERIS !== true || $_SESSION['member']['level'] < _SADMIN_LEVEL){
			$pwd = \DB::SQL()->Fetch('SELECT pwd FROM '.$this->model->table.' WHERE article_seq='.SetDBInt($_POST['article_seq']).' AND seq='.SetDBInt($_POST['seq']));
			if(!_password_verify($_POST['pwd'], $pwd['pwd'])){
				echo json_encode(array('result' => false, 'message' => '비밀번호가 일치하지 않습니다.'));
				exit;
			}
		}

		// 파일 업로드
		if(isset($_FILES['file'])){
			$fres_em = FileUpload($_FILES['file'], App::$SettingData['IMAGE_EXT'], '/board/'.date('ym').'/');

			if($fres_em === 'noext'){
				echo json_encode(array('result' => false, 'message' => '등록 불가능한 파일입니다.'));
				exit;
			}
			else if(is_array($fres_em)){
				if($this->model->GetValue('file')) @unlink (_UPLOAD_DIR.$this->model->GetValue('file'));
				$this->model->SetValue('file', $fres_em['file']);
				$this->model->SetValue('filenm', $_FILES['file']['name']);
			}
		}

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)){
			echo json_encode(array('result' => false, 'message' => $error[0]));
			exit;
		}

		// 기본 데이타
		$res2 = $this->model->DBUpdate();

		if($res2->result){
			echo json_encode(array('result' => true, 'message' => '수정되었습니다.'));
			exit;
		}else{
			echo json_encode(array('result' => false, 'message' => $res2->message ? $res2->message : 'ERROR'));
			exit;
		}
	}



	public function PostDelete(){
		$res = $this->GetAuth('Write');
		if(!$res){
			echo json_encode(array('result' => false));
			exit;
		}

		$seq = SetDBInt($_POST['seq']);
		$article_seq = SetDBInt($_POST['article_seq']);

		$res = $this->GetData();
		if(!$res->result) JSON(false, $res->message ? $res->message : 'ERROR#201');

		// 회원 글 체크
		if($this->model->GetValue('muid')){
			if(_MEMBERIS !== true) JSON(false, 'ERROR#101');
			else if($this->model->GetValue('muid') != $_SESSION['member']['muid'] && $_SESSION['member']['level'] < _SADMIN_LEVEL) JSON(false, 'ERROR#102');
		}
		else if(_MEMBERIS !== true || $_SESSION['member']['level'] < _SADMIN_LEVEL || !$this->managerIs){
			$getpwd = \DB::SQL()->Fetch('SELECT pwd FROM '.$this->model->table.' WHERE article_seq = '.$article_seq.' AND seq='.$seq);
			if(!_password_verify($_POST['pwd'], $getpwd['pwd'])){
				echo json_encode(array('result' => false, 'message' => '비밀번호가 일치하지 않습니다.'));
				exit;
			}
		}

		$this->model->SetValue('delis', 'y');
		$this->model->DBUpdate();
		$this->model->article_count_set($article_seq);

		echo json_encode(array('result' => true, 'message' => '삭제되었습니다.'));
	}


	public function GetAuth(){
		if(!isset($this->boardManger)) return true;
		$memberLevel = _MEMBERIS === true ? $_SESSION['member']['level'] : 0;
		if($this->managerIs) return true;
		if($memberLevel < $this->boardManger->GetValue('auth_reply_level')) return false;
		return true;
	}

	public function GetData(){
		return $this->model->DBGet(SetDBInt($_POST['article_seq']), SetDBInt($_POST['seq']));
	}

	protected function MyArticleCheck(){
		$myArticleIs = false;
		if($this->managerIs || (_MEMBERIS === true && $_SESSION['member']['level'] == _SADMIN_LEVEL)) $myArticleIs = true;
		else if(strlen($this->boardModel->GetValue('muid'))){
			$myArticleIs = (_MEMBERIS === true && $this->boardModel->GetValue('muid') == $_SESSION['member']['muid']);
		}
		return $myArticleIs;
	}
}
