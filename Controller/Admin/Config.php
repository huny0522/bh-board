<?php
/**
 * Bang Hun.
 * 16.07.10
 */
namespace Admin;
use \BH_Application as App;
use \BH as BH;
class ConfigController{

	public function __construct(){
		App::$_Value['NowMenu'] = '001001';
		BH::CF()->AdminAuth();

		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		BH::APP()->SetFollowQuery(array('where', 'keyword','page'));
		BH::APP()->Layout = '_Admin';
	}

	public function Index(){
		App::$_Value['NowMenu'] = '001001001';
		App::$_Value['Code'] = 'Default';
		BH::APP()->_View();
	}

	public function PostWrite(){
		if(!file_exists( _DATADIR.'/CFG') || !is_dir(_DATADIR.'/CFG')) mkdir(_DATADIR.'/CFG', 0757);
		$path = _DATADIR.'/CFG/'.$_POST['Code'].'.php';
		$txt = '';
		foreach($_POST as $k => $v){
			$txt .= '\\BH::APP()->CFG[\''.addslashes($_POST['Code']).'\'][\''.addslashes($k).'\'] = \''.addslashes($v).'\';'.chr(10);
		}
		file_put_contents($path, '<?php'.chr(10).$txt);
		Redirect(BH::APP()->URLAction(), '설정되었습니다.');
	}
}