<?php
/**
 * Bang Hun.
 * 16.07.10
 */

class BH_Application{
	public $Controller = '';
	public $Action = '';
	public $ID = '';
	public $NativeDir = '';
	public $BaseDir = '';
	public $TID = '';
	public $CtrlUrl = '';

	/**
	 * @var BH_Controller
	 */
	public $CTRL = null;

	/**
	 * @var BH_Router
	 */
	public $Router = null;

	public $_Conn = null;
	public $_MainConn = null;

	public $InstallIs = true;
	public $CFG = array();

	public function __construct(){
		$this->_Conn = SqlConnection($GLOBALS['_DBInfo']);
		$this->_MainConn = $this->_Conn;
		if(!$this->_Conn){
			echo('ACCESS_DENIED_DB_CONNECTION');
			exit;
		}
		mysqli_set_charset($this->_Conn,'utf8');
	}

	public function __destruct(){
		if($this->_MainConn) mysqli_close($this->_MainConn);
	}

	public function run(){
		if(_DEVELOPERIS === true) $this->InstallIs = SqlTableExists(TABLE_MEMBER);

		$this->Router = new \BH_Router();
		$this->Router->router();

		if(!$this->Controller) $this->Controller = _DEFAULT_CONTROLLER;
		if(!strlen($this->Action)) $this->Action = 'Index';
		else if(strtolower(substr($this->Action, 0, 4)) == 'post'){
			$this->Action = substr($this->Action, (strlen($this->Action) - 4)*(-1));
		}

		if(substr($this->Action, 0, 1) == '_') $this->Action = preg_replace('/_+(.*+)/', '$1', $this->Action);

		$path = _DIR.'/Controller/'.($this->NativeDir ? $this->NativeDir.'/' : '').$this->Controller.'.php';

		if(file_exists($path)){
			require $path;
			$controller = $this->NativeDir.'\\'.$this->Controller.'Controller';
			if (!class_exists($controller)) $controller = $this->Controller.'Controller';
			if (!class_exists($controller)){
				if(_DEVELOPERIS === true) echo '클래스('.$controller.')가 존재하지 않습니다.';
				exit;
			}

			$action = _POSTIS === true ? 'Post'.$this->Action : $this->Action;

			if(method_exists($controller, $action) && is_callable(array($controller, $action))){

				$this->CTRL = new $controller();
				if(method_exists($this->CTRL, '__Init')) $this->CTRL->__Init();

				$this->CTRL->{$action}();
			}else{
				if(_DEVELOPERIS === true) echo '메소드가 존재하지 않습니다.(#2)';
				else Redirect(_URL.'/');
			}
		}else{
			if(_DEVELOPERIS === true){
				require _COMMONDIR.'/Create.html';
			}
			else Redirect(_URL.'/');
		}
	}
}