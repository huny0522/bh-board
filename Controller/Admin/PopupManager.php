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
		$this->model = new \PopupModel();

		$dbGetList = new \BH_DB_GetList($this->model->table);
		$dbGetList->SetKey('DISTINCT category');
		App::$data['category'] = array();
		if(isset(App::$settingData['popupCategory'])) foreach(App::$settingData['popupCategory'] as $v){
			App::$data['category'][$v] = $v;
		}

		while($row = $dbGetList->Get()) App::$data['category'][$row['category']] = $row['category'];
	}

	public function __init(){
		App::$data['NowMenu'] = '001003';
		CM::AdminAuth();

		App::$layout = '_Admin';
		App::SetFollowQuery(array('category', 'page', 'keyword', 'kind'));
	}

	public function Index(){
		// 리스트를 불러온다.
		$qry = DB::GetListPageQryObj($this->model->table)
			->SetPage(Get('page'))
			->SetPageUrl(App::URLAction().App::GetFollowQuery('page'))
			->SetSort('seq DESC')
			->SetArticleCount(20);

		if(!EmptyGet('keyword')) $qry->AddWhere('INSTR(`subject`, %s)', Get('keyword'));
		if(!EmptyGet('category')) $qry->AddWhere('`category` = %s', Get('category'));
		if(!EmptyGet('kind')) $qry->AddWhere('FIND_IN_SET(%s, `kind`)', Get('kind'));

		App::View($this->model, $qry->Run());
	}

	public function Write(){
		App::View($this->model);
	}

	public function Modify(){
		$res = $this->model->DBGet(to10(App::$id));

		if(!$res->result){
			URLReplace('-1', $res->message);
		}
		App::$html = 'Write';
		App::View($this->model);
	}
	public function PostWrite(){
		$res = $this->model->SetPostValuesWithFile();
		if(!$res->result){
			URLReplace('-1',$res->message);
		}
		else{
			echo 3;
			/*if(isset($_FILES['img'])){
				require_once _COMMONDIR.'/FileUpload.php';
				$fres_em = FileUpload($_FILES['img'], App::$settingData['POSSIBLE_EXT'], '/board/'.date('ym').'/');

				if(is_string($fres_em)) URLReplace('-1', $fres_em);
				else if(is_array($fres_em)){
					$this->model->SetValue('img', $fres_em['file']);
				}
			}*/

			$error = $this->model->GetErrorMessage();
			if(sizeof($error)){
				URLReplace(App::URLAction().App::GetFollowQuery(), $error[0]);
			}
			echo 4;

			$res = $this->model->DBInsert();
			if($res->result){
				echo 5;
				CM::ContentImageUpdate($this->model->table, array('seq' => $res->id), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');
				echo 6;

				URLReplace(App::URLAction().App::GetFollowQuery());
			}else{
				echo 7;
				URLReplace(App::URLAction().App::GetFollowQuery(), 'ERROR');
			}
		}
	}

	public function PostModify(){
		$res = $this->model->DBGet(to10(App::$id));
		$res = $this->model->SetPostValuesWithFile();
		if(!$res->result){
			URLReplace('-1',$res->message);
		}
		else{
			/*if(isset($_FILES['img'])){
				require_once _COMMONDIR.'/FileUpload.php';
				$fres_em = FileUpload($_FILES['img'], App::$settingData['POSSIBLE_EXT'], '/board/'.date('ym').'/');

				if(is_string($fres_em)) URLReplace('-1', $fres_em);
				else if(is_array($fres_em)){
					if($this->model->GetValue('img')) @unlink(\Paths::DirOfUpload().$this->model->GetValue('img'));
					$this->model->SetValue('img', $fres_em['file']);
				}
			}*/

			$error = $this->model->GetErrorMessage();
			if(sizeof($error)){
				URLReplace(App::URLAction().App::GetFollowQuery(), $error[0]);
			}

			$res = $this->model->DBUpdate();
			if($res->result){
				CM::ContentImageUpdate($this->model->table, array('seq' => to10(App::$id)), array('name' => 'contents', 'contents' => $_POST['contents']), 'modify');
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
