<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class Config{
	private $fileNames;

	public function __construct(){
		if(_POSTIS === true && is_array(Post('file_field')) && Post('Code')){
			$this->fileNames = array(Post('Code') => Post('file_field'));
		}
		else $this->fileNames = array();
	}

	public function __init(){
		App::$Data['NowMenu'] = '001001';
		CM::AdminAuth();

		App::SetFollowQuery(array('where', 'keyword','page'));
		App::$Layout = '_Admin';
		App::$Data['Code'] = App::$Action;
		if(App::$Action === 'SuperSet' && $_SESSION['member']['level'] < _SADMIN_LEVEL) URLRedirect(-1, _MSG_WRONG_CONNECTED);
	}

	public function Index(){
		App::$Data['NowMenu'] = '001001';
		App::$Data['Code'] = 'Default';
		App::View();
	}

	public function PostWrite(){
		$class = '\\Config'.Post('Code');
		if(EmptyPost('Code') || !class_exists($class)) URLRedirect(-1,  '설정이 존재하지 않습니다.');
		$cfg = $class::GetInstance();
		$res = $cfg->DataWrite($_POST, $_FILES);
		if(!$res->result) URLReplace(-1, $res->message);

		if(Get('redirect_url')){
			URLReplace(Get('redirect_url'), '설정되었습니다.');
		}
		else URLReplace(App::URLAction(Post('Code') != 'Default' ? Post('Code') : ''), '설정되었습니다.');
	}

	public function Content(){
		App::$Data['NowMenu'] = '001050';
		reset(App::$Data['menu']);
		App::$Data['Code'] = (!App::$ID) ? 'TermsText' : App::$ID;

		$class = '\\Config' . App::$Data['Code'];
		if(!class_exists($class)) URLRedirect(-1, _MSG_WRONG_CONNECTED);
		App::$Data['class'] = $class::GetInstance();


		App::View();
	}
}