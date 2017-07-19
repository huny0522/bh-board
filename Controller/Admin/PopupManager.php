<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class PopupManager
{
	/**
	 * @var \PopupModel
	 */
	public $model = null;

	public function __construct(){
		$this->model = App::InitModel('Popup');
	}

	public function __init(){
		App::$Data['NowMenu'] = '001003';
		CM::AdminAuth();

		App::$Layout = '_Admin';
	}

	public function Index(){
		// 리스트를 불러온다.
		$dbGetList = new \BH_DB_GetListWithPage($this->model->table);
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = App::URLAction().App::GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->Run();

		App::View($this, $this->model, $dbGetList);
	}

	public function Write(){
		App::View($this, $this->model);
	}

	public function Modify(){
		$res = $this->model->DBGet(to10(App::$ID));

		if(!$res->result){
			URLReplace('-1', $res->message);
		}
		App::$Html = 'Write';
		App::View($this, $this->model);
	}
	public function PostWrite(){
		$res = $this->model->SetPostValues();
		if(!$res->result){
			URLReplace('-1',$res->message);
		}
		else{
			if(isset($_FILES['img'])){
				require_once _COMMONDIR.'/FileUpload.php';
				$fres_em = FileUpload($_FILES['img'], App::$SettingData['POSSIBLE_EXT'], '/board/'.date('ym').'/');

				if(is_string($fres_em)) URLReplace('-1', $fres_em);
				else if(is_array($fres_em)){
					$this->model->SetValue('img', $fres_em['file']);
				}
			}

			$error = $this->model->GetErrorMessage();
			if(sizeof($error)){
				URLReplace(App::URLAction().App::GetFollowQuery(), $error[0]);
			}

			$res = $this->model->DBInsert();
			if($res->result){
				CM::ContentImageUpate($this->model->table, array('seq' => $res->id), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');

				URLReplace(App::URLAction().App::GetFollowQuery());
			}else{
				URLReplace(App::URLAction().App::GetFollowQuery(), 'ERROR');
			}
		}
	}

	public function PostModify(){
		$res = $this->model->DBGet(to10(App::$ID));
		$res = $this->model->SetPostValues();
		if(!$res->result){
			URLReplace('-1',$res->message);
		}
		else{
			if(isset($_FILES['img'])){
				require_once _COMMONDIR.'/FileUpload.php';
				$fres_em = FileUpload($_FILES['img'], App::$SettingData['POSSIBLE_EXT'], '/board/'.date('ym').'/');

				if(is_string($fres_em)) URLReplace('-1', $fres_em);
				else if(is_array($fres_em)){
					if($this->model->GetValue('img')) @unlink(_UPLOAD_DIR.$this->model->GetValue('img'));
					$this->model->SetValue('img', $fres_em['file']);
				}
			}

			$error = $this->model->GetErrorMessage();
			if(sizeof($error)){
				URLReplace(App::URLAction().App::GetFollowQuery(), $error[0]);
			}

			$res = $this->model->DBUpdate();
			if($res->result){
				CM::ContentImageUpate($this->model->table, array('seq' => to10(App::$ID)), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');
				$url = App::URLAction().App::GetFollowQuery();
				URLReplace($url, '수정완료');
			}else{
				URLReplace('-1', 'ERROR');
			}
		}
	}

	public function PostDelete(){
		if(isset($_POST['seq']) && $_POST['seq'] != ''){
			$res = $this->model->DBDelete(to10($_POST['seq']));
			if($res->result){
				URLReplace(App::URLAction().App::GetFollowQuery(), '삭제되었습니다.');
			}else{
				URLReplace('-1', $res->message);
			}
		}
	}
}
