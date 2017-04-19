<?php
/**
 * Bang Hun.
 * 16.07.10
 */
namespace Admin;

class ConfigController extends \BH_Controller{

	public function __Init(){
		$this->_Value['NowMenu'] = '001001';
		$this->_CF->AdminAuth();

		// 항상 따라다닐 URL 쿼리 파라미터를 지정
		$this->SetFollowQuery(array('where', 'keyword','page'));
		$this->Layout = '_Admin';
	}

	public function Index(){
		$this->_Value['NowMenu'] = '001001001';
		$this->_Value['Code'] = 'Default';
		$this->_View();
	}

	public function PostWrite(){
		if(!file_exists( _DATADIR.'/CFG') || !is_dir(_DATADIR.'/CFG')) mkdir(_DATADIR.'/CFG', 0757);
		$path = _DATADIR.'/CFG/'.$_POST['Code'].'.php';
		$txt = '';
		foreach($_POST as $k => $v){
			$txt .= '$GLOBALS[\'_BH_App\']->CFG[\''.addslashes($_POST['Code']).'\'][\''.addslashes($k).'\'] = \''.addslashes($v).'\';'.chr(10);
		}
		file_put_contents($path, '<?php'.chr(10).$txt);
		Redirect($this->URLAction(), '설정되었습니다.');
	}
}