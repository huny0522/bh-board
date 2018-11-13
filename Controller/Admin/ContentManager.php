<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class ContentManager{

	/**
	 * @var \ContentModel;
	 */
	public $model;

	public function __construct(){
		$this->model = App::InitModel('Content');

		$dbGetList = new \BH_DB_GetList($this->model->table);
		$dbGetList->SetKey('DISTINCT category');
		App::$data['category'] = array();
		if(isset(App::$settingData['contentCategory'])) foreach(App::$settingData['contentCategory'] as $v){
			App::$data['category'][$v] = $v;
		}

		while($row = $dbGetList->Get()) App::$data['category'][$row['category']] = $row['category'];
	}

	public function __init(){
		App::$data['NowMenu'] = '003';
		CM::AdminAuth();

		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		App::SetFollowQuery(array('category', 'page', 'keyword'));
		App::$layout = '_Admin';

		$AdminAuth = explode(',', CM::GetMember('admin_auth'));
		App::$data['menuAuth'] = (in_array('004', $AdminAuth) || $_SESSION['member']['level'] == _SADMIN_LEVEL);
	}

	public function Index(){
		// 리스트를 불러온다.
		$qry = DB::GetListPageQryObj($this->model->table.' A LEFT JOIN '.TABLE_MENU.' B ON A.bid = B.bid AND B.type=\'content\'')
			->SetPageUrl(App::URLAction('').App::GetFollowQuery('page'))

			->SetPage(Get('page'))
			->SetArticleCount(20)
			->SetSort('A.reg_date DESC')
			->SetGroup('A.bid')
			->SetKey('A.*, group_concat(B.title SEPARATOR \', \') as title');

		if($category = trim(Get('category'))) $qry->AddWhere('`A`.`category` = %s', $category);
		if($keyword = trim(Get('keyword'))) $qry->AddWhere('INSTR(`A`.`subject`, %s)', $keyword);

		$qry->Run();

		App::View($this->model, $qry);
	}
	public function View(){
		$res = $this->model->DBGet($_GET['bid']);

		$dbGet = new \BH_DB_GetList(TABLE_MENU);
		$dbGet->AddWhere('type=\'content\'');
		$dbGet->AddWhere('bid = %s', $this->model->GetValue('bid'));
		App::$data['selectedMenu'] = $dbGet->GetRows();

		if(!$res->result){
			URLReplace('-1', $res->message);
		}

		App::View($this->model);
	}
	public function Write(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		App::$data['menu'] = $dbGetList->GetRows();

		$dbGetList = new \BH_DB_GetList($this->model->table);
		$dbGetList->SetKey('DISTINCT layout');
		App::$data['layout'] = $dbGetList->GetRows();

		App::View($this->model);
	}
	public function Modify(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		App::$data['menu'] = $dbGetList->GetRows();

		$dbGetList = new \BH_DB_GetList($this->model->table);
		$dbGetList->SetKey('DISTINCT layout');
		App::$data['layout'] = $dbGetList->GetRows();

		$res = $this->model->DBGet($_GET['bid']);
		$dbGet = new \BH_DB_GetList(TABLE_MENU);
		$dbGet->AddWhere('type=\'content\'');
		$dbGet->AddWhere('bid = %s', $this->model->GetValue('bid'));
		App::$data['selectedMenu'] = $dbGet->GetRows();

		if(!$res->result){
			URLReplace('-1', $res->message);
		}
		App::$html = 'Write';
		App::View($this->model);
	}
	public function PostWrite(){
		$_POST['bid'] = strtolower(Post('bid'));
		$temp = preg_replace('/[^a-z0-9\_]/', '', Post('bid'));
		if($temp !== Post('bid')){
			$this->model->SetPostValues();
			$this->_ErrorView('게시판 ID는 영문 소문자와 숫자, 언더바(_)만 입력하여 주세요.');
		}

		$_POST['bid'] = $temp;

		$res = $this->model->SetPostValues();
		if(!$res->result){
			URLReplace('-1',$res->message);
		}
		else{
			$this->model->SetValue('reg_date',date('Y-m-d H:i:s'));
			$res = $this->model->DBInsert();
			if($res->result){
				CM::MenuConnect($this->model->GetValue('bid'), 'content');
				URLReplace(App::URLAction());
			}else{
				URLReplace('-1', '등록에 실패했습니다.');
			}
		}
	}
	public function PostModify(){
		$res = $this->model->DBGet($_POST['bid']);
		if(!$res->result){
			URLReplace('-1',$res->message);
		}

		$res = $this->model->SetPostValues();
		if(!$res->result){
			URLReplace('-1',$res->message);
		}
		else{
			$res = $this->model->DBUpdate();
			if($res->result){
				CM::MenuConnect($this->model->GetValue('bid'), 'content');
				$url = App::URLAction('Modify').'?bid='.$_POST['bid'].App::GetFollowQuery();
				URLReplace($url, '수정완료');
			}else{
				URLReplace('-1', '수정에 실패했습니다.');
			}
		}
	}

	public function GetSubMenu(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = %d', strlen(App::$id) + _CATEGORY_LENGTH);
		$dbGetList->AddWhere('LEFT(category, %d) = %s', strlen(App::$id), App::$id);
		JSON(true, '', $dbGetList->GetRows());
	}

	public function PostDelete(){
		$res = $this->model->DBDelete($_POST['bid']);
		if($res->result) URLReplace(App::URLAction('').App::GetFollowQuery());
		else URLReplace('-1', '삭제에 실패했습니다.');
	}

	public function GetSkinFiles(){
		$this->GetFiles(_SKINDIR . '/Contents/');
	}

	public function GetLayoutFiles(){
		$this->GetFiles(_SKINDIR . '/Layout/');
	}

	public function GetFiles($parentPath){
		$list = array('dir' => array(), 'file' => array());
		$path = $parentPath . Get('path');
		if(is_dir($path) && $dh = opendir($path)){
			while(($file = readdir($dh)) !== false){
				if($file != '.' && $file != '..'){
					$dest_path = $path . '/' . $file;
					if(is_dir($dest_path)) $list['dir'][] = $file;
					else $list['file'][] = substr($file, -5) === '.html' ? substr($file, 0, -5) : $file;
				}
			}
			closedir($dh);
		}
		JSON(true, '', $list);
	}
}