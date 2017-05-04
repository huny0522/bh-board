<?php
namespace Admin;
use \BH_Application as App;
use \BH as BH;
class HomeController{
	public function __construct(){
		if(_MEMBERIS !== true || ($_SESSION['member']['level'] != _SADMIN_LEVEL  && $_SESSION['member']['level'] != _ADMIN_LEVEL)){
			Redirect(_ADMINURL.'/Login', _NO_AUTH.' 로그인하여 주세요.');
		}
		BH::APP()->Layout = '_Admin';
		App::$_Value['NowMenu'] = '';
	}

	public function Index(){
		BH::APP()->_View();
	}
}
