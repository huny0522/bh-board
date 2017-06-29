<?php

namespace BH\Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class Home{
	public function __construct(){
	}

	public function __init(){
		if(_MEMBERIS !== true || ($_SESSION['member']['level'] != _SADMIN_LEVEL  && $_SESSION['member']['level'] != _ADMIN_LEVEL)){
			URLReplace(_ADMINURL.'/Login', _MSG_NO_AUTH.' 로그인하여 주세요.');
		}
		App::$Layout = '_Admin';
		App::$Data['NowMenu'] = '';
	}

	public function Index(){
		App::View($this);
	}
}
