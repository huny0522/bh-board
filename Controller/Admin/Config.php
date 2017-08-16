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
	public function __construct(){
	}

	public function __init(){
		App::$Data['NowMenu'] = '001001';
		CM::AdminAuth();

		App::SetFollowQuery(array('where', 'keyword','page'));
		App::$Layout = '_Admin';
	}

	public function Index(){
		App::$Data['NowMenu'] = '001001001';
		App::$Data['Code'] = 'Default';
		App::View($this);
	}

	public function PostWrite(){
		if(!file_exists( _DATADIR.'/CFG') || !is_dir(_DATADIR.'/CFG')) mkdir(_DATADIR.'/CFG', 0755);
		foreach($_POST as $k => $v){
			App::$CFG[$_POST['Code']][$k] = $v;
		}

		$path = _DATADIR.'/CFG/'.$_POST['Code'].'.php';
		$txt = '<?php \BH_Application::$CFG[\''.$_POST['Code'].'\'] = '.var_export(App::$CFG[$_POST['Code']], true).';';
		file_put_contents($path, $txt);
		URLReplace(App::URLAction(), '설정되었습니다.');
	}
}