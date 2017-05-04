<?php
/**
 * Bang Hun.
 * 16.07.10
 */

class BH_Application{
	/** @var  self */
	private static $Instance;
	/** @var BH_Controller */
	private $ControllerInstance = null;
	/** @var BH_Router */
	private $RouterInstance = null;

	public $ControllerName = '';
	public $Action = '';
	public $ID = '';
	public $NativeDir = '';
	public $BaseDir = '';
	public $TID = '';
	public $CtrlUrl = '';

	public static $IMAGE_EXT = array('jpg','jpeg','png','gif','bmp');
	public static $POSSIBLE_EXT = array('jpg','jpeg','png','gif','bmp','zip','7z','gz','xz','tar',
		'xls', 'xlsx', 'ppt', 'doc', 'hwp', 'pdf', 'docx', 'pptx',
		'avi', 'mov', 'mkv', 'mpg', 'mpeg', 'wmv','asf','asx', 'flv', 'm4v', 'mp4');

	public $InstallIs = true;
	public $CFG = array();

	private function __construct(){
	}

	public static function &GetInstance(){
		if(!isset(self::$Instance)) self::$Instance = new self();
		return self::$Instance;
	}

	public static function &This(){
		return self::$Instance;
	}

	public static function &CTRL(){
		return self::$Instance->ControllerInstance;
	}

	public static function &Router(){
		return self::$Instance->RouterInstance;
	}

	public function run(){
		if(_DEVELOPERIS === true) $this->InstallIs = \DB::SQL()->TableExists(TABLE_MEMBER);

		$this->RouterInstance = new \BH_Router();
		$this->RouterInstance->router();

		if(!$this->ControllerName) $this->ControllerName = _DEFAULT_CONTROLLER;
		if(!strlen($this->Action)) $this->Action = 'Index';
		else if(strtolower(substr($this->Action, 0, 4)) == 'post') $this->Action = preg_replace('/^(Post)+(.*)/i', '$2', $this->Action);

		if(substr($this->Action, 0, 1) == '_') $this->Action = preg_replace('/_+(.*+)/', '$1', $this->Action);

		if(substr($this->Action, 0, 1) == '~'){
			$this->ID = substr($this->Action, 1);
			$this->Action = '_DirectView';
		}

		$path = _DIR.'/Controller/'.($this->NativeDir ? $this->NativeDir.'/' : '').$this->ControllerName.'.php';

		if(file_exists($path)){
			require $path;
			$controller = $this->NativeDir.'\\'.$this->ControllerName.'Controller';
			if (!class_exists($controller)) $controller = $this->ControllerName.'Controller';
			if (!class_exists($controller)){
				if(_DEVELOPERIS === true) echo '클래스('.$controller.')가 존재하지 않습니다.';
				exit;
			}

			$action = _POSTIS === true ? 'Post'.$this->Action : $this->Action;

			if(method_exists($controller, $action) && is_callable(array($controller, $action))){

				$this->ControllerInstance = new $controller();
				$this->ControllerInstance->Layout = $this->RouterInstance->Layout;
				if(_AJAXIS === true) unset($this->ControllerInstance->Layout);
				if(method_exists($this->ControllerInstance, '__Init')) $this->ControllerInstance->__Init();

				$this->ControllerInstance->{$action}();
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