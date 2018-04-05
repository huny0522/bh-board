<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use Common\BH_Category;
use \DB as DB;

class MenuManager extends BH_Category{

	/**
	 * @var \MenuModel
	 */
	public $model;

	public function __construct(){
	}

	public function __init(){
		App::$Data['NowMenu'] = '004';
		CM::AdminAuth();
		App::$Layout = '_Admin';
		parent::__init();
	}

	public function PostGetBidList(){
		$dbGetList = new \BH_DB_GetList();
		if($_POST['type'] == 'board') $dbGetList->table = TABLE_BOARD_MNG;
		else if($_POST['type'] == 'content') $dbGetList->table = TABLE_CONTENT;
		else {
			echo json_encode(array('result' => false, 'message' => 'ERROR #1'));
			exit;
		}

		$dbGetList->SetKey(array('subject', 'bid'));
		$res = array();
		while($row = $dbGetList->Get()){
			$res[] = $row;
		}
		echo json_encode($res);
	}
}