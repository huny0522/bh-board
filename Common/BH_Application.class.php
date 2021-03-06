<?php

/**
 * Bang Hun.
 * 16.07.10
 */
class BH_Application
{

	public static $controllerInstance = null;
	public static $controllerName = '';
	public static $action = '';
	public static $id = '';
	public static $id2 = '';
	public static $nativeDir = '';
	public static $nativeSkinDir = '';
	public static $baseDir = '';
	public static $tid = '';
	public static $sub_tid = '';
	public static $ctrlUrl = '';
	public static $version = '';
	/**
	 * @var _ConfigMap
	 */
	public static $cfg = array();
	public static $showError = false;
	public static $layout = null;
	public static $parentLayout = '';
	public static $html;
	public static $title;
	public static $css = array();
	public static $js = array();
	public static $followQuery = array();
	/** @var array $settingData = DeepAssocComplete::SettingData() */
	public static $settingData = array();
	public static $data = array();
	public static $bodyHtml = '';
	public static $extendMethod = array();

	/** @var array $lang = \Custom\Lang\kor::Lang() */
	public static $lang = array();
	/** @var callable $routingFailFunc */
	public static $routingFailFunc = null;

	/** @var SysArrayObject */
	public static $_get;
	/** @var SysArrayObject */
	public static $_post;
	/** @var SysArrayObject */
	public static $_session;

	private function __construct(){

	}

	public static function AutoLoad($class){
		if(substr($class, -5) === 'Model') $file = _MODELDIR . '/' . $class . '.php';
		else $file = _DIR . '/' . str_replace('\\', '/', $class) . '.php';
		if(file_exists($file)) require $file;
	}

	public static function run(){
		self::$_get = new SysArrayObject();
		self::$_post = new SysArrayObject('post');
		self::$_session = new SysArrayObject('session');

		self::$settingData['URLFirst'] = '';
		spl_autoload_register(array('BH_Application', 'AutoLoad'));

		$composerFile = _DIR . '/vendor/autoload.php';
		if(file_exists($composerFile)) require $composerFile;


		if(_IS_DEVELOPER_IP === true && PHP_RUN_CLI !== true) require _DIR . '/DBUpdate.php';

		// ----------------------
		//
		//    라우팅 초기화
		//
		self::$settingData['GetUrl'] = explode('/', isset($_GET['_bh_url']) ? $_GET['_bh_url'] : '');
		for($i = 0; $i < 10; $i++) if(!isset(self::$settingData['GetUrl'][$i])) self::$settingData['GetUrl'][$i] = '';

		if(self::$settingData['GetUrl'][1] == 'MyIp'){
			echo $_SERVER['REMOTE_ADDR'];
			exit;
		}

		if(self::$settingData['GetUrl'][1] == '~Create'){
			if(_DEVELOPERIS === true && _POSTIS === true){
				if(!isset($_POST['const']) || $_POST['const'] != 'y') $_POST['table_name'] = "'{$_POST['table_name']}'";
				BH_HtmlCreate::CreateController($_POST['controller_name'], $_POST['model_name'], $_POST['table_name']);
			}
			exit;
		}

		if(self::$settingData['GetUrl'][1] == '_Refresh'){
			if(_DEVELOPERIS === true){
				self::$cfg->Sys()->refresh->value++;
				$res = self::$cfg->Sys()->DataWrite();

				if($res->result){
					if(_REFRESH_HTML_ALL === true){
						delTree(\Paths::DirOfHtml());
						ReplaceHTMLAll(\Paths::DirOfSkin(), \Paths::DirOfHtml());
						ReplaceBHCSSALL(\Paths::DirOfHtml(), \Paths::DirOfHtml());
						ReplaceBHCSSALL(\Paths::DirOfSkin(), \Paths::DirOfHtml());
					}

					if(isset(self::$extendMethod['refreshExtend'])){
						$RefreshExtend = self::$extendMethod['refreshExtend'];
						$RefreshExtend();
					}
					URLReplace($_GET['r_url']);
				}
				else URLReplace(-1, $res->message);
			}
			exit;
		}

		if(!isset(self::$settingData['GetUrl'][1]) || !strlen(self::$settingData['GetUrl'][1])) self::$settingData['GetUrl'][1] = _DEFAULT_CONTROLLER;


		self::$baseDir = Paths::Url();
		self::$nativeDir = '';

		require _DIR . '/Custom/BH_Router.php';

		//
		//    라우팅 초기화
		//
		// ----------------------


		if(!self::$controllerName) self::$controllerName = _DEFAULT_CONTROLLER;
		if(!strlen(self::$action)) self::$action = 'Index';
		else if(strtolower(substr(self::$action, 0, 4)) == 'post') self::$action = preg_replace('/^(Post)+(.*)/i', '$2', self::$action);

		if(substr(self::$action, 0, 1) == '_') self::$action = preg_replace('/_+(.*+)/', '$1', self::$action);

		if(substr(self::$action, 0, 1) == '~'){
			self::$id = substr(self::$action, 1);
			self::$action = '_DirectView';
		}

		else if(substr(self::$controllerName, 0, 1) == '~'){
			self::$id = substr(self::$controllerName, 1);
			self::$controllerName = _DEFAULT_CONTROLLER;
			self::$action = '_DirectView';
		}

		$path = _DIR . '/Controller/' . (self::$nativeDir ? self::$nativeDir . '/' : '') . self::$controllerName . '.php';

		if(file_exists($path)){

			if(isset(self::$extendMethod['BeforeLoadController'])){
				$beforeLoadController = self::$extendMethod['BeforeLoadController'];
				$beforeLoadController();
			}

			require $path;
			$controller = '\\Controller\\' . (self::$nativeDir ? str_replace('/', '\\', self::$nativeDir) . '\\' : '') . self::$controllerName;
			if(!class_exists($controller)){
				$code = 101;
				$msg = 'CLASS(' . $controller . ') DOES NOT EXIST.';
				if(is_callable(self::$routingFailFunc)){
					$func = self::$routingFailFunc;
					$func($code, $msg);
				}
				else if(_DEVELOPERIS === true) echo $msg;
				exit;
			}

			$action = _POSTIS === true ? 'Post' . self::$action : self::$action;

			if(method_exists($controller, $action) && is_callable(array($controller, $action))){
				self::$controllerInstance = new $controller();

				if(isset(self::$extendMethod['createControllerInstance'])){
					$beforeLoadController = self::$extendMethod['createControllerInstance'];
					$beforeLoadController();
				}

				if(method_exists(self::$controllerInstance, '__Init')) self::$controllerInstance->__Init();
				self::$controllerInstance->{$action}();
			}
			else{
				$code = 201;
				$msg = 'METHOD DOES NOT EXIST(#2)';
				if(is_callable(self::$routingFailFunc)){
					$func = self::$routingFailFunc;
					$func($code, $msg);
				}
				else if(_DEVELOPERIS === true) echo $msg;
				else URLReplace(Paths::Url() . '/');
				exit;
			}
		}
		else{
			$code = 301;
			$msg = 'Controller file not exist.';
			if(is_callable(self::$routingFailFunc)){
				$func = self::$routingFailFunc;
				$func($code, $msg);
			}
			else if(_DEVELOPERIS === true && _SHOW_CREATE_GUIDE === true) require _COMMONDIR . '/Create.html';
			else URLReplace(Paths::Url() . '/');
			exit;
		}
	}

