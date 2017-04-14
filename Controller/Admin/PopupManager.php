<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Admin;

class PopupManagerController extends \BH_Controller
{
	/**
	 * @var PopupModel
	 */
	public $model = null;
	public function __Init(){
		$this->_Value['NowMenu'] = '001003';
		$this->_CF->AdminAuth();

		require _DIR.'/Model/Popup.model.php';
		$this->model = new \PopupModel();

		$this->Layout = '_Admin';
	}

	public function Index(){
		// 리스트를 불러온다.
		$dbGetList = new \BH_DB_GetListWithPage($this->model->table);
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = $this->URLAction().$this->GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->Run();

		$this->_View($this->model, $dbGetList);
	}

	public function Write(){
		$this->_View($this->model);
	}

	public function Modify(){
		$res = $this->model->DBGet(to10($this->ID));

		if(!$res->result){
			Redirect('-1', $res->message);
		}
		$this->Html = 'Write';
		$this->_View($this->model);
	}
	public function PostWrite(){
		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			if(isset($_FILES['img'])){
				require_once _COMMONDIR.'/FileUpload.php';
				$fres_em = FileUpload($_FILES['img'], self::$POSSIBLE_EXT, '/board/'.date('ym').'/');

				if($fres_em === 'noext'){
					Redirect('-1', '등록 불가능한 파일입니다.');
				}
				else if(is_array($fres_em)){
					$this->model->SetValue('img', $fres_em['file']);
				}
			}

			$error = $this->model->GetErrorMessage();
			if(sizeof($error)){
				Redirect($this->URLAction().$this->GetFollowQuery(), $error[0]);
			}

			$res = $this->model->DBInsert();
			if($res->result){
				$this->_CF->ContentImageUpate($this->model->table, array('seq' => $res->id), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');

				Redirect($this->URLAction().$this->GetFollowQuery());
			}else{
				Redirect($this->URLAction().$this->GetFollowQuery(), 'ERROR');
			}
		}
	}

	public function PostModify(){
		$res = $this->model->DBGet(to10($this->ID));
		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			if(isset($_FILES['img'])){
				require_once _COMMONDIR.'/FileUpload.php';
				$fres_em = FileUpload($_FILES['img'], self::$POSSIBLE_EXT, '/board/'.date('ym').'/');

				if($fres_em === 'noext'){
					Redirect('-1', '등록 불가능한 파일입니다.');
				}
				else if(is_array($fres_em)){
					if($this->model->GetValue('img')) @unlink(_UPLOAD_DIR.$this->model->GetValue('img'));
					$this->model->SetValue('img', $fres_em['file']);
				}
			}

			$error = $this->model->GetErrorMessage();
			if(sizeof($error)){
				Redirect($this->URLAction().$this->GetFollowQuery(), $error[0]);
			}

			$res = $this->model->DBUpdate();
			if($res->result){
				$this->_CF->ContentImageUpate($this->model->table, array('seq' => to10($this->ID)), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');
				$url = $this->URLAction().$this->GetFollowQuery();
				Redirect($url, '수정완료');
			}else{
				Redirect('-1', 'ERROR');
			}
		}
	}

	public function PostDelete(){
		if(isset($_POST['seq']) && $_POST['seq'] != ''){
			$res = $this->model->DBDelete(to10($_POST['seq']));
			if($res->result){
				Redirect($this->URLAction().$this->GetFollowQuery(), '삭제되었습니다.');
			}else{
				Redirect('-1', $res->message);
			}
		}
	}
}
