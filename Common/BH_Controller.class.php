<?php
/**
 * Bang Hun.
 * 16.07.10
 */

abstract class BH_Controller{
	public $Layout = '';
	public $Html;

	public $Title;
	private $CSS = array();
	private $JS = array();
	public $Action;
	public $Controller;
	public $ID;
	public $TID;
	public static $IMAGE_EXT = array('jpg','jpeg','png','gif','bmp');
	public static $POSSIBLE_EXT = array('jpg','jpeg','png','gif','bmp','zip','7z','gz','xz','tar',
		'xls', 'xlsx', 'ppt', 'doc', 'hwp', 'pdf', 'docx', 'pptx',
		'avi', 'mov', 'mkv', 'mpg', 'mpeg', 'wmv','asf','asx', 'flv', 'm4v', 'mp4');

	private $FollowQuery = array();
	public $_Value = array();

	/**
	 * @var BH_Common
	 */
	public $_CF;

	public function __construct(){
		$this->_CF = new \BH_Common();
		$this->Action = $GLOBALS['_BH_App']->Action;
		$this->Controller = $GLOBALS['_BH_App']->Controller;
		$this->ID = $GLOBALS['_BH_App']->ID;
		$this->TID = $GLOBALS['_BH_App']->TID;
	}

	abstract public function __Init();

	/**
	 *  항상 따라다니는 URL을 지정
	 * @param array $ar
	 */
	public function SetFollowQuery(array $ar){
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
	public function _View($Model = NULL, $Data = NULL){
		$viewAction = $this->Html ? $this->Html : $GLOBALS['_BH_App']->Action;
		if(!$viewAction) $viewAction = 'Index';

		$html = substr($viewAction, 0, 1) == '/' ? $viewAction :
			($GLOBALS['_BH_App']->NativeDir ? '/'.$GLOBALS['_BH_App']->NativeDir : '').'/'.$GLOBALS['_BH_App']->Controller.'/'.$viewAction;
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
			if(_DEVELOPERIS === true && _CREATE_HTML_ALL !== true){
				ReplaceHTMLFile(_SKINDIR.$layout, _HTMLDIR.$layout);
			}
			if($layout && file_exists(_HTMLDIR.$layout)){
				require _HTMLDIR.$layout;
			}
		}

		echo $_BODY;
	}

	public function _GetView($Model = NULL, $Data = NULL){
		$viewAction = $this->Html ? $this->Html : $GLOBALS['_BH_App']->Action;
		if(!$viewAction) $viewAction = 'Index';

		$html = substr($viewAction, 0, 1) == '/' ? $viewAction :
			($GLOBALS['_BH_App']->NativeDir ? '/'.$GLOBALS['_BH_App']->NativeDir : '').'/'.$GLOBALS['_BH_App']->Controller.'/'.(substr($viewAction, -5) == '.html' ? $viewAction : $viewAction.'.html');

		if(_DEVELOPERIS === true && _CREATE_HTML_ALL !== true){
			ReplaceHTMLFile(_SKINDIR.$html, _HTMLDIR.$html);
		}

		ob_start();
		if(file_exists(_HTMLDIR.$html)){
			require _HTMLDIR . $html;
		}else{
			echo 'ERROR : NOT EXISTS TEMPLATE : '.$viewAction;
		}
		return ob_get_clean();
	}

	public function JSPrint(){
		$html = '';
		if(isset($this->JS) && is_array($this->JS)){
			ksort($this->JS);
			foreach($this->JS as $v){
				foreach($v as $row){
					if(substr($row, 0, 4) == 'http') $html .= chr(9) . '<script src="' . $row . '" charset="utf8"></script>' . chr(10);
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

		if(_DEVELOPERIS === true){
			$css2 = '/css'.($css[0] == '/' ? $css : '/'.$css);
			$dir = _SKINDIR;
			if(file_exists(_HTMLDIR.$css2)) $dir = _HTMLDIR;
			else if(!file_exists(_SKINDIR.$css2)) return;

			$d = BH_CSS($dir.$css2);
			file_put_contents(_HTMLDIR.'/css'.($convCss[0] == '/' ? $convCss : '/'.$convCss), $d);
			@chmod(_HTMLDIR.'/css/'.$convCss, 0777);
		}
		$this->CSS[$idx][] = _HTMLURL.'/css/'.$convCss.$queryParam;
	}

	public function URLAction($Action = ''){
		return $GLOBALS['_BH_App']->CtrlUrl.'/'.$Action;
	}

	public function URLBase($Controller = ''){
		return $GLOBALS['_BH_App']->BaseDir.'/'.$Controller;
	}
}

