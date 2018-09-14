<?php

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class Home{
	public function __construct(){
	}

	public function __init(){
		if(_MEMBERIS !== true || ($_SESSION['member']['level'] != _SADMIN_LEVEL  && $_SESSION['member']['level'] != _ADMIN_LEVEL)){
			URLReplace(_ADMINURL.'/Login');
		}
		App::$Layout = '_Admin';
		App::$Data['NowMenu'] = '';
	}

	public function Index(){
		App::$Data['freeBoard'] = CM::GetBoardArticle('board', 'free_board', '', 5);
		App::$Data['notice'] = CM::GetBoardArticle('board', 'notice', '', 5);
		App::$Data['user'] = DB::GetListQryObj(TABLE_MEMBER)
			->SetSort('reg_date DESC')
			->SetLimit(10)
			->GetRows();
		App::$Data['loginUser'] = DB::GetListQryObj(TABLE_MEMBER)
			->SetSort('login_date DESC')
			->AddWhere('login_date > %s', '1000-01-01 00:00:00')
			->SetLimit(10)
			->GetRows();
		App::View();
	}

	public function PHPInfo(){
		if($_SESSION['member']['level'] != _SADMIN_LEVEL) return;

		echo phpinfo();
	}
}