	/**
	 *  항상 따라다니는 URL을 지정
	 * @param array $ar
	 */
	public static function SetFollowQuery($ar){
		if(!is_array($ar)) $ar = func_get_args();
		foreach($ar as $v) if(isset($_GET[$v]) && !empty($_GET[$v])) self::$followQuery[$v] = $_GET[$v];
	}

	/**
	 *  항상 따라다니는 URL을 지정(post)
	 * @param array $ar
	 */
	public static function SetPostFollowQuery($ar){
		if(!is_array($ar)) $ar = func_get_args();
		foreach($ar as $v) if(isset($_POST[$v]) && !empty($_POST[$v])) self::$followQuery[$v] = $_POST[$v];
	}

	/**
	 * 항상 따라다니는 URL을 출력
	 * @param string $ar 제외할 쿼리 파라미터
	 * @param string $begin 쿼리 시작(& 또는 ?)
	 *
	 * @return string
	 */
	public static function GetFollowQuery($ar = '', $begin = '?'){
		$fq = self::$followQuery;
		if($ar){
			if(is_string($ar)) $ar = explode(',', trim($ar));
			if(is_array($ar) && sizeof($ar)) foreach($ar as $v) unset($fq[trim($v)]);
		}

		$queryparam = '';
		foreach($fq as $k => $v){
			if(is_array($v)) foreach($v as $v2) $queryparam .= ($queryparam ? '&' : $begin) . $k . '[]=' . urlencode($v2);
			else $queryparam .= ($queryparam ? '&' : $begin) . $k . '=' . urlencode($v);
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
		$fq = self::$followQuery;
		if($ar){
			if(is_string($ar)) $ar = explode(',', $ar);
			if(is_array($ar) && sizeof($ar)) foreach($ar as $v) unset($fq[trim($v)]);
		}

		$queryparam = '';
		foreach($fq as $k => $v) $queryparam .= '<input type="hidden" name="' . GetDBText($k) . '" value="' . GetDBText($v) . '">';
		return $queryparam;
	}

	/*
	 * html : self::$html 를 지정하면 그 파일을 찾고 아니라면 액션명의 파일을 찾는다.
	 * layout : /Layout 디렉토리에서 self::$layout 의 파일을 찾아 레이아웃을 생성
	 */
	private static function SetViewHtml(&$Model, &$Data, $disableLayout = false, $htmlPath = ''){
		$d_b = phpversion() < 5.6 ? debug_backtrace() : debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);

		$Ctrl = &$d_b[2]['object'];

		if(strlen($htmlPath)) $viewAction = $htmlPath;
		else if(isset($Ctrl->html) && strlen($Ctrl->html)) $viewAction = $Ctrl->html;
		else if(self::$html) $viewAction = self::$html;
		else if(self::$action) $viewAction = self::$action;
		else $viewAction = 'Index';

		if(!self::$nativeSkinDir) self::$nativeSkinDir = self::$nativeDir;
		$html = substr($viewAction, 0, 1) == '/' ? $viewAction : (self::$nativeSkinDir ? '/' . self::$nativeSkinDir : '') . '/' . self::$controllerName . '/' . $viewAction;
		if(substr($html, -5) != '.html') $html .= '.html';

		if(isset(self::$extendMethod['htmlPathSet']) && is_callable(self::$extendMethod['htmlPathSet'])){
			$htmlPathSet = self::$extendMethod['htmlPathSet'];
			$h = $htmlPathSet($html);
			if($h) $html = $h;
		}

		$path = \Paths::DirOfHtml() . $html;
		$skinPath = \Paths::DirOfSkin() . $html;
		if((_DEVELOPERIS === true && _CREATE_HTML_ALL !== true) || !file_exists($path)){
			require_once 'HtmlConvert.php';
			ReplaceHTMLFile($skinPath, $path);
		}

		ob_start();
		if(file_exists($path)) require $path;
		else{
			if(_DEVELOPERIS !== true) echo 'ERROR : NOT EXISTS TEMPLATE';
			else echo 'ERROR : NOT EXISTS TEMPLATE : ' . $path;
		}
		self::$bodyHtml = ob_get_clean();

		if(!$disableLayout && !is_null(self::$layout)){
			self::$parentLayout = self::$layout;
			while(strlen(self::$parentLayout)){
				self::$layout = self::$parentLayout;
				self::$parentLayout = '';

				$layout = '/Layout/' . self::$layout;
				if(substr($layout, -5) != '.html') $layout .= '.html';

				if(isset(self::$extendMethod['layoutPathSet']) && is_callable(self::$extendMethod['layoutPathSet'])){
					$htmlPathSet = self::$extendMethod['layoutPathSet'];
					$l = $htmlPathSet($layout);
					if($l) $layout = $l;
				}

				$path = \Paths::DirOfHtml() . $layout;
				if((_DEVELOPERIS === true && _CREATE_HTML_ALL !== true) || !file_exists($path)){
					require_once 'HtmlConvert.php';
					ReplaceHTMLFile(\Paths::DirOfSkin() . $layout, $path);
				}

				if(file_exists($path)){
					ob_start();
					require $path;
					self::$bodyHtml = ob_get_clean();
				}

			}
		}

		if(isset(self::$extendMethod['AfterSetView'])){
			$AfterSetView = self::$extendMethod['AfterSetView'];
			$AfterSetView();
		}
	}

