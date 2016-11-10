<?php
/**
 * Bang Hun.
 * 16.07.10
 */

class BH_Application{
	public $Controller = '';
	public $Action = '';
	public $ID = '';
	public $SubDir = '';
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

	public function __construct(){
		$this->_Conn = SqlConnection($GLOBALS['_DBInfo']);
		$this->_MainConn = $this->_Conn;
		if(!$this->_Conn){
			echo("ACCESS_DENIED_DB_CONNECTION");
			exit;
		}

	}

	public function __destruct(){
		if($this->_MainConn) mysqli_close($this->_MainConn);
	}

	public function run(){

		$this->Router = new BH_Router();
		$this->Router->router();

		if(!$this->Controller) $this->Controller = _DEFAULT_CONTROLLER;
		if(!$this->Action) $this->Action = 'Index';


		$path = _DIR.'/Controller/'.($this->SubDir ? $this->SubDir.'/' : '').$this->Controller.'.php';

		if(file_exists($path)){
			require $path;
			$controller = $this->Controller.'Controller';

			$action = _POSTIS === true ? 'Post'.$this->Action : $this->Action;

			if(method_exists($controller, $action)){

				$this->CTRL = new $controller();
				if(method_exists($this->CTRL, '__Init')) $this->CTRL->__Init();

				$this->CTRL->{$action}();
			}else{
				if(_DEVELOPERIS === true) echo '메소드가 존재하지 않습니다.(#2)';
				else Redirect(_URL.'/');
			}
		}else{
			if(_DEVELOPERIS === true) echo '파일이 존재하지 않습니다.(#1)';
			else Redirect(_URL.'/');
		}
	}
}