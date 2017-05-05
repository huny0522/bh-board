<?php
/**
 * Bang Hun.
 * 16.07.10
 */
namespace Admin;
use \BH_Application as App;
use \BH_Common as CF;

class ConfigController{
	public function __construct(){
	}

	public function __init(){
		App::$_Value['NowMenu'] = '001001';
		CF::Get()->AdminAuth();

		App::$Instance->SetFollowQuery(array('where', 'keyword','page'));
		App::$Instance->Layout = '_Admin';
	}

	public function Index(){
		App::$_Value['NowMenu'] = '001001001';
		App::$_Value['Code'] = 'Default';
		App::$Instance->_View($this);
	}

	public function PostWrite(){
		if(!file_exists( _DATADIR.'/CFG') || !is_dir(_DATADIR.'/CFG')) mkdir(_DATADIR.'/CFG', 0755);
		$path = _DATADIR.'/CFG/'.$_POST['Code'].'.php';
		$txt = '';
		foreach($_POST as $k => $v){
			$txt .= '\BH_Application::$Instance->CFG[\''.addslashes($_POST['Code']).'\'][\''.addslashes($k).'\'] = \''.addslashes($v).'\';'.chr(10);
		}
		file_put_contents($path, '<?php'.chr(10).$txt);
		Redirect(App::$Instance->URLAction(), '설정되었습니다.');
	}
}