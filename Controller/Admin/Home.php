<?php

class HomeController extends BH_Controller{
	public function __Init(){
		if(_MEMBERIS !== true || ($_SESSION['member']['level'] != _SADMIN_LEVEL  && $_SESSION['member']['level'] != _ADMIN_LEVEL)){
			Redirect(_ADMINURL.'/Login', _NO_AUTH.' 로그인하여 주세요.');
		}
		$this->Layout = '_Admin';
		$this->_Value['NowMenu'] = '';
	}

	public function Index(){
		$this->_View();
	}
}
