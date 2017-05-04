<?php
/**
 * Bang Hun.
 * 16.07.10
 */
namespace Admin;
use \BH_Application as App;
use \BH as BH;
require _COMMONDIR.'/BH_Category.class.php';
class MenuManagerController extends \BH_Category{

	/**
	 * @var MenuModel
	 */
	public $model;

	public function __construct(){
		App::$_Value['NowMenu'] = '004';
		BH::CF()->AdminAuth();
		BH::APP()->Layout = '_Admin';
		parent::__construct();
	}

	public function PostGetBidList(){
		$dbGetList = BH::DBList();
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