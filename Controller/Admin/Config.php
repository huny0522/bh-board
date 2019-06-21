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
		App::$data['NowMenu'] = '001001';
		CM::AdminAuth();

		App::SetFollowQuery(array('where', 'keyword','page'));
		App::$layout = '_Admin';
		App::$data['Code'] = App::$action;
		if(App::$action === 'SuperSet' && $_SESSION['member']['level'] < _SADMIN_LEVEL) URLRedirect(-1, App::$lang['MSG_WRONG_CONNECTED']);
	}

	public function Index(){
		App::$data['NowMenu'] = '001001';
		App::$data['Code'] = 'Default';
		App::View();
	}

	public function PostWrite(){
		$class = '\\Config'.Post('Code');
		if(EmptyPost('Code') || !class_exists($class)) URLRedirect(-1,  '설정이 존재하지 않습니다.');
		$cfg = $class::GetInstance();

		$arr = get_object_vars($cfg);
		foreach($arr as $k=> $v){
			if($cfg->{$k}->type == \HTMLType::CHECKBOX) $cfg->{$k}->value = '';
		}

		$res = $cfg->DataWrite($_POST, $_FILES);
		if(!$res->result) URLReplace(-1, $res->message);

		if(Get('redirect_url')){
			URLReplace(Get('redirect_url'), '설정되었습니다.');
		}
		else URLReplace(App::URLAction(Post('Code') != 'Default' ? Post('Code') : ''), '설정되었습니다.');
	}

	public function Content(){
		App::$data['NowMenu'] = '001050';
		reset(App::$data['menu']);
		App::$data['Code'] = (!App::$id) ? 'EmailCollector' : App::$id;

		$class = '\\Config' . App::$data['Code'];
		if(!class_exists($class)) URLRedirect(-1, App::$lang['MSG_WRONG_CONNECTED']);
		App::$data['class'] = $class::GetInstance();


		App::View();
	}
}