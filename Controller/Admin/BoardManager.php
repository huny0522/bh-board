<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class BoardManager
{

	/**
	 * @var \BoardManagerModel
	 */
	public $model = null;

	public function __construct(){
		$this->model = App::InitModel('BoardManager');

		$dbGetList = new \BH_DB_GetList($this->model->table);
		$dbGetList->SetKey('DISTINCT group_name');
		App::$Data['group_name'] = array();

		while($row = $dbGetList->Get()) App::$Data['group_name'][$row['group_name']] = $row['group_name'];
	}

	public function __init(){
		App::$Data['NowMenu'] = '002001';
		if(App::$SettingData['GetUrl'][2] == 'Board' || !EmptyGet('gn')) App::$Data['NowMenu'] = '002';
		CM::AdminAuth();

		$AdminAuth = explode(',', CM::GetMember('admin_auth'));
		App::$Data['menuAuth'] = (in_array('004', $AdminAuth) || $_SESSION['member']['level'] == _SADMIN_LEVEL);

		App::SetFollowQuery(array('where', 'keyword','page', 'gn'));
		App::$Layout = '_Admin';

		$dbGetList = new \BH_DB_GetList($this->model->table);
		$dbGetList->SetKey('DISTINCT bid');
		App::$Data['bids'] = array();

		while($row = $dbGetList->Get()) App::$Data['bids'][$row['bid']] = $row['bid'];
	}

	public function Index(){

		// 리스트를 불러온다.
		$dbGetList = DB::GetListPageQryObj($this->model->table.' A LEFT JOIN '.TABLE_MENU.' B ON A.bid = B.bid AND B.type=\'board\'')
			->SetPage(Get('page'))
			->SetPageUrl(App::URLAction('').App::GetFollowQuery('page'))
			->SetArticleCount(20)
			->SetGroup('A.bid, A.subid')
			->SetSort('A.reg_date DESC')
			->SetKey('A.*, group_concat(B.title SEPARATOR \', \') as title');

		if(!EmptyGet('gn')) $dbGetList->AddWhere('A.group_name = %s', Get('gn'));
		if(!EmptyGet('keyword')) $dbGetList->AddWhere('INSTR(A.subject, %s)', Get('keyword'));

		$dbGetList->Run();

		App::View($this->model, $dbGetList);
	}

	public function Write(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		App::$Data['menu'] = $dbGetList->GetRows();

		App::$Data['subCategoryData'] = array();

		App::View('Write', $this->model);
	}

	public function CategoryChange(){
		$res = $this->model->DBGet(App::$ID, App::$ID2);

		if(!$res->result){
			URLReplace('-1', $res->message);
		}

		App::$Data['category'] = explode(',', $this->model->_category->txt());

		if(!strlen($this->model->_category->txt()) || !sizeof(App::$Data['category'])) URLRedirect(-1, '분류 설정이 되어있지 않습니다.');

		App::$Data['subCategoryData'] = $this->model->GetSubCategory();

		App::View($this->model);
	}

	public function Copy(){
		$this->Modify();
	}

	public function Modify(){
		$res = $this->model->DBGet(App::$ID, App::$ID2);

		if(!$res->result){
			URLReplace('-1', $res->message);
		}

		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH);
		App::$Data['menu'] = $dbGetList->GetRows();

		$dbGet = new \BH_DB_GetList(TABLE_MENU);
		$dbGet->AddWhere('type=\'board\'');
		$dbGet->AddWhere('bid = %s', $this->model->GetValue('bid'));
		$dbGet->AddWhere('subid = %s', $this->model->GetValue('subid'));
		App::$Data['selectedMenu'] = $dbGet->GetRows();

		App::$Data['subCategoryData'] = $this->model->GetSubCategory();

		App::View('Write', $this->model);
	}

	public function PostWrite(){
		$_POST['bid'] = strtolower(Post('bid'));
		$temp = preg_replace('/[^a-z0-9\_]/', '', Post('bid'));
		if($temp !== Post('bid')){
			$this->model->SetPostValues();
			$this->_ErrorView('게시판 ID는 영문 소문자와 숫자, 언더바(_)만 입력하여 주세요.');
		}

		$_POST['bid'] = $temp;

		$_POST['subid'] = strtolower(Post('subid'));
		$temp = preg_replace('/[^a-z0-9\_]/', '', Post('subid'));
		if($temp !== Post('subid')){
			$this->model->SetPostValues();
			$this->_ErrorView('게시판 서브ID는 영문 소문자와 숫자, 언더바(_)만 입력하여 주세요.');
		}

		$_POST['subid'] = $temp;

		$exists = DB::GetQryObj($this->model->table)
			->AddWhere('bid = %s', Post('bid'))
			->AddWhere('subid = %s', Post('subid'))
			->SetKey('bid, subid')
			->Get();
		if($exists){
			$this->model->SetPostValues();
			$this->_ErrorView('입력하신 게시판 아이디와 서브아이디는 이미 존재합니다.');
		}

		$res = $this->model->SetPostValues();
		if(!$res->result) $this->_ErrorView($res->message);

		$this->model->_sub_category->SetValue($this->_PostSubCategoryToJson());

		$error = $this->model->GetErrorMessage();
		if(sizeof($error)) $this->_ErrorView($error[0]);

		$res = $this->model->DBInsert();

		if(!$res->result) $this->_ErrorView($res->message);

		if(!DB::SQL()->TableExists(TABLE_FIRST.'bbs_'.$this->model->GetValue('bid'))){
			$r1 = $this->model->CreateTableBoard(TABLE_FIRST.'bbs_'.$this->model->GetValue('bid'));
			if(!$r1) $this->_ErrorView('게시판 DB 생성오류');

			$r2 = $this->model->CreateTableReply(TABLE_FIRST.'bbs_'.$this->model->GetValue('bid').'_reply');
			if(!$r2) $this->_ErrorView('댓글 DB 생성오류');

			$r3 = $this->model->CreateTableImg(TABLE_FIRST.'bbs_'.$this->model->GetValue('bid').'_images');
			if(!$r3) $this->_ErrorView('이미지 DB 생성오류');

			$r4 = $this->model->CreateTableAction(TABLE_FIRST.'bbs_'.$this->model->GetValue('bid').'_action');
			if(!$r4) $this->_ErrorView('액션 DB 생성오류');
		}

		CM::MenuConnect($this->model->GetValue('bid'), 'board', $this->model->GetValue('subid'));

		URLReplace(App::URLAction().App::GetFollowQuery());
	}

	public function PostModify(){
		$res = $this->model->DBGet(Post('bid'), Post('subid'));
		if(!$res->result) $this->_ErrorView($res->message ? $res->message : '게시판 불러오기 오류', 'Write');

		$res = $this->model->SetPostValues();
		if(!$res->result) $this->_ErrorView($res->message ? $res->message : '게시판 불러오기 오류', 'Write');

		$this->model->_sub_category->SetValue($this->_PostSubCategoryToJson());

		$res = $this->model->DBUpdate();

		if($res->result){
			CM::MenuConnect($this->model->GetValue('bid'), 'board', $this->model->GetValue('subid'));
			$url = App::URLAction('Modify').'?bid='.Post('bid').App::GetFollowQuery('&');
			URLReplace($url, '수정완료');
		}
		else $this->_ErrorView($res->message ? $res->message : 'Error', 'Write');
	}

	public function PostDelete(){
		if(strlen(Post('bid'))){
			if(isset(App::$SettingData['FixedBoardId']) && is_array(App::$SettingData['FixedBoardId']) && in_array(Post('bid'), App::$SettingData['FixedBoardId'])) URLRedirect('-1', '해당 게시판은 삭제가 불가능합니다.');

			$res = $this->model->DBDelete(Post('bid'), Post('subid'));
			$bm = DB::GetQryObj(TABLE_BOARD_MNG)
				->AddWhere('bid = %s', Post('bid'))
				->Get();

			if($res->result){
				if(!$bm){
					$board_nm = TABLE_FIRST.'bbs_'.Post('bid');
					@\DB::SQL()->Query("DROP TABLE `{$board_nm}`");
					@\DB::SQL()->Query("DROP TABLE `{$board_nm}_reply`");
					@\DB::SQL()->Query("DROP TABLE `{$board_nm}_images`");
					@\DB::SQL()->Query("DROP TABLE `{$board_nm}_action`");
				}

				$dirName = Post('bid') . (EmptyPost('subid') ? '' : '-' . Post('subid'));

				delTree(_UPLOAD_DIR . '/board/' . $dirName);
				delTree(_UPLOAD_DIR . '/boardimage/' . $dirName);
				delTree(_UPLOAD_DIR . '/reply/' . $dirName);

				URLReplace(App::URLAction('').App::GetFollowQuery(), '삭제되었습니다.');
			}else{
				URLReplace('-1', $res->message);
			}
		}
	}

	public function GetSubMenu(){
		$dbGetList = new \BH_DB_GetList(TABLE_MENU);
		$dbGetList->AddWhere('LENGTH(category) = %d', (strlen(App::$ID) + _CATEGORY_LENGTH));
		$dbGetList->AddWhere('LEFT(category, %d) = %s', strlen(App::$ID), App::$ID);
		JSON(true, '', $dbGetList->GetRows());
	}

	public function _ErrorView($error, $page = null){
		App::$Data['error'] = $error;

		App::$Data['subCategoryData'] = $this->model->GetSubCategory();
		App::View($page, $this->model);
		exit;
	}

	public function PostCategoryChange(){
		$before = Post('before_cate');
		$after = Post('after_cate');
		$before2 = Post('before_sub_cate');
		$after2 = Post('after_sub_cate');
		$bid = Post('bid');
		if(!strlen($bid)) JSON(false, _MSG_WRONG_CONNECTED);
		$qry = DB::UpdateQryObj(TABLE_FIRST . 'bbs_' . $bid);
		$c = false;
		$qry->AddWhere('category = %s', $before);
		if($after !== $before && strlen($after)){
			$qry->SetDataStr('category', $after);
			$c = true;
		}

		if(strlen($after2)){
			$qry->AddWhere('sub_category = %s', $before2);
			if($after2 !== $before2){
				$qry->SetDataStr('sub_category', $after2);
				$c = true;
			}
		}
		if(!$c) JSON(true);
		$res = $qry->Run();
		if($res->result) JSON(true);
		else JSON(false, $res->message ? $res->message : 'DB 변경 오류');
	}

	private function _PostSubCategoryToJson(){
		$names = Post('sub_category_name');
		$data = Post('sub_category_data');
		if(!is_array($names) || !sizeof($names) || !is_array($data) || !sizeof($data) || is_array($data) != sizeof($data)) return json_encode(array());

		$res = array();
		foreach($names as $k => $v){
			$v = trim($v);
			$res[] = array(
				'category' => $v,
				'sub_category' => $this->_ExplodeNotEmpty(',', $data[$k])
			);
		}

		return json_encode($res);
	}

	private function _ExplodeNotEmpty($delimiter, $str){
		$res = array();
		$temp = explode($delimiter, $str);
		foreach($temp as $v){
			$v = trim($v);
			if(strlen($v)) $res[] = $v;
		}
		return $res;
	}

}