	// 레이아웃을 포함한 HTML을 출력한다.
	public static function View(){
		$Model = $Data = $html = null;
		$args = func_get_args();
		foreach($args as $k => &$row){
			if(is_object($row) && substr(get_class($row), -5) === 'Model') $Model = $row;
			else if(!$k && is_string($row)) $html = $row;
			else $Data = $row;
		}
		if(is_null($html)) self::SetViewHtml($Model, $Data);
		else self::SetViewHtml($Model, $Data, false, $html);
		if(_JSONIS === true) JSON(true, '', self::$bodyHtml);
		else echo self::$bodyHtml;
	}

	// 레이아웃을 제외한 HTML을 출력한다.
	public static function OnlyView(){
		$Model = $Data = $html = null;
		$args = func_get_args();
		foreach($args as $k => &$row){
			if(is_object($row) && substr(get_class($row), -5) === 'Model') $Model = $row;
			else if(!$k && is_string($row)) $html = $row;
			else $Data = $row;
		}

		if(is_null($html)) self::SetViewHtml($Model, $Data, true);
		else self::SetViewHtml($Model, $Data, true, $html);
		if(_JSONIS === true) JSON(true, '', self::$bodyHtml);
		else echo self::$bodyHtml;
	}

	// 레이아웃을 포함한 HTML을 가져온다.
	public static function GetView(){
		$Model = $Data = $html = null;
		$args = func_get_args();
		foreach($args as $k => &$row){
			if(is_object($row) && substr(get_class($row), -5) === 'Model') $Model = $row;
			else if(!$k && is_string($row)) $html = $row;
			else $Data = $row;
		}

		if(is_null($html)) self::SetViewHtml($Model, $Data);
		else self::SetViewHtml($Model, $Data, false, $html);
		return self::$bodyHtml;
	}

