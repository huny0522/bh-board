<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Admin;
use \BH_Application as App;
use \BH_Common as CF;

class BannerManagerController
{
	/**
	 * @var BannerModel
	 */
	public $model = null;

	public function __construct(){
		$this->model = App::GetModel('Banner');

		$dbGetList = new \BH_DB_GetList($this->model->table);
		$dbGetList->SetKey('DISTINCT category');
		while($row = $dbGetList->Get()) App::$Data['category'][] = $row['category'];
	}

	public function __init(){
		App::$Data['NowMenu'] = '001002';
		CF::AdminAuth();
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
			Redirect('-1', $res->message);
		}
		App::$Html = 'Write';
		App::View($this, $this->model);
	}
	public function PostWrite(){
		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			if(isset($_FILES['img'])){
				require_once _COMMONDIR.'/FileUpload.php';
				$fres_em = FileUpload($_FILES['img'], App::$SettingData['POSSIBLE_EXT'], '/board/'.date('ym').'/');

				if($fres_em === 'noext'){
					Redirect('-1', '등록 불가능한 파일입니다.');
				}
				else if(is_array($fres_em)){
					$this->model->SetValue('img', $fres_em['file']);
				}
			}

			$error = $this->model->GetErrorMessage();
			if(sizeof($error)){
				App::$Data['error'] = $error[0];
				App::View($this, $this->model);
			}else{
				$res = $this->model->DBInsert();
				if($res->result){
					CF::ContentImageUpate($this->model->table, array('seq' => $res->id), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');

					Redirect(App::URLAction().App::GetFollowQuery());
				}else{
					Redirect(App::URLAction().App::GetFollowQuery(), 'ERROR');
				}
			}
		}
	}

	public function PostModify(){
		$res = $this->model->DBGet(to10(App::$ID));
		$res = $this->model->SetPostValues();
		if(!$res->result){
			Redirect('-1',$res->message);
		}
		else{
			if(isset($_FILES['img'])){
				require_once _COMMONDIR.'/FileUpload.php';
				$fres_em = FileUpload($_FILES['img'], App::$SettingData['POSSIBLE_EXT'], '/board/'.date('ym').'/');

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
				Redirect(App::URLAction().App::GetFollowQuery(), $error[0]);
			}

			$res = $this->model->DBUpdate();
			if($res->result){
				CF::ContentImageUpate($this->model->table, array('seq' => to10(App::$ID)), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');
				$url = App::URLAction().App::GetFollowQuery();
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
				Redirect(App::URLAction().App::GetFollowQuery(), '삭제되었습니다.');
			}else{
				Redirect('-1', $res->message);
			}
		}
	}
}
