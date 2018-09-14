<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use Common\BH_Category;
use Common\MenuHelp;
use \DB as DB;

class MenuManager extends BH_Category{

	public function __init(){
		App::$Data['NowMenu'] = '004';
		CM::AdminAuth();
		App::$Layout = '_Admin';
	}

	protected function _MenuChangeAfter(){
		MenuHelp::GetInstance()->MenusToFile();
	}

	public function PostGetBidList(){
		$dbGetList = new \BH_DB_GetList();
		if($_POST['type'] == 'board'){
			$dbGetList->table = TABLE_BOARD_MNG;
			$dbGetList->SetKey(array('bid as subject', 'bid'))->SetGroup('bid');
		}
		else if($_POST['type'] == 'content'){
			$dbGetList->table = TABLE_CONTENT;
			$dbGetList->SetKey(array('subject', 'bid'));
		}
		else {
			echo json_encode(array('result' => false, 'message' => 'ERROR #1'));
			exit;
		}

		$res = array();
		while($row = $dbGetList->Get()){
			$res[] = $row;
		}
		echo json_encode($res);
	}

	public function PostGetSubidList(){
		if(Post('type') !== 'board'){
			echo json_encode(array('result' => false, 'message' => 'ERROR #1'));
			exit;
		}

		$qry = DB::GetListQryObj(TABLE_BOARD_MNG)
			->SetKey(array('subject', 'bid', 'subid'))
			->AddWhere('bid = %s', Post('bid'));
		$res = array();
		while($row = $qry->Get()){
			$res[] = $row;
		}
		echo json_encode($res);
	}

	public function PostWrite(){
		if(!EmptyPost('addi_subid')){
			$_POST['addi_subid'] = strtolower(preg_replace('/[^a-zA-Z\_\,]/is', '', Post('addi_subid')));
		}
		parent::PostWrite();
	}
}