	// 레이아웃을 제외한 HTML을 가져온다.
	public static function GetOnlyView(){
		$Model = $Data = $html = null;
		$args = func_get_args();
		foreach($args as $k => &$row){
			if(is_object($row) && substr(get_class($row), -5) === 'Model') $Model = $row;
			else if(!$k && is_string($row)) $html = $row;
			else $Data = $row;
		}

		if(is_null($html)) self::SetViewHtml($Model, $Data, true);
		else self::SetViewHtml($Model, $Data, true, $html);
		return self::$bodyHtml;
	}

	public static function JSPrint(){
		$html = '';
		if(isset(self::$js) && is_array(self::$js)){
			ksort(self::$js);
			foreach(self::$js as $v){
				foreach($v as $row){
					if(substr($row, 0, 4) == 'http' || substr($row, 0, 1) == '/') $html .= chr(9) . '<script src="' . $row . '" charset="utf8"></script>' . chr(10);
					else
						$html .= chr(9) . '<script src="' . \Paths::UrlOfSkin() . '/js/' . $row . '" charset="utf8"></script>' . chr(10);
				}
			}
		}
		return $html;
	}

	public static function JSAdd($js, $idx = 100){
		self::$js[$idx][] = $js;
	}

	public static function CSSPrint(){
		$html = '';
		if(isset(self::$css) && is_array(self::$css)){
			ksort(self::$css);
			foreach(self::$css as $v){
				foreach($v as $row){
					if(substr($row, 0, 4) == 'http' || substr($row, 0, 1) == '/') $html .= chr(9) . '<link rel="stylesheet" href="' . $row . '">' . chr(10);
					else
						$html .= chr(9) . '<link rel="stylesheet" href="' . \Paths::UrlOfSkin() . '/css/' . $row . '">' . chr(10);
				}
			}
		}
		return $html;
	}

	public static function CSSAdd($css, $idx = 100){
		if(strpos($css, '?') !== false){
			$ex1 = explode('?', $css);
			$queryParam = '?' . array_pop($ex1);
			$css = $ex1[0];
		}
		else $queryParam = '';

		$ex = explode('.', $css);
		$ext = array_pop($ex);
		if($ext == 'css'){
			self::$css[$idx][] = $css . $queryParam;
			return;
		}

		$convCss = (substr($css, strlen(BH\BHCss\BHCss::$fileExtension) * (-1)) === BH\BHCss\BHCss::$fileExtension) ? substr($css, 0, strlen(BH\BHCss\BHCss::$fileExtension) * (-1)) . '.css' : implode('.', $ex) . '.css';
		$target = '/css' . ($convCss[0] == '/' ? $convCss : '/' . $convCss);

		if(_DEVELOPERIS === true || !file_exists(\Paths::DirOfHtml() . $target)){
			$css2 = '/css' . ($css[0] == '/' ? $css : '/' . $css);
			$dir = \Paths::DirOfSkin();
			if(file_exists(\Paths::DirOfHtml() . $css2)) $dir = \Paths::DirOfHtml();
			else if(!file_exists(\Paths::DirOfSkin() . $css2)) $dir = false;

			if($dir !== false){
				$res = BH\BHCss\BHCss::conv($dir . $css2, \Paths::DirOfHtml() . $target);
			}
		}
		self::$css[$idx][] = \Paths::UrlOfHtml() . $target . $queryParam;
	}

	public static function URLAction($Action = ''){
		return self::$settingData['URLFirst'] . self::$ctrlUrl . '/' . $Action;
	}

	public static function URLBase($Controller = ''){
		return self::$settingData['URLFirst'] . self::$baseDir . '/' . $Controller;
	}

	/**
	 * 모델 생성
	 * @param string $ModelName
	 * @param string $connName
	 * @return mixed
	 */
	public static function &InitModel($ModelName, $connName = ''){
		$model = $ModelName . 'Model';
		if(class_exists($model)){
			$newModel = new $model($connName);
			return $newModel;
		}

		if(_DEVELOPERIS === true) echo $ModelName . '-Model is not exists';
		else echo 'ERROR';
		exit;
	}

}
