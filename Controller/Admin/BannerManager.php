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
		require_once _MODELDIR.'/Banner.model.php';
		$this->model = new \BannerModel();

		$dbGetList = new \BH_DB_GetList($this->model->table);
		$dbGetList->SetKey('DISTINCT category');
		while($row = $dbGetList->Get()) App::$_Value['category'][] = $row['category'];
	}

	public function __init(){
		App::$_Value['NowMenu'] = '001002';
		CF::Get()->AdminAuth();
		App::$Instance->Layout = '_Admin';
	}

	public function Index(){
		// 리스트를 불러온다.
		$dbGetList = new \BH_DB_GetListWithPage($this->model->table);
		$dbGetList->page = isset($_GET['page']) ? $_GET['page'] : 1;
		$dbGetList->pageUrl = App::$Instance->URLAction().App::$Instance->GetFollowQuery('page');
		$dbGetList->articleCount = 20;
		$dbGetList->Run();

		App::$Instance->_View($this, $this->model, $dbGetList);
	}

	public function Write(){
		App::$Instance->_View($this, $this->model);
	}

	public function Modify(){
		$res = $this->model->DBGet(to10(App::$Instance->ID));

		if(!$res->result){
			Redirect('-1', $res->message);
		}
		App::$Instance->Html = 'Write';
		App::$Instance->_View($this, $this->model);
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
				App::$_Value['error'] = $error[0];
				App::$Instance->_View($this, $this->model);
			}else{
				$res = $this->model->DBInsert();
				if($res->result){
					CF::Get()->ContentImageUpate($this->model->table, array('seq' => $res->id), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');

					Redirect(App::$Instance->URLAction().App::$Instance->GetFollowQuery());
				}else{
					Redirect(App::$Instance->URLAction().App::$Instance->GetFollowQuery(), 'ERROR');
				}
			}
		}
	}

	public function PostModify(){
		$res = $this->model->DBGet(to10(App::$Instance->ID));
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
				Redirect(App::$Instance->URLAction().App::$Instance->GetFollowQuery(), $error[0]);
			}

			$res = $this->model->DBUpdate();
			if($res->result){
				CF::Get()->ContentImageUpate($this->model->table, array('seq' => to10(App::$Instance->ID)), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');
				$url = App::$Instance->URLAction().App::$Instance->GetFollowQuery();
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
				Redirect(App::$Instance->URLAction().App::$Instance->GetFollowQuery(), '삭제되었습니다.');
			}else{
				Redirect('-1', $res->message);
			}
		}
	}
}
