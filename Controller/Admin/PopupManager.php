<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Admin;
use \BH_Application as App;
use \BH as BH;
class PopupManagerController
{
	/**
	 * @var \PopupModel
	 */
	public $model = null;
	public function __construct(){
		App::$_Value['NowMenu'] = '001003';
		BH::CF()->AdminAuth();

		require _DIR.'/Model/Popup.model.php';
		$this->model = new \PopupModel();

		BH::APP()->Layout = '_Admin';
	}

	public function Index(){
		// 리스트를 불러온다.
		$dbGetList = BH::DBListPage($this->model->table);
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = BH::APP()->URLAction().BH::APP()->GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->Run();

		BH::APP()->_View($this->model, $dbGetList);
	}

	public function Write(){
		BH::APP()->_View($this->model);
	}

	public function Modify(){
		$res = $this->model->DBGet(to10(BH::APP()->ID));

		if(!$res->result){
			Redirect('-1', $res->message);
		}
		BH::APP()->Html = 'Write';
		BH::APP()->_View($this->model);
	}
	public function PostWrite(){
		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			if(isset($_FILES['img'])){
				require_once _COMMONDIR.'/FileUpload.php';
				$fres_em = FileUpload($_FILES['img'], App::$POSSIBLE_EXT, '/board/'.date('ym').'/');

				if($fres_em === 'noext'){
					Redirect('-1', '등록 불가능한 파일입니다.');
				}
				else if(is_array($fres_em)){
					$this->model->SetValue('img', $fres_em['file']);
				}
			}

			$error = $this->model->GetErrorMessage();
			if(sizeof($error)){
				Redirect(BH::APP()->URLAction().BH::APP()->GetFollowQuery(), $error[0]);
			}

			$res = $this->model->DBInsert();
			if($res->result){
				BH::CF()->ContentImageUpate($this->model->table, array('seq' => $res->id), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');

				Redirect(BH::APP()->URLAction().BH::APP()->GetFollowQuery());
			}else{
				Redirect(BH::APP()->URLAction().BH::APP()->GetFollowQuery(), 'ERROR');
			}
		}
	}

	public function PostModify(){
		$res = $this->model->DBGet(to10(BH::APP()->ID));
		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			if(isset($_FILES['img'])){
				require_once _COMMONDIR.'/FileUpload.php';
				$fres_em = FileUpload($_FILES['img'], App::$POSSIBLE_EXT, '/board/'.date('ym').'/');

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
				Redirect(BH::APP()->URLAction().BH::APP()->GetFollowQuery(), $error[0]);
			}

			$res = $this->model->DBUpdate();
			if($res->result){
				BH::CF()->ContentImageUpate($this->model->table, array('seq' => to10(BH::APP()->ID)), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');
				$url = BH::APP()->URLAction().BH::APP()->GetFollowQuery();
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
				Redirect(BH::APP()->URLAction().BH::APP()->GetFollowQuery(), '삭제되었습니다.');
			}else{
				Redirect('-1', $res->message);
			}
		}
	}
}
