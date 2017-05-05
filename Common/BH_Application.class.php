<?php
/**
 * Bang Hun.
 * 16.07.10
 */
class BH_Application{
	/** @var  self */
	public static $Instance;
	public static $ControllerInstance = null;
	/** @var BH_Router */
	public static $RouterInstance = null;
	private $RunIs = false;

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

	// BH_Controller
	public $Layout = '';
	public $Html;
	public $Title;

	private $CSS = array();
	private $JS = array();
	private $FollowQuery = array();

	public static $_Value = array();

	private function __construct(){
	}

	public static function &Get(){
		if(!isset(self::$Instance)) self::$Instance = new self();
		return self::$Instance;
	}

	public function run(){
		if($this->RunIs) return;
		$this->RunIs = true;
		if(_DEVELOPERIS === true) $this->InstallIs = \DB::SQL()->TableExists(TABLE_MEMBER);

		$this::$RouterInstance = new \BH_Router();
		$this::$RouterInstance->router();

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
				$this::$ControllerInstance = new $controller();
				$this->Layout = $this::$RouterInstance->Layout;
				if(_AJAXIS === true) unset($this->Layout);
				if(method_exists($this::$ControllerInstance, '__Init')) $this::$ControllerInstance->__Init();
				$this::$ControllerInstance->{$action}();
			}else{
				if(_DEVELOPERIS === true) echo '메소드가 존재하지 않습니다.(#2)';
				else Redirect(_URL.'/');
			}
		}else{
			if(_DEVELOPERIS === true && _SHOW_CREATE_GUIDE === true) require _COMMONDIR.'/Create.html';
			else Redirect(_URL.'/');
		}
	}

	/**
	 *  항상 따라다니는 URL을 지정
	 * @param array $ar
	 */
	public function SetFollowQuery($ar){
		if(!is_array($ar)) $ar = func_get_args();
		foreach($ar as $v){
			if(isset($_GET[$v]) && !empty($_GET[$v])) $this->FollowQuery[$v] = $_GET[$v];
		}
	}

	/**
	 * 항상 따라다니는 URL을 출력
	 * @param string $ar 제외할 쿼리 파라미터
	 * @param string $begin 쿼리 시작(& 또는 ?)
	 *
	 * @return string
	 */
	public function GetFollowQuery($ar = '', $begin = '?'){
		$ar = trim($ar);
		$fq = $this->FollowQuery;
		if($ar){
			if(is_string($ar)) $ar = explode(',', $ar);

			if(is_array($ar) && sizeof($ar)){
				foreach($ar as $v){
					unset($fq[trim($v)]);
				}
			}
		}

		$queryparam = '';
		foreach($fq as $k => $v){
			if(is_array($v)){
				foreach($v as $v2){
					$queryparam .= ($queryparam ? '&' : $begin ).$k.'[]='.$v2;
				}
			}
			else $queryparam .= ($queryparam ? '&' : $begin ).$k.'='.$v;
		}
		return $queryparam;
	}

	/**
	 * 항상 따라다니는 URL을 input hidden 출력
	 * @param string $ar 제외할 쿼리 파라미터
	 * @return string
	 */
	public function GetFollowQueryInput($ar = ''){
		$ar = trim($ar);
		$fq = $this->FollowQuery;
		if($ar){
			if(is_string($ar)) $ar = explode(',', $ar);

			if(is_array($ar) && sizeof($ar)){
				foreach($ar as $v){
					unset($fq[trim($v)]);
				}
			}
		}

		$queryparam = '';
		foreach($fq as $k => $v){
			$queryparam .= '<input type="hidden" name="'.GetDBText($k).'" value="'.GetDBText($v).'">';
		}
		return $queryparam;
	}

	/**
	 * html : $this->Html 를 지정하면 그 파일을 찾고 아니라면 액션명의 파일을 찾는다.
	 * layout : /Layout 디렉토리에서 $this->Layout 의 파일을 찾아 레이아웃을 생성
	 * @param $Model mixed
	 * @param $Data mixed
	 */
	public function _View(&$Ctrl, $Model = NULL, $Data = NULL){
		$Router = &$this::$RouterInstance;
		$viewAction = isset($Ctrl->Html) && strlen($Ctrl->Html) ? $Ctrl->Html : ($this->Html ? $this->Html : $this->Action);
		if(!$viewAction) $viewAction = 'Index';

		$html = substr($viewAction, 0, 1) == '/' ? $viewAction :
			($this->NativeDir ? '/'.$this->NativeDir : '').'/'.$this->ControllerName.'/'.$viewAction;
		if(substr($html, -5) != '.html') $html .= '.html';

		if(_DEVELOPERIS === true && _CREATE_HTML_ALL !== true) ReplaceHTMLFile(_SKINDIR.$html, _HTMLDIR.$html);

		ob_start();
		if(file_exists(_HTMLDIR.$html)) require _HTMLDIR . $html;
		else{
			if(_DEVELOPERIS !== true) echo 'ERROR : NOT EXISTS TEMPLATE';
			else echo 'ERROR : NOT EXISTS TEMPLATE : '._HTMLDIR.$html;
		}
		$_BODY = ob_get_clean();

		if(isset($this->Layout)){
			$layout = '/Layout/'.($this->Layout ? $this->Layout.'.html' :  _DEFAULT_LAYOUT.'.html');
			if(_DEVELOPERIS === true && _CREATE_HTML_ALL !== true) ReplaceHTMLFile(_SKINDIR.$layout, _HTMLDIR.$layout);
			if($layout && file_exists(_HTMLDIR.$layout)) require _HTMLDIR.$layout;
		}
		echo $_BODY;
	}

	public function _GetView($Ctrl, $Model = NULL, $Data = NULL){
		$Router = &$this::$RouterInstance;
		$viewAction = isset($Ctrl->Html) && strlen($Ctrl->Html) ? $Ctrl->Html : ($this->Html ? $this->Html : $this->Action);
		if(!$viewAction) $viewAction = 'Index';

		$html = substr($viewAction, 0, 1) == '/' ? $viewAction :
			($this->NativeDir ? '/'.$this->NativeDir : '').'/'.$this->ControllerName.'/'.(substr($viewAction, -5) == '.html' ? $viewAction : $viewAction.'.html');

		if(_DEVELOPERIS === true && _CREATE_HTML_ALL !== true) ReplaceHTMLFile(_SKINDIR.$html, _HTMLDIR.$html);

		ob_start();
		if(file_exists(_HTMLDIR.$html)) require _HTMLDIR . $html;
		else echo 'ERROR : NOT EXISTS TEMPLATE : '.$viewAction;
		return ob_get_clean();
	}

	public function JSPrint(){
		$html = '';
		if(isset($this->JS) && is_array($this->JS)){
			ksort($this->JS);
			foreach($this->JS as $v){
				foreach($v as $row){
					if(substr($row, 0, 4) == 'http' || substr($row, 0, 1) == '/') $html .= chr(9) . '<script src="' . $row . '" charset="utf8"></script>' . chr(10);
					else $html .= chr(9) . '<script src="' . _SKINURL . '/js/' . $row . '" charset="utf8"></script>' . chr(10);
				}
			}
		}
		return $html;
	}

	public function JSAdd($js, $idx = 100){
		$this->JS[$idx][] = $js;
	}

	public function CSSPrint(){
		$html = '';
		if(isset($this->CSS) && is_array($this->CSS)){
			ksort($this->CSS);
			foreach($this->CSS as $v){
				foreach($v as $row){
					if(substr($row, 0, 4) == 'http' || substr($row, 0, 1) == '/') $html .= chr(9) . '<link rel="stylesheet" href="' . $row . '">' . chr(10);
					else $html .= chr(9) . '<link rel="stylesheet" href="' . _SKINURL . '/css/' . $row . '">' . chr(10);
				}
			}
		}
		return $html;
	}

	public function CSSAdd($css, $idx = 100){
		$this->CSS[$idx][] = $css;
	}

	public function CSSAdd2($css, $idx = 100){
		if(strpos($css, '?') !== false){
			$ex1 = explode('?', $css);
			$queryParam = '?'.array_pop($ex1);
			$css = $ex1[0];
		}else $queryParam = '';

		$ex = explode('.', $css);
		array_pop($ex);
		$convCss = implode('.', $ex).'.css';
		$target = _HTMLURL.'/css'.($convCss[0] == '/' ? $convCss : '/'.$convCss);

		if(_DEVELOPERIS === true){
			$css2 = '/css'.($css[0] == '/' ? $css : '/'.$css);
			$dir = _SKINDIR;
			if(file_exists(_HTMLDIR.$css2)) $dir = _HTMLDIR;
			else if(!file_exists(_SKINDIR.$css2)) $dir = false;

			if($dir !== false) BH_CSS($dir.$css2, _DIR.$target);
		}
		$this->CSS[$idx][] = $target.$queryParam;
	}

	public function URLAction($Action = ''){
		return $this->CtrlUrl.'/'.$Action;
	}

	public function URLBase($Controller = ''){
		return $this->BaseDir.'/'.$Controller;
	}
}