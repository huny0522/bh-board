<?php
namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use Common\ArticleAction;
use Common\MenuHelp;
use \DB as DB;

class Board extends \Controller\Board{
	public function __init(){
		parent::__init();

		App::SetFollowQuery(array('page','stype','keyword','cate','lastSeq','scate', 'dv'));
	}

	/** @param \BH_DB_GetListWithPage $qry */
	protected function _R_GetListQuery(&$qry){
		if(Get('dv') == 'y') $qry->AddWhere('delis = \'y\'');
	}
	/** @param \BH_DB_GetList $qry */
	protected function _R_MoreListQuery(&$qry){
		if(Get('dv') == 'y') $qry->AddWhere('delis = \'y\'');
	}
}