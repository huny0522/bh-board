<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Application as App;
use \BH_Common as CF;

class BoardController{
	/**
	 * @var BoardModel
	 */
	public $model;
	/**
	 * @var BoardManagerModel
	 */
	public $boardManger;
	public $managerIs = false;

	public function __construct(){
		require _DIR.'/Model/Board.model.php';
		$this->model = new \BoardModel();
	}

	public function __init(){
		$this->BoardSetting();
	}

	protected function BoardSetting(){
		if(!isset(App::$TID) || App::$TID == '') Redirect('-1', '잘못된 접근입니다.');

		require _DIR.'/Model/BoardManager.model.php';
		$this->boardManger = new \BoardManagerModel();
		$this->boardManger->DBGet(App::$TID);
		App::SetFollowQuery(array('page','searchType','searchKeyword','category'));

		$mid = CF::Get()->GetMember('mid');
		$manager = explode(',', $this->boardManger->GetValue('manager'));
		if ($mid !== false && in_array($mid, $manager)) {
			$this->managerIs = true;
		}

		App::$Html = '/Board/' . App::$Action.'.html';
		$layout = $this->boardManger->GetValue('layout');
		if($layout) App::$Layout = $layout;


		App::$_Value['categorys'] = array();
		if(!is_null($this->boardManger->GetValue('category')) && strlen($this->boardManger->GetValue('category'))){
			App::$_Value['categorys'] = explode(',', $this->boardManger->GetValue('category'));
		}
	}

	public function Index($viewPageIs = false){
		if(_AJAXIS === true) App::$Layout = null;

		$res = $this->GetAuth('List');
		if(!$res) Redirect('-1', _NO_AUTH);

		// 공지를 불러온다.
		App::$_Value['notice'] = array();
		$_GET['searchKeyword'] = isset($_GET['searchKeyword']) ? trim($_GET['searchKeyword']) : '';
		if((!isset($_GET['page']) || $_GET['page'] < 2) && !strlen($_GET['searchKeyword'])){
			$qry = new \BH_DB_GetList($this->model->table);
			$qry->AddWhere('delis=\'n\'');
			$qry->AddWhere('notice=\'y\'');
			App::$_Value['notice'] = $qry->GetRows();
		}

		// 리스트를 불러온다.
		$dbList = new \BH_DB_GetListWithPage($this->model->table);
		$dbList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbList->pageUrl = App::URLAction('').App::GetFollowQuery('page');
		$dbList->articleCount = $this->boardManger->GetValue('article_count');
		$dbList->AddWhere('delis=\'n\'');
		$dbList->sort = 'sort1, sort2';

		if(isset($_GET['category']) && strlen($_GET['category'])) $dbList->AddWhere('category = '.SetDBText($_GET['category']));

		if(isset($_GET['searchType']) && strlen($_GET['searchType']) && isset($_GET['searchKeyword']) && strlen($_GET['searchKeyword'])){
			switch($_GET['searchType']){
				case 's':
					$dbList->AddWhere('subject LIKE %s', '%'.$_GET['searchKeyword'].'%');
				break;
				case 'c':
					$dbList->AddWhere('content LIKE %s', '%'.$_GET['searchKeyword'].'%');
				break;
				case 'snc':
					$dbList->AddWhere('subject LIKE %s OR content LIKE %s', '%'.$_GET['searchKeyword'].'%', '%'.$_GET['searchKeyword'].'%');
				break;
			}
		}
		//$dbList->test = true;
		$dbList->Run();

		$html = '/Board/'.$this->boardManger->GetValue('skin').'/Index.html';
		if(file_exists(_SKINDIR.$html)) App::$Html = $html;

		if($viewPageIs) return App::_GetView($this, $this->model, $dbList);
		else App::_View($this, $this->model, $dbList);
	}

	public function PostView(){
		$this->View();
	}
	public function View(){
		if($this->boardManger->GetValue('list_in_view') == 'y') App::$_Value['List'] = $this->Index(true);

		if(!isset(App::$ID) || !strlen(App::$ID)) Redirect('-1');

		$seq = to10(App::$ID);

		if(_AJAXIS === true) App::$Layout = null;
		$viewAuth = $this->GetAuth('View');
		if(!$viewAuth) Redirect('-1', _NO_AUTH);

		$this->model->DBGet($seq);

		$data['answerAuth'] = $this->GetAuth('Answer');

		// 비밀번호없이 수정권한
		$data['modifyAuthDirect'] = false;
		if($this->GetAuth('Write') && _MEMBERIS === true && ($this->model->GetValue('muid') == $_SESSION['member']['muid'] || $_SESSION['member']['level'] == _SADMIN_LEVEL )){
			$data['modifyAuthDirect'] = true;
		}

		// 비밀글일경우 권한 : 관리자 또는 게시판 매니저, 글쓴이
		if($this->model->GetValue('secret') == 'y'){
			$viewAuth = false;

			// first_seq 가 있으면 첫째글을 호출
			if(strlen($this->model->GetValue('first_seq'))){
				$dbGet = new \BH_DB_Get($this->model->table);
				$dbGet->AddWhere('seq=' . $this->model->GetValue('first_seq'));
				$firstDoc = $dbGet->Get();
			}

			if(_MEMBERIS === true){
				// 관리자, 매니저 권한
				if($_SESSION['member']['level'] == _SADMIN_LEVEL || $this->managerIs){
					$viewAuth = true;
				}
				// 자신의 글 권한
				else if($this->model->GetValue('muid') == $_SESSION['member']['muid'] || (isset($firstDoc) && $this->model->GetValue('first_member_is') == 'y' && $firstDoc['muid'] == $_SESSION['member']['muid'])){
					$viewAuth = true;
				}
			}

			// 원글이나 현재 글이 비회원글일 경우 비밀번호를 체크
			if(!$viewAuth && (!$this->model->GetValue('muid') || $this->model->GetValue('first_member_is') == 'n')){
				if(_POSTIS !==	true) Redirect('-1', _WRONG_CONNECTED);

				if(_password_verify($_POST['pwd'], $this->model->GetValue('pwd')) || (isset($firstDoc) && _password_verify($_POST['pwd'], $firstDoc['pwd']))){
					$viewAuth = true;
				}
				else Redirect('-1', '비밀번호가 일치하지 않습니다.');
			}

			if(!$viewAuth) Redirect('-1', _NO_AUTH);
		}

		$cookieName = $this->model->table.$seq;
		if(!isset($_COOKIE[$cookieName]) || !$_COOKIE[$cookieName]){
			$dbUpdate = new \BH_DB_Update($this->model->table);
			$dbUpdate->SetData('hit', 'hit + 1');
			$dbUpdate->AddWhere('seq='.$seq);
			$dbUpdate->Run();
			setcookie($cookieName, 'y');
		}

		$html = '/Board/'.$this->boardManger->GetValue('skin').'/View.html';
		if(file_exists(_SKINDIR.$html)) App::$Html = $html;

		App::_View($this, $this->model, $data);
	}

	public function Write(){
		$res = $this->GetAuth('Write');
		if(!$res) Redirect('-1', _NO_AUTH);

		$html = '/Board/'.$this->boardManger->GetValue('skin').'/Write.html';
		if(file_exists(_SKINDIR.$html)) App::$Html = $html;

		App::_View($this, $this->model);
	}

	public function Answer(){
		$res = $this->GetAuth('Answer');
		if(!$res) Redirect('-1', _NO_AUTH);

		App::$Html = 'Write';
		$seq = to10($_GET['target']);
		if(!strlen($seq)) Redirect('-1');

		$qry = new \BH_DB_Get($this->model->table);
		$qry->AddWhere('seq = %d', $seq);
		$data = $qry->Get();

		//		// 비밀글일경우 답변 권한 : 관리자 또는 게시판 매니저
		//		if($data['secret'] == 'y'){
		//			if(_MEMBERIS !== true){
		//				Redirect('-1', _NO_AUTH);
		//			}
		//
		//			if($_SESSION['member']['level'] < _SADMIN_LEVEL){
		//				$member = CF::Get()->GetMember();
		//
		//				$manager = explode(',', $this->boardManger->data['manager']);
		//				if(!in_array($member['mid'], $manager)){
		//					Redirect('-1', _NO_AUTH);
		//				}
		//			}
		//		}

		$this->model->SetValue('subject', strpos('[답변]', $data['subject']) === false ? '[답변] '.$data['subject'] : $data['subject']);
		$this->model->SetValue('secret', $data['secret']);

		$html = '/Board/'.$this->boardManger->GetValue('skin').'/Write.html';
		if(file_exists(_SKINDIR.$html)) App::$Html = $html;

		App::_View($this, $this->model);
	}

	public function Modify(){
		if(!isset(App::$ID) || !strlen(App::$ID)){
			Redirect('-1');
		}

		$res = $this->GetAuth('Modify');
		if(!$res) Redirect('-1', _NO_AUTH);

		App::$Html = 'Write';
		$seq = to10(App::$ID);
		$this->model->DBGet($seq);

		// 회원 글 체크
		if(_MEMBERIS !== true || $_SESSION['member']['level'] != _SADMIN_LEVEL){
			$res = $this->PasswordCheck();
			if($res !== true) Redirect('-1', $res);
		}


		$html = '/Board/'.$this->boardManger->GetValue('skin').'/Write.html';
		if(file_exists(_SKINDIR.$html)) App::$Html = $html;

		App::_View($this, $this->model);
	}

	public function PostModify(){
		if(isset($_POST['mode']) && $_POST['mode'] == 'view'){
			$this->Modify();
			return;
		}
		$res = $this->GetAuth('Modify');
		if(!$res) Redirect('-1', _NO_AUTH);

		require_once _COMMONDIR.'/FileUpload.php';

		$seq = to10(App::$ID);

		$this->model->Need = array('subject', 'content', 'secret');
		if(_MEMBERIS !== true) $this->model->Need[] = 'mnane';
		else $this->model->AddExcept('pwd');

		$this->model->DBGet($seq);
		$res = $this->model->SetPostValues();
		if(!$res->result) Redirect(App::URLAction('View/'.App::$ID).App::GetFollowQuery(), $res->message);

		// 회원 글 체크
		if(_MEMBERIS !== true || $_SESSION['member']['level'] != _SADMIN_LEVEL){
			$res = $this->PasswordCheck();
			if($res !== true) Redirect(App::URLAction('View/'.App::$ID).App::GetFollowQuery(), $res);
		}

		// 파일 업로드
		for($n = 1; $n <= 2; $n++){
			if(!isset($_FILES['file'.$n])) continue;
			$fres_em = FileUpload($_FILES['file'.$n], App::$POSSIBLE_EXT, '/board/'.date('ym').'/');

			if($fres_em === 'noext') Redirect('-1', '등록 불가능한 파일입니다.');
			else if(is_array($fres_em)){
				if($this->model->GetValue('file'.$n)) @unlink (_UPLOAD_DIR.$this->model->GetValue('file'.$n));
				$this->model->SetValue('file'.$n, $fres_em['file']);
				$this->model->SetValue('filenm'.$n, $_FILES['file'.$n]['name']);
			}
		}

		// 기본 데이타
		$this->model->SetValue('htmlis', _MOBILEIS === true ? 'n' : 'y');

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)) Redirect(App::URLAction('View/'.App::$ID).App::GetFollowQuery(), $error[0]);

		$res2 = $this->model->DBUpdate();
		$this->ContentImageUpate($_POST['content'], $seq, 'modify');


		if($res2->result) Redirect(App::URLAction('View/'.App::$ID).App::GetFollowQuery(), '수정되었습니다.');
		else Redirect(App::URLAction('View/'.App::$ID).App::GetFollowQuery(), $res2->message ? $res2->message : 'ERROR');
	}

	public function PostAnswer(){
		$this->PostWrite();
	}

	public function PostWrite(){
		if(_POSTIS !== true) Redirect('-1', _WRONG_CONNECTED);

		$res = $this->GetAuth('Write');
		if(!$res) Redirect('-1', _NO_AUTH);

		$first_seq = '';
		$first_member_is = 'n';


		if(App::$Action == 'Answer'){
			$auth = $this->GetAuth('Answer');
			if(!$auth) Redirect('-1', _NO_AUTH);
			$dbGet = new \BH_DB_Get($this->model->table);
			$dbGet->AddWhere('seq=%d', to10($_POST['target']));
			$dbGet->SetKey('mname, depth, muid, sort1, sort2', 'seq', 'first_seq', 'first_member_is', 'category');
			App::$_Value['targetData'] = $dbGet->Get();
			$first_seq = strlen(App::$_Value['targetData']['first_seq']) ? App::$_Value['targetData']['first_seq'] : App::$_Value['targetData']['seq'];
			$first_member_is = App::$_Value['targetData']['first_member_is'];
		}

		require_once _COMMONDIR.'/FileUpload.php';

		$result = new \BH_Result();

		$this->model->Need = array('subject', 'content', 'secret');
		if(_MEMBERIS === true){
			$member = CF::Get()->GetMember();
			$this->model->AddExcept('pwd');
		}

		$res = $this->model->SetPostValues();
		if(!$res->result){
			App::$_Value['error'] = $res->message;
			$this->Write();
			return;
		}

		// 파일 업로드
		for($n = 1; $n <= 2; $n++){
			if(!isset($_FILES['file'.$n])) continue;
			$fres_em = FileUpload($_FILES['file'.$n], App::$POSSIBLE_EXT, '/board/'.date('ym').'/');

			if($fres_em === 'noext'){
				App::$_Value['error'] = '등록 불가능한 파일입니다.';
				$this->Write();
				return;
			}
			else if(is_array($fres_em)){
				$this->model->SetValue('file'.$n, $fres_em['file']);
				$this->model->SetValue('filenm'.$n, $_FILES['file'.$n]['name']);
			}
		}

		// 기본 데이타
		$this->model->SetValue('reg_date', date('Y-m-d H:i:s'));
		$this->model->SetValue('htmlis', _MOBILEIS === true ? 'n' : 'y');

		// 회원유무
		if(_MEMBERIS === true){
			$this->model->SetValue('muid', $_SESSION['member']['muid']);
			$this->model->SetValue('mlevel', $member['level']);
			$this->model->SetValue('mname', $member['nickname']);
		}

		// 답글쓰기라면 sort 정렬
		if(App::$Action == 'Answer'){
			$qry = new \BH_DB_Update($this->model->table);
			$qry->SetData('sort2', 'sort2 + 1');
			$qry->AddWhere('sort1 = %d', App::$_Value['targetData']['sort1']);
			$qry->AddWhere('sort2 > %d', App::$_Value['targetData']['sort2']);
			$qry->sort = 'sort2 DESC';
			$qry->Run();
			if(!$qry->result){
				App::$_Value['error'] = 'ERROR#201';
				$this->Write();
				return;
			}
			$this->model->SetValue('first_seq', $first_seq);
			$this->model->SetValue('first_member_is', $first_member_is);
			$this->model->SetValue('target_mname', App::$_Value['targetData']['mname']);
			$this->model->SetValue('target_muid', App::$_Value['targetData']['muid'] ? App::$_Value['targetData']['muid'] : 0);
			$this->model->SetValue('sort1', App::$_Value['targetData']['sort1']);
			//echo  $row['sort1'];exit;
			$this->model->SetValue('sort2', App::$_Value['targetData']['sort2'] + 1);
			$this->model->SetValue('depth', App::$_Value['targetData']['depth'] + 1);
		}else{
			$this->model->SetValue('first_member_is', _MEMBERIS === true ? 'y' : 'n');
			$this->model->SetQueryValue('sort1', '(SELECT IF(COUNT(s.sort1) = 0, 0, MIN(s.sort1))-1 FROM '.$this->model->table.' as s)');
		}
		// print_r($this->model->data);
		// exit;

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)){
			App::$_Value['error'] = $error[0];
			$this->Write();
			return;
		}

		$res = $this->model->DBInsert();
		$result->result = $res->result;
		$result->message = $res->message;

		if($result->result){
			$this->ContentImageUpate($_POST['content'], $res->id);
			Redirect(App::URLAction(), '등록되었습니다.');
		}else{
			Redirect(App::URLAction('Write').App::GetFollowQuery(), $result->message ? $result->message : 'ERROR');
		}
	}

	public function PostDelete(){
		if(_POSTIS !== true) Redirect('-1', _WRONG_CONNECTED);

		$res = $this->GetAuth('Write');
		if(!$res) Redirect('-1', _NO_AUTH);

		$seq = to10(App::$ID);

		$this->model->DBGet($seq);

		// 회원 글 체크
		if(_MEMBERIS !== true || $_SESSION['member']['level'] != _SADMIN_LEVEL || !$this->managerIs){
			$res = $this->PasswordCheck();
			if($res !== true){
				Redirect('-1', $res);
			}
		}

		$this->model->SetValue('delis', 'y');
		$this->model->DBUpdate();

		Redirect(App::URLAction().App::GetFollowQuery(), '삭제되었습니다.');
	}


	public function GetAuth($mode){
		$memberLevel = _MEMBERIS === true ? $_SESSION['member']['level'] : 0;
		if($this->managerIs) return true;
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
	protected function ContentImageUpate($content, $seq, $mode = 'write'){
		$newcontent = $content;
		$maxImage = _MAX_IMAGE_COUNT;

		if($mode == 'modify'){
			$dbGetList = new \BH_DB_GetList($this->model->imageTable);
			$dbGetList->AddWhere('article_seq='.$seq);
			while($img = $dbGetList->Get()){
				if(strpos($content,$img['image']) === false){
					// 파일이 없으면 삭제
					@unlink(_UPLOAD_DIR.$img['image']);

					if($img['image'] == $this->model->GetValue('thumnail')) $this->model->SetValue('thumnail', '');

					$qry = new \BH_DB_Delete($this->model->table.'_images');
					$qry->AddWhere('article_seq = '.$img['article_seq']);
					$qry->AddWhere('seq = '.$img['seq']);
					$qry->Run();
				}
			}
		}

		$dbGet = new \BH_DB_Get($this->model->imageTable);
		$dbGet->AddWhere('article_seq='.$seq);
		$dbGet->SetKey('COUNT(*) as cnt');
		$cnt = $dbGet->Get();
		$imageCount = $cnt['cnt'];

		if(isset($_POST['addimg']) && is_array($_POST['addimg'])){
			$ym = date('ym');
			foreach($_POST['addimg'] as $img){
				$exp = explode('|', $img);

				if(strpos($content, $exp[0]) !== false){
					if($imageCount >= $maxImage){
						@unlink(_UPLOAD_DIR.$exp[0]);
						continue;
					}

					$newpath = str_replace('/temp/', '/image/'.$ym.'/', $exp[0]);
					$uploadDir = _UPLOAD_DIR.'/image/'.$ym;
					if(!is_dir($uploadDir)){
						mkdir($uploadDir, 0777, true);
					}
					@copy(_UPLOAD_DIR.$exp[0],_UPLOAD_DIR.$newpath);
					$newcontent = str_replace($exp[0],$newpath, $newcontent);
					// 파일이 있으면 등록

					unset($dbInsert);
					$dbInsert = new \BH_DB_Insert($this->model->imageTable);
					$dbInsert->data['article_seq'] = $seq;
					$dbInsert->data['image'] = SetDBText($newpath);
					$dbInsert->data['imagename'] = SetDBText($exp[1]);
					$dbInsert->decrement = 'seq';
					$dbInsert->AddWhere('article_seq = %d', $seq);
					//$params['test'] = true;
					$dbInsert->Run();
					$imageCount++;
				}
				@unlink(_UPLOAD_DIR.$exp[0]);
			}

			if($newcontent != $content){
				if(!$this->model->GetValue('thumnail')){
					$dbGet = new \BH_DB_Get($this->model->imageTable);
					$dbGet->AddWhere('article_seq='.$seq);
					$dbGet->sort = 'seq';
					$new = $dbGet->Get();
					$this->model->SetValue('thumnail', $new['image']);
				}
				$qry = new \BH_DB_Update($this->model->table);
				$qry->SetDataStr('thumnail', $this->model->GetValue('thumnail'));
				$qry->SetDataStr('content', $newcontent);
				$qry->AddWhere('seq = '.$seq);
				$qry->Run();
			}
		}

		DeleteOldTempFiles(_UPLOAD_DIR.'/temp/', strtotime('-6 hours'));
		return true;
	}

	protected function PasswordCheck(){
		if($this->model->GetValue('muid')){
			if(_MEMBERIS !== true) return 'ERROR#101';
			else if($this->model->GetValue('muid') != $_SESSION['member']['muid']) return 'ERROR#102';
		}
		else{
			if(!isset($_POST['pwd'])) return _WRONG_CONNECTED;

			$pwd = \DB::SQL()->Fetch('SELECT pwd FROM '.$this->model->table.' WHERE seq='.$this->model->GetValue('seq'));
			if(!_password_verify($_POST['pwd'], $pwd['pwd'])){
				return '비밀번호가 일치하지 않습니다.';
			}
		}
		return true;
	}

	public function _DirectView(){
		$this->View();
	}
}
