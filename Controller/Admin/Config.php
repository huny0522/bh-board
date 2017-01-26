<?php
/**
 * Bang Hun.
 * 16.07.10
 */

class ConfigController extends BH_Controller{

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
		$var = json_encode($_POST);
		$path = _DIR.'/Common/CFG/'.$_POST['Code'].'.inc';
		file_put_contents($path, $var);
		Redirect($this->URLAction(), '설정되었습니다.');
	}
}