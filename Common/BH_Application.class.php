<?php
/**
 * Bang Hun.
 * 16.07.10
 */
class BH_Application{
	public static $ControllerInstance = null;
	/** @var BH_Router */
	public static $RouterInstance = null;

	public static $ControllerName = '';
	public static $Action = '';
	public static $ID = '';
	public static $NativeDir = '';
	public static $BaseDir = '';
	public static $TID = '';
	public static $CtrlUrl = '';

	public static $InstallIs = true;
	public static $CFG = array();

	// BH_Controller
	public static $Layout = null;
	public static $Html;
	public static $Title;

	private static $CSS = array();
	private static $JS = array();
	private static $FollowQuery = array();

	public static $SettingData = array();
	public static $Data = array();

	public static $BodyHtml = '';

	private function __construct(){
	}

	public static function run(){
		if(_DEVELOPERIS === true) self::$InstallIs = \DB::SQL()->TableExists(TABLE_MEMBER);

		self::$SettingData['MainMenu'] = array();
		self::$SettingData['SubMenu'] = array();
		// ----------------------
		//
		//    라우팅 초기화
		//
		self::$SettingData['GetUrl'] = explode('/', isset($_GET['_bh_url']) ? $_GET['_bh_url'] : '');
		for($i = 0; $i < 10; $i++) if(!isset(self::$SettingData['GetUrl'][$i])) self::$SettingData['GetUrl'][$i] = '';

		if(self::$SettingData['GetUrl'][1] == 'MyIp'){
			echo $_SERVER['REMOTE_ADDR'];
			exit;
		}

		if(self::$SettingData['GetUrl'][1] == '~Create'){
			if(_DEVELOPERIS === true && _POSTIS === true){
				if(!isset($_POST['const']) || $_POST['const'] != 'y') $_POST['table_name'] = "'{$_POST['table_name']}'";
				BH_HtmlCreate::CreateController($_POST['controller_name'], $_POST['model_name'], $_POST['table_name']);
			}
			exit;
		}

		if(self::$SettingData['GetUrl'][1] == '_Refresh'){
			if(_DEVELOPERIS === true){
				$s = \BH_Common::Config('Default', 'Refresh');
				$res = \BH_Common::SetConfig('Default', 'Refresh', $s+1);

				if($res->result){
					if(_REFRESH_HTML_ALL === true){
						delTree(_HTMLDIR);
						ReplaceHTMLAll(_SKINDIR, _HTMLDIR);
						ReplaceCSS2ALL(_HTMLDIR, _HTMLDIR);
						ReplaceCSS2ALL(_SKINDIR, _HTMLDIR);
					}
					if(_REFRESH_DB_CACHE_ALL === true) delTree(_DATADIR.'/temp');
					Redirect($_GET['r_url']);
				}
				else Redirect(-1, $res->message);
			}
			exit;
		}

		if(!isset(self::$SettingData['GetUrl'][1]) || !strlen(self::$SettingData['GetUrl'][1])) self::$SettingData['GetUrl'][1] = _DEFAULT_CONTROLLER;


		self::$BaseDir = _URL;
		self::$NativeDir = '';

		if(!self::$InstallIs){
			if(self::$SettingData['GetUrl'][1] == 'Install'){
				self::$ControllerName = self::$SettingData['GetUrl'][1];
				self::$Action = self::$SettingData['GetUrl'][2];
				self::$ID = self::$SettingData['GetUrl'][3];
				self::$CtrlUrl = _URL.'/'.self::$ControllerName;
			}
			else exit;
		}
		else require _COMMONDIR.'/BH_Router.php';

		//
		//    라우팅 초기화
		//
		// ----------------------


		if(!self::$ControllerName) self::$ControllerName = _DEFAULT_CONTROLLER;
		if(!strlen(self::$Action)) self::$Action = 'Index';
		else if(strtolower(substr(self::$Action, 0, 4)) == 'post') self::$Action = preg_replace('/^(Post)+(.*)/i', '$2', self::$Action);

		if(substr(self::$Action, 0, 1) == '_') self::$Action = preg_replace('/_+(.*+)/', '$1', self::$Action);

		if(substr(self::$Action, 0, 1) == '~'){
			self::$ID = substr(self::$Action, 1);
			self::$Action = '_DirectView';
		}

		$path = _DIR.'/Controller/'.(self::$NativeDir ? self::$NativeDir.'/' : '').self::$ControllerName.'.php';

		if(file_exists($path)){
			require $path;
			$controller = self::$NativeDir.'\\'.self::$ControllerName.'Controller';
			if (!class_exists($controller)) $controller = self::$ControllerName.'Controller';
			if (!class_exists($controller)){
				if(_DEVELOPERIS === true) echo '클래스('.$controller.')가 존재하지 않습니다.';
				exit;
			}

			$action = _POSTIS === true ? 'Post'.self::$Action : self::$Action;

			if(method_exists($controller, $action) && is_callable(array($controller, $action))){
				self::$ControllerInstance = new $controller();
				if(method_exists(self::$ControllerInstance, '__Init')) self::$ControllerInstance->__Init();
				self::$ControllerInstance->{$action}();
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
	public static function SetFollowQuery($ar){
		if(!is_array($ar)) $ar = func_get_args();
		foreach($ar as $v) if(isset($_GET[$v]) && !empty($_GET[$v])) self::$FollowQuery[$v] = $_GET[$v];
	}

	/**
	 * 항상 따라다니는 URL을 출력
	 * @param string $ar 제외할 쿼리 파라미터
	 * @param string $begin 쿼리 시작(& 또는 ?)
	 *
	 * @return string
	 */
	public static function GetFollowQuery($ar = '', $begin = '?'){
		$ar = trim($ar);
		$fq = self::$FollowQuery;
		if($ar){
			if(is_string($ar)) $ar = explode(',', $ar);
			if(is_array($ar) && sizeof($ar)) foreach($ar as $v) unset($fq[trim($v)]);
		}

		$queryparam = '';
		foreach($fq as $k => $v){
			if(is_array($v)) foreach($v as $v2) $queryparam .= ($queryparam ? '&' : $begin ).$k.'[]='.$v2;
			else $queryparam .= ($queryparam ? '&' : $begin ).$k.'='.$v;
		}
		return $queryparam;
	}

	/**
	 * 항상 따라다니는 URL을 input hidden 출력
	 * @param string $ar 제외할 쿼리 파라미터
	 * @return string
	 */
	public static function GetFollowQueryInput($ar = ''){
		$ar = trim($ar);
		$fq = self::$FollowQuery;
		if($ar){
			if(is_string($ar)) $ar = explode(',', $ar);
			if(is_array($ar) && sizeof($ar)) foreach($ar as $v) unset($fq[trim($v)]);
		}

		$queryparam = '';
		foreach($fq as $k => $v) $queryparam .= '<input type="hidden" name="'.GetDBText($k).'" value="'.GetDBText($v).'">';
		return $queryparam;
	}

	/**
	 * html : self::$Html 를 지정하면 그 파일을 찾고 아니라면 액션명의 파일을 찾는다.
	 * layout : /Layout 디렉토리에서 self::$Layout 의 파일을 찾아 레이아웃을 생성
	 * @param $Model mixed
	 * @param $Data mixed
	 * @param $DisableLayout bool
	 * @return string
	 */

	private static function SetViewHtml(&$Ctrl, &$Model, &$Data, $DisableLayout = false){
		$viewAction = isset($Ctrl->Html) && strlen($Ctrl->Html) ? $Ctrl->Html : (self::$Html ? self::$Html : self::$Action);
		if(!$viewAction) $viewAction = 'Index';

		$html = substr($viewAction, 0, 1) == '/' ? $viewAction :
			(self::$NativeDir ? '/'.self::$NativeDir : '').'/'.self::$ControllerName.'/'.$viewAction;
		if(substr($html, -5) != '.html') $html .= '.html';

		if(_DEVELOPERIS === true && _CREATE_HTML_ALL !== true) ReplaceHTMLFile(_SKINDIR.$html, _HTMLDIR.$html);

		ob_start();
		if(file_exists(_HTMLDIR.$html)) require _HTMLDIR . $html;
		else{
			if(_DEVELOPERIS !== true) echo 'ERROR : NOT EXISTS TEMPLATE';
			else echo 'ERROR : NOT EXISTS TEMPLATE : '._HTMLDIR.$html;
		}
		self::$BodyHtml = ob_get_clean();

		if(!$DisableLayout && !is_null(self::$Layout)){
			$layoutChoose = isset($opt['Layout']) && strlen($opt['Layout']) ? $opt['Layout'] : self::$Layout;
			$layout = '/Layout/'.($layoutChoose ? $layoutChoose :  _DEFAULT_LAYOUT);
			if(substr($layout, -5) != '.html') $layout .= '.html';
			if(_DEVELOPERIS === true && _CREATE_HTML_ALL !== true) ReplaceHTMLFile(_SKINDIR.$layout, _HTMLDIR.$layout);
			if($layout && file_exists(_HTMLDIR.$layout)) require _HTMLDIR.$layout;
		}
	}

	public static function View(&$Ctrl, $Model = NULL, $Data = NULL){
		self::SetViewHtml($Ctrl, $Model, $Data);
		echo self::$BodyHtml;
	}

	public static function OnlyView(&$Ctrl, $Model = NULL, $Data = NULL){
		self::SetViewHtml($Ctrl, $Model, $Data, true);
		echo self::$BodyHtml;
	}

	public static function PrintView(&$Ctrl, $Model = NULL, $Data = NULL){
		self::SetViewHtml($Ctrl, $Model, $Data);
		if(_JSONIS === true) JSON(true, '', self::$BodyHtml);
		else echo self::$BodyHtml;
	}

	public static function PrintOnlyView(&$Ctrl, $Model = NULL, $Data = NULL){
		self::SetViewHtml($Ctrl, $Model, $Data, true);
		if(_JSONIS === true) JSON(true, '', self::$BodyHtml);
		else echo self::$BodyHtml;
	}

	public static function &GetView(&$Ctrl, $Model = NULL, $Data = NULL){
		self::SetViewHtml($Ctrl, $Model, $Data);
		return self::$BodyHtml;
	}

	public static function &GetOnlyView(&$Ctrl, $Model = NULL, $Data = NULL){
		self::SetViewHtml($Ctrl, $Model, $Data, true);
		return self::$BodyHtml;
	}

	public static function JSPrint(){
		$html = '';
		if(isset(self::$JS) && is_array(self::$JS)){
			ksort(self::$JS);
			foreach(self::$JS as $v){
				foreach($v as $row){
					if(substr($row, 0, 4) == 'http' || substr($row, 0, 1) == '/') $html .= chr(9) . '<script src="' . $row . '" charset="utf8"></script>' . chr(10);
					else $html .= chr(9) . '<script src="' . _SKINURL . '/js/' . $row . '" charset="utf8"></script>' . chr(10);
				}
			}
		}
		return $html;
	}

	public static function JSAdd($js, $idx = 100){
		self::$JS[$idx][] = $js;
	}

	public static function CSSPrint(){
		$html = '';
		if(isset(self::$CSS) && is_array(self::$CSS)){
			ksort(self::$CSS);
			foreach(self::$CSS as $v){
				foreach($v as $row){
					if(substr($row, 0, 4) == 'http' || substr($row, 0, 1) == '/') $html .= chr(9) . '<link rel="stylesheet" href="' . $row . '">' . chr(10);
					else $html .= chr(9) . '<link rel="stylesheet" href="' . _SKINURL . '/css/' . $row . '">' . chr(10);
				}
			}
		}
		return $html;
	}

	public static function CSSAdd($css, $idx = 100){
		if(strpos($css, '?') !== false){
			$ex1 = explode('?', $css);
			$queryParam = '?'.array_pop($ex1);
			$css = $ex1[0];
		}else $queryParam = '';

		$ex = explode('.', $css);
		$ext = array_pop($ex);
		if($ext == 'css'){
			self::$CSS[$idx][] = $css;
			return;
		}
		$convCss = implode('.', $ex).'.css';
		$target = _HTMLURL.'/css'.($convCss[0] == '/' ? $convCss : '/'.$convCss);

		if(_DEVELOPERIS === true){
			$css2 = '/css'.($css[0] == '/' ? $css : '/'.$css);
			$dir = _SKINDIR;
			if(file_exists(_HTMLDIR.$css2)) $dir = _HTMLDIR;
			else if(!file_exists(_SKINDIR.$css2)) $dir = false;

			if($dir !== false) BH_CSS($dir.$css2, _DIR.$target);
		}
		self::$CSS[$idx][] = $target.$queryParam;
	}

	public static function URLAction($Action = ''){
		return self::$CtrlUrl.'/'.$Action;
	}

	public static function URLBase($Controller = ''){
		return self::$BaseDir.'/'.$Controller;
	}

	/**
	 * 모델 생성
	 * @param string $ModelName
	 * @return mixed
	 */
	public static function &GetModel($ModelName){
		$model = $ModelName.'Model';
		require_once _MODELDIR.'/'.$ModelName.'.model.php';
		if(class_exists($model)) return $model::Get();

		if(_DEVELOPERIS === true) echo $ModelName.'-Model is not exists';
		else echo 'ERROR';
		exit;
	}
}