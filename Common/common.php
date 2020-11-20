<?php

/**
 * Bang Hun.
 * 16.07.10
 */

use \BH_Application as App;
use \DB as DB;

class BH_Result
{
	public $result = false;
	public $message = '';
	public $data;

	public static function Init($res, $mes = null, $dt = null){
		$result = new static();
		$result->result = $res;
		if(!is_null($mes)) $result->message = $mes;
		if(!is_null($dt)) $result->data = $dt;
		return $result;
	}
}

class BH_InsertResult
{
	public $result = false;
	public $id = null;
	public $message = '';
}


class ResultAction
{
	public $result = false;
	public $message = '';
	public $data = '';
	public $url = '';
	public $isJson = false;

	/**
	 * @param bool $result
	 * @param string $message
	 * @param string $data
	 * @param string $url
	 * @return ResultAction
	 */
	public static function GetInstance($result = false, $message = '', $data = '', $url = ''){
		$static = new static();
		$static->result = $result;
		$static->message = $message;
		$static->data = $data;
		$static->url = (!$url && !$static->result) ? '-1' : $url;
		return $static;
	}

	/**
	 * @param $result
	 * @return ResultAction
	 */
	public function SetResult($result){
		$this->result = $result;
		return $this;
	}

	/**
	 * @param mixed $data
	 * @return ResultAction
	 */
	public function SetData($data){
		$this->data = $data;
		return $this;
	}

	/**
	 * @param string $url
	 * @return ResultAction
	 */
	public function SetUrl($url = '-1'){
		$this->url = $url;
		return $this;
	}

	/**
	 * @param string $message
	 * @return ResultAction
	 */
	public function SetMessage($message){
		$this->message = $message;
		return $this;
	}

	/**
	 * @param bool $bool
	 * @return ResultAction
	 */
	public function IsJson($bool = false){
		$this->isJson = $bool;
		return $this;
	}

	/**
	 * @param string $data
	 * @param string $url
	 */
	public function SetTrueAndAction($data = '', $url = ''){
		$this->data = $data;
		$this->url = $url;
		$this->result = true;
		$this->Action();
	}

	/**
	 * @param string $message
	 */
	public function SetMsgAndAction($message){
		$this->message = $message;
		$this->Action();
	}

	public function Action(){
		if($this->isJson) JSON($this->result, $this->message, $this->data);
		else URLRedirect($this->url, $this->message);
	}
}

class Paths{
	private static $dir = _DIR;

	private static $dataDirName = _DATADIRNAME;
	private static $skinDirName = _SKINDIRNAME;
	private static $uploadDirName = _UPLOAD_DIRNAME;
	private static $adminUrlName = _ADMINURLNAME;

	private static $skinDir = _SKINDIR;
	private static $dataDir = _DATADIR;
	private static $htmlDir = _HTMLDIR;
	private static $uploadDir = _UPLOAD_DIR;

	private static $url = _URL;
	private static $dataUrl = _DATAURL;
	private static $skinUrl = _SKINURL;
	private static $htmlUrl = _HTMLURL;
	private static $adminUrl = _ADMINURL;
	private static $imageUrl = _IMGURL;
	private static $uploadUrl = _UPLOAD_URL;

	public static function SetDefaultUrl($str){
		self::$url = $str;
		self::$dataUrl = self::$url . '/' . self::$dataDirName;
		self::$skinUrl = self::$url . '/' . self::$skinDirName;
		self::$uploadUrl = self::$dataUrl . '/' . self::$uploadDirName;
		self::$htmlUrl = self::$dataUrl . '/_HTML';
		self::$imageUrl = self::$skinUrl . '/images';
		self::$adminUrl = self::$url . '/' . self::$adminUrlName;
	}

	public static function SetDataDirName($str){
		self::$dataDirName = $str;
		self::$dataUrl = self::$url . '/' . self::$dataDirName;
		self::$dataDir = self::$dir . '/' . self::$dataDirName;

		self::$htmlUrl = self::$dataUrl . '/_HTML';
		self::$uploadUrl = self::$dataUrl . '/' . self::$uploadDirName;
	}

	public static function SetSkinDirName($str){
		self::$skinDirName = $str;
		self::$skinUrl = self::$url . '/' . self::$skinDirName;
		self::$skinDir = self::$dir . '/' . self::$skinDirName;

		self::$imageUrl = self::$skinUrl . '/images';
	}

	public static function SetUploadDirName($str){
		self::$uploadDirName = $str;

		self::$uploadUrl = self::$url . '/' . self::$uploadDirName;
		self::$uploadDir = self::$dir . '/' . self::$uploadDirName;
	}

	public static function SetAdminUrlName($str){
		self::$adminUrlName = $str;
		self::$adminUrl = self::$url . '/' .self::$adminUrlName;
	}

	public static function Url($str = ''){
		return self::$url . $str;
	}

	public static function Dir($str = ''){
		return self::$dir . $str;
	}

	public static function NameOfAdmin(){
		return self::$adminUrlName;
	}

	public static function NameOfSkin(){
		return self::$skinDirName;
	}

	public static function NameOfData(){
		return self::$dataDirName;
	}

	public static function DirOfData($str = ''){
		return self::$dataDir . $str;
	}

	public static function UrlOfData($str = ''){
		return self::$dataUrl . $str;
	}

	public static function DirOfSkin($str = ''){
		return self::$skinDir . $str;
	}

	public static function UrlOfSkin($str = ''){
		return self::$skinUrl . $str;
	}

	public static function DirOfHtml($str = ''){
		return self::$htmlDir . $str;
	}

	public static function UrlOfHtml($str = ''){
		return self::$htmlUrl . $str;
	}

	public static function DirOfUpload($str = ''){
		return self::$uploadDir . $str;
	}

	public static function UrlOfUpload($str = ''){
		return self::$uploadUrl . $str;
	}

	public static function UrlOfAdmin($str = ''){
		return self::$adminUrl . $str;
	}
}

class BHError
{
	private $first = 1;
	private $second = 0;
	private $third = 1;
	private $msg = '';
	private $devMsg = '';
	private $url = '-1';
	private $isJson = false;

	public function __construct($first, $second, $num, $msg = '', $devMsg = ''){
		$this->first = $first;
		$this->second = $second;
		$this->third = $num;
		$this->msg = $msg;
		$this->devMsg = $devMsg;
	}

	public function __toString(){
		return $this->Get();
	}

	/**
	 * @param string $first
	 * @param string $second
	 * @param int $num
	 * @param string $msg
	 * @param string $devMsg
	 * @return BHError
	 */
	public static function Instance($first, $second, $num, $msg = '', $devMsg = ''){
		$static = new static($first, $second, $num, $msg, $devMsg);
		return $static;
	}
	public function Msg($msg, $devMsg = ''){ $this->msg = $msg; $this->devMsg = $devMsg; return $this; }

	public function IsJson($bool = true){ $this->isJson = $bool; return $this; }

	public function SetUrl($url = '-1'){ $this->url = $url; return $this; }

	/**
	 * @param string $msg
	 * @param string $devMsg
	 * @return string
	 */
	public function Get($msg = '', $devMsg = ''){
		if(strlen($msg)) $this->msg = $msg;
		if(strlen($devMsg)) $this->devMsg = $devMsg;
		$res = 'Error#' . $this->first . $this->second . $this->third;
		if(strlen($this->msg)) $res = '['  . $res . ']' . $this->msg;
		if(_DEVELOPERIS === true && strlen($this->devMsg)) $res .= ' (' . $this->devMsg . ')';
		return $res;
	}

	/**
	 * @param string $msg
	 * @param string $devMsg
	 */
	public function Run($msg = '', $devMsg = ''){
		if($this->isJson) $this->JSON($msg, $devMsg);
		else $this->Redirect($this->url, $msg, $devMsg);
	}

	/**
	 * @param string $msg
	 * @param string $devMsg
	 */
	public function JSON($msg = '', $devMsg = ''){
		JSON(false, $this->Get($msg, $devMsg));
	}

	/**
	 * @param string $url
	 * @param string $msg
	 * @param string $devMsg
	 */
	public function Redirect($url = '-1', $msg = '', $devMsg = ''){
		URLRedirect($url, $this->Get($msg, $devMsg));
	}
}

define('_POSTIS', isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST');
define('_AJAXIS', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
define('_JSONIS', isset($_SERVER['HTTP_ACCEPT']) && strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false);

if(_IS_DEVELOPER_IP === true){
	$developerBottomHtml = '<ul style="position:fixed; bottom:10px; right:10px; z-index:9999; display:table; opacity:0;" onmouseenter="this.style.opacity=1" onmouseleave="this.style.opacity=0">';
	if(_DEVELOPERIS !== true){
		$developerBottomHtml .= '<li style="display:table-cell; vertical-align:middle;"><form method="post" action="' . Paths::UrlOfAdmin() . '/Login/DevLogin?r_url=' . urlencode($_SERVER['REQUEST_URI']) . '"><input type="password" name="devpwd" value="" style="height:30px; padding:0 15px; margin-right:5px; border:0; font-size:12px; background:rgba(0,0,0,0.2); color:#fff; border-radius:15px;"><button type="submit" style="height:30px; padding:0 15px; margin-right:15px; font-size:12px; background:rgba(0,0,0,0.5); color:#fff; border-radius:15px;">Developer Join</button></form></li>';
	}
	else{
		$developerBottomHtml .= '<li style="display:table-cell; vertical-align:middle;"><a href="' . Paths::UrlOfAdmin() . '/Login/DevLogout?r_url=' . urlencode(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '') . '" style="display:block; height:30px; line-height:30px; padding:0 15px; margin-right:5px; font-size:12px; background:rgba(0,0,0,0.5); color:#fff; border-radius:15px;">Developer Logout</a></li>';

		$developerBottomHtml .= '<li style="display:table-cell; vertical-align:middle;"><a id="_BH_RefreshBtn" href="' . Paths::Url() . '/_Refresh?r_url=' . urlencode(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '') . '" style="display:block; height:30px; line-height:30px; padding:0 15px; font-size:12px; background:rgba(0,0,0,0.5); color:#fff; border-radius:15px;">Refresh</a></li>';
	}
	$developerBottomHtml .= '</ul>';
}
else $developerBottomHtml = '';


define('_REFRESH_BTN', PHP_RUN_CLI ? '' : $developerBottomHtml);


require _COMMONDIR . '/BH_PDO.class.php';
require _COMMONDIR . '/BH_Application.class.php';
require _COMMONDIR . '/BH_Model.class.php';
require _COMMONDIR . '/BH_Common.class.php';
if(file_exists(_DIR . '/Custom/ConfigSetting.php')) require _DIR . '/Custom/ConfigSetting.php';
require _COMMONDIR . '/BHCss/core/BHCss.php';

require _DIR . '/Custom/Lang/' . LANG_FILE;

if(get_magic_quotes_gpc()){
	$_POST = BH_Common::StripSlashes($_POST);
	$_GET = BH_Common::StripSlashes($_GET);
}

App::$settingData['LevelArray'] = array(0 => App::$lang['TXT_NOT_MEMBER'], 1 => App::$lang['TXT_MEMBER'], 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8,
	9 => 9, 10 => 10, 15 => App::$lang['TXT_NOT_MANAGER'], 18 => App::$lang['TXT_ADMIN'], 20 => App::$lang['TXT_SUPER_ADMIN']);
App::$settingData['noext'] = array('php', 'htm', 'html', 'cfg', 'inc', 'phtml', 'php5', 'asp', 'jsp');
App::$settingData['IMAGE_EXT'] = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
App::$settingData['POSSIBLE_EXT'] = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'zip', '7z', 'gz', 'xz', 'tar', 'xls',
	'xlsx', 'ppt', 'doc', 'hwp', 'pdf', 'docx', 'pptx', 'avi', 'mov', 'mkv', 'mpg', 'mpeg', 'wmv', 'asf', 'asx', 'flv',
	'm4v', 'mp4', 'mp3', 'txt');
App::$settingData['iframePossibleUrl'] = array('www.youtube.com');

if(_DEVELOPERIS === true){
	if(!file_exists(\Paths::DirOfData()) || !is_dir(\Paths::DirOfData())) @mkdir(\Paths::DirOfData(), 0755, true);
	require _COMMONDIR . '/HtmlConvert.php';
	BH\BHCss\BHCss::setNL(true);
	require _COMMONDIR . '/BH_HtmlCreate.class.php';
}

if(_CREATE_HTML_ALL === true){
	delTree(\Paths::DirOfHtml());
	ReplaceHTMLAll(\Paths::DirOfSkin(), \Paths::DirOfHtml());
	ReplaceBHCSSALL(\Paths::DirOfHtml(), \Paths::DirOfHtml());
	ReplaceBHCSSALL(\Paths::DirOfSkin(), \Paths::DirOfHtml());
}

define('ENG_NUM', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
define('ENG_UPPER', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
define('ENG_LOWER', 'abcdefghijklmnopqrstuvwxyz');

// -------------------------------------
//
//		Mobile Check
//
if(isset($_SERVER['HTTP_USER_AGENT'])){
	if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($_SERVER['HTTP_USER_AGENT'], 0, 4))){
		define('_MOBILEIS', true);
	}
	else define('_MOBILEIS', false);
}
else define('_MOBILEIS', false);

// -------------------------------------
//
//		기본 함수
//
function my_escape_string($str){
	if(is_array($str)) return array_map('my_escape_string', $str);
	else return mysqli_real_escape_string(DB::SQL()->GetConn(), trim($str));
}

function URLReplace($url, $msg = '', $data = '', $exitIs = true, $redirect = false){
	if(_JSONIS === true) JSON($url != '-1', $msg, $data);

	echo '<script>';
	if($url == '-1') echo 'window.onpopstate = function(){
			if(!document.body || document.body.innerHTML.replace(/\s/g, \'\') === \'\') location.reload();
		};';
	if($msg) echo 'alert(\'' . addslashes($msg) . '\');';
	if($url == '-1') echo 'history.go(-1);';
	else{
		$url = str_replace(' ', '%20', $url);
		if($redirect) echo 'location.href = \'' . addslashes($url) . '\';';
		else echo 'location.replace(\'' . addslashes($url) . '\');';
	}
	echo '</script>';
	if($exitIs) exit;
}

function URLRedirect($url, $msg = '', $data = '', $exitIs = true){
	URLReplace($url, $msg, $data, $exitIs, true);
}

function PhoneNumber($num){
	$num = preg_replace('/[^0-9]/', '', str_replace('-', '', $num));
	if(substr($num, 0, 2) == '02'){
		if(strlen($num) >= 10){
			return substr($num, 0, 2) . '-' . substr($num, 2, 4) . '-' . substr($num, 6);
		}
		else if(strlen($num) == 9){
			return substr($num, 0, 2) . '-' . substr($num, 2, 3) . '-' . substr($num, 5);
		}
		else return $num;
	}
	else if(strlen($num) >= 11) return substr($num, 0, 3) . '-' . substr($num, 3, 4) . '-' . substr($num, 7);
	else if(strlen($num) == 10){
		return substr($num, 0, 3) . '-' . substr($num, 3, 3) . '-' . substr($num, 6);
	}
	else return $num;
}

function KrDate($date, $opt = 'ymdhis', $hourView = 0){
	if($hourView){
		$t = time() - strtotime($date);
		if($t < 60) return $t . '초 전';
		else if($t < 3600) return floor($t / 60) . '분 전';
		else if($t < 3600 * $hourView) return floor($t / 3600) . '시간 전';
	}
	$opt = strtolower($opt);
	$res = (strpos($opt, 'y') !== false ? substr($date, 0, 4) . '년 ' : '') . (strpos($opt, 'm') !== false ? (int)substr($date, 5, 2) . '월 ' : '') . (strpos($opt, 'd') !== false ? (int)substr($date, 8, 2) . '일 ' : '') . (strpos($opt, 'h') !== false ? (int)substr($date, 11, 2) . '시 ' : '') . (strpos($opt, 'i') !== false ? (int)substr($date, 14, 2) . '분 ' : '') . (strpos($opt, 's') !== false ? (int)substr($date, 17, 2) . '초 ' : '');
	return trim($res);
}

function DotDate($dt, $length = 10){
	return str_replace('-', '.', substr($dt, 0, $length));
}

function AutoLinkText($text){
	$text = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" target=\"_blank\">$3</a>", $text);
	$text = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" target=\"_blank\">$3</a>", $text);
	$text = preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\" target=\"_blank\">$2@$3</a>", $text);
	return ($text);
}

function OptionAreaNumber($num = ''){
	$numbers = array();
	$numbers[] = array('num' => '02', 'loc' => '서울');
	$numbers[] = array('num' => '031', 'loc' => '경기');
	$numbers[] = array('num' => '051', 'loc' => '부산');
	$numbers[] = array('num' => '053', 'loc' => '대구');
	$numbers[] = array('num' => '032', 'loc' => '인천');
	$numbers[] = array('num' => '062', 'loc' => '광주');
	$numbers[] = array('num' => '042', 'loc' => '대전');
	$numbers[] = array('num' => '052', 'loc' => '울산');
	$numbers[] = array('num' => '044', 'loc' => '세종');
	$numbers[] = array('num' => '033', 'loc' => '강원');
	$numbers[] = array('num' => '043', 'loc' => '충북');
	$numbers[] = array('num' => '041', 'loc' => '충남');
	$numbers[] = array('num' => '063', 'loc' => '전북');
	$numbers[] = array('num' => '061', 'loc' => '전남');
	$numbers[] = array('num' => '054', 'loc' => '경북');
	$numbers[] = array('num' => '055', 'loc' => '경남');
	$numbers[] = array('num' => '064', 'loc' => '제주');

	$str = '';
	foreach($numbers as $item){
		$str .= '<option value="' . $item['num'] . '"' . ($num == $item['num'] ? ' selected="selected"' : '') . '>' . $item['num'] . '(' . $item['loc'] . ')</option>';
	}
	return $str;
}

function OptionPhoneFirstNumber($find = ''){
	$numbers = array();
	$numbers[] = '010';
	$numbers[] = '011';
	$numbers[] = '016';
	$numbers[] = '017';
	$numbers[] = '019';
	$str = '';
	foreach($numbers as $item){
		$str .= '<option value="' . $item . '"' . ($find == $item ? ' selected="selected"' : '') . '>' . $item . '</option>';
	}
	return $str;
}

function OptionEmailAddress($find = ''){
	if(isset(App::$settingData['optionEmail'])) $addr = App::$settingData['optionEmail'];
	else{
		$addr = array(
			'naver.com',
			'gmail.com',
			'hanmail.net'
		);
	}

	$str = '';
	foreach($addr as $item){
		$str .= '<option value="' . $item . '"' . ($find == $item ? ' selected="selected"' : '') . '>' . $item . '</option>';
	}
	return $str;
}

/**
 * @param array $OptionValues
 * @param string $SelectValue
 * @param bool $noOptValue
 * @return string
 */
function SelectOption($OptionValues, $SelectValue = '', $noOptValue = false){
	$str = '';
	if(!isset($OptionValues) || !is_array($OptionValues)) return $str;
	foreach($OptionValues as $k => $v){
		$key = $noOptValue ? $v : $k;
		$str .= '<option' . ($noOptValue ? '' : ' value="' . $k . '"') . ($SelectValue === (string)$key ? ' selected="selected"' : '') . '>' . GetDBText($v) . '</option>';
	}
	return $str;
}

/**
 * @param string $name
 * @param array $OptionValues
 * @param string $SelectValue
 * @param string $class
 * @return string
 */
function InputRadio($name, $OptionValues, $SelectValue = '', $class = ''){
	if(is_null($SelectValue)) $SelectValue = '';
	$str = '';
	if(!isset($OptionValues) || !is_array($OptionValues)) return $str;
	foreach($OptionValues as $k => $v) $str .= '<label class="radio' . ($class ? ' ' . $class : '') . '"><input type="radio" name="' . $name . '" value="' . $k . '" class="' . $class . '"' . (isset($SelectValue) && $SelectValue === (string)$k ? ' checked' : '') . '><i></i><span>' . $v . '</span></label>';
	return $str;
}

/**
 * @param string $name
 * @param array $OptionValues
 * @param array $SelectValue
 * @param string $class
 * @return string
 */
function InputCheckbox($name, $OptionValues, $SelectValue = array(), $class = ''){
	if(is_null($SelectValue)) $SelectValue = array();
	if(!is_array($SelectValue)) $SelectValue = array($SelectValue);
	$str = '';
	if(!isset($OptionValues) || !is_array($OptionValues)) return $str;
	foreach($OptionValues as $k => $v) $str .= '<label class="checkbox' . ($class ? ' ' . $class : '') . '"><input type="checkbox" name="' . $name . '" value="' . $k . '" class="' . $class . '"' . (in_array($k, $SelectValue) ? ' checked' : '') . '><i></i><span>' . $v . '</span></label>';
	return $str;
}

// Cut title length
function StringCut($title, $length, $last = '...'){
	if(mb_strlen($title, 'utf-8') > $length) $result_title = mb_substr($title, 0, $length, 'utf-8') . $last;
	else $result_title = $title;
	Return $result_title;
}

/**
 * @param $month 월 (숫자 두자리 또는 '2015-10-11'형식으로 $year 생략)
 * @param false|int $year 년
 * @return bool|false|string
 */
function GetLastDay($month, $year = false){
	if(!strlen($month)){
		echo 'Error';
		exit;
	}

	if($year === false){
		$temp = explode('-', $month);
		if(sizeof($temp) < 2) return false;
		$year = $temp[0];
		$month = $temp[1];
	}
	return date('t', mktime(0, 0, 0, $month, 1, $year));
}

/**
 * @param $month 월 (숫자 두자리 또는 '2015-10-11'형식으로 $year 생략)
 * @param false|int $year 년
 * @return bool|false|string
 */
function GetBeforeMonth($month, $year = false){
	if(!strlen($month)){
		echo 'Error';
		exit;
	}

	if($year === false){
		$temp = explode('-', $month);
		if(sizeof($temp) < 2) return false;
		$year = $temp[0];
		$month = $temp[1];
	}
	$month -= 1;
	if($month < 1){
		$year -= 1;
		$month = 12;
	}
	return sprintf('%04d-%02d', $year, $month);
}

/**
 * @param $month 월 (숫자 두자리 또는 '2015-10-11'형식으로 $year 생략)
 * @param false|int $year 년
 * @return bool|false|string
 */
function GetNextMonth($month, $year = false){
	if(!strlen($month)){
		echo 'Error';
		exit;
	}

	if($year === false){
		$temp = explode('-', $month);
		if(sizeof($temp) < 2) return false;
		$year = $temp[0];
		$month = $temp[1];
	}
	$month += 1;
	if($month > 12){
		$year += 1;
		$month = 1;
	}
	return sprintf('%04d-%02d', $year, $month);
}

function Download($path, $fname){
	$temp = explode('/', $path);
	if(!$fname) $fname = $temp[sizeof($temp) - 1];
	if(!App::$settingData['viewMobile']) $fname = mb_convert_encoding($fname, 'cp949', 'utf-8');

	App::$layout = null;

	ignore_user_abort(true);
	set_time_limit(0); // disable the time limit for this script


	$dl_file = filter_var($path, FILTER_SANITIZE_URL); // Remove (more) invalid characters
	if(!file_exists($dl_file)) URLReplace(-1, App::$lang['TXT_FILE_NOT_EXIST']);

	if($fd = fopen($dl_file, "r")){
		$fsize = filesize($dl_file);
		$path_parts = pathinfo($dl_file);
		$ext = strtolower($path_parts["extension"]);
		switch($ext){
			case "pdf":
				header("Content-type: application/pdf");
				header("Content-Disposition: attachment; filename=\"" . $fname . "\""); // use 'attachment' to force a file download
			break;
			// add more headers for other content types here
			default;
				header("Content-type: application/octet-stream");
				header('Content-Description: File Download');
				header('Content-Disposition: attachment; filename="' . $fname . '"');
				header('Content-Transfer-Encoding: binary');
			break;
		}
		header("Content-length: $fsize");
		header("Cache-control: private"); //use this to open files directly
		while(!feof($fd)){
			$buffer = fread($fd, 2048);
			echo $buffer;
		}
	}
	fclose($fd);
	exit;
}

function ResizeImage($path, $width, $noext = ''){
	if(!strlen($noext)) $noext = (isset(App::$settingData['noImg']) && strlen(App::$settingData['noImg'])) ? App::$settingData['noImg'] : _NO_IMG;
	if(!file_exists(\Paths::DirOfUpload() . $path) || is_dir(\Paths::DirOfUpload() . $path)) return $noext ? Paths::Url() . $noext : '';
	$temp = explode('/', $path);
	$temp[sizeof($temp) - 1] = $width . '_' . $temp[sizeof($temp) - 1];
	$new = implode('/', $temp);
	if(!file_exists(\Paths::DirOfUpload() . $new)){
		_ModelFunc::Thumbnail(\Paths::DirOfUpload() . $path, \Paths::DirOfUpload() . $new, $width);
	}
	return Paths::UrlOfUpload() . $new;
}

function UnlinkImage($file){
	$temp = explode('/', $file);
	$temp[sizeof($temp) - 1] = '*_' . $temp[sizeof($temp) - 1];
	@unlink($file);
	array_map('unlink', glob(implode('/', $temp)));
}

function DeleteOldTempFiles($tempfile_path, $time){
	if(is_dir($tempfile_path)) if($dh = opendir($tempfile_path)){
		while(($file = readdir($dh)) !== false){
			if($file != '.' && $file != '..'){
				$dest_path = $tempfile_path . '/' . $file;
				if(is_dir($dest_path)) DeleteOldTempFiles($dest_path, $time);
				else{
					$fat = filemtime($dest_path);
					if($fat < $time) @unlink($dest_path);
				}
			}
		}
		closedir($dh);
	}
}

function ToInt($s){
	if(!$s) return 0;
	$s = (string)$s;
	return ($s[0] == '-' ? $s[0] : '') . preg_replace('/[^0-9]/', '', $s);
}

function ToFloat($s){
	if(!$s) return 0;
	$s = (string)$s;
	return ($s[0] == '-' ? $s[0] : '') . preg_replace('/[^0-9\.]/', '', $s);
}

function RemoveScriptTag($str){
	return preg_replace(array('/\<\/*\s*(script|form|input|select|button|textarea).*?\>/is', '/\<\s*(\S+?)(\s+[^\>]*\s+on[a-zA-Z]+|\s+on[a-zA-Z]+)\s*\=[^\>]*?\>/is', '/\<a[^\>]*?src\s*\=\s*[^\>]*?javascript\s*\:[^\>]*\>/'), array('', '<$1>', ''), $str);
}

function RemoveIFrame($str){
	return preg_replace_callback('/\<\s*iframe\s*(.*?)\>([^\<]*)(\<\/\s*iframe\s*\>|)/is', function($matches){
		if(is_array($matches) && sizeof($matches) > 1){
			preg_replace_callback('/.*?src\s*\=\s*[\'\"]*(.*?)[\'\"\s].*?/is', function($matches2) use(&$matches){
				$r = false;
				foreach(App::$settingData['iframePossibleUrl'] as $v){
					if(strpos($matches2[1], $v) !== false) $r = true;
				}
				if(!$r) $matches[0] = '';
				return '';
			}, $matches[1]);
			return $matches[0];
		}
		else return '';
		//
		//if(substr($matches[1]) === 'https://www.youtube.com')
	}, $str);
}

function SetDBTrimText($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v) $v = SetDBTrimText($v);
		return $txt;
	}
	return 'UNHEX(\'' . bin2hex(trim($txt)) . '\')';
}

function SetDBText($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v) $v = SetDBText($v);
		return $txt;
	}
	return 'UNHEX(\'' . bin2hex($txt) . '\')';
}

function SetDBQuot($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v) $v = SetDBQuot($v);
		return $txt;
	}
	return chr(39) . $txt . chr(39);
}

function ValidateInt($txt){
	$res = new \BH_Result();
	$res->result = true;
	if(is_array($txt)){
		foreach($txt as $v){
			$r = ValidateInt($v);
			if(!$r->result) $res = $r;
		}
		return $res;
	}

	$val = ToInt($txt);
	if(!strlen($txt)){
		$res->result = false;
		$res->message = App::$lang['TXT_EMPTY_NUMBER'];
	}
	else if((string)$val !== (string)$txt){
		$res->result = false;
		$res->message = App::$lang['TXT_ONLY_NUMBER_NOT_CHARACTER'];
	}
	return $res;
}

function SetDBInt($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v) $v = SetDBInt($v);
		return $txt;
	}
	if(!strlen($txt)) URLReplace('-1', App::$lang['TXT_EMPTY_NUMBER']);
	$val = ToInt($txt);
	if((string)$val !== (string)$txt) URLReplace('-1', App::$lang['TXT_ONLY_NUMBER_NOT_CHARACTER']);
	return $txt;
}

function ValidateFloat($txt){
	$res = new \BH_Result();
	$res->result = true;
	if(is_array($txt)){
		foreach($txt as $v){
			$r = ValidateFloat($v);
			if(!$r->result) $res = $r;
		}
		return $res;
	}

	$val = ToFloat($txt);
	if(!strlen($txt)){
		$res->result = false;
		$res->message = App::$lang['TXT_EMPTY_NUMBER'];
	}
	else if((string)$val !== (string)$txt){
		$res->result = false;
		$res->message = App::$lang['TXT_ONLY_NUMBER_NOT_CHARACTER'];
	}
	return $res;
}

function SetDBFloat($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v) $v = SetDBFloat($v);
		return $txt;
	}

	if(!strlen($txt)){
		if(_DEVELOPERIS === true) PrintError(App::$lang['TXT_EMPTY_NUMBER']);
		URLReplace('-1', App::$lang['TXT_EMPTY_NUMBER']);
	}

	$val = ToFloat($txt);
	if((string)$val !== (string)$txt){
		if(_DEVELOPERIS === true) PrintError(App::$lang['TXT_ONLY_NUMBER_NOT_CHARACTER']);
		URLReplace('-1', App::$lang['TXT_ONLY_NUMBER_NOT_CHARACTER']);
	}

	return $val;
}

function PrintError($message){
	if(_DEVELOPERIS !== true) exit;
	function GetArrayData($arr){
		if(is_array($arr)){
			foreach($arr as $k => $v) $arr[$k] = GetArrayData($v);
			return $arr;
		}
		if(is_object($arr)) return get_class($arr);
		return $arr;
	}
	echo '<b style="color:#c00;">' . (is_array($message) ? implode('<br>', $message) : $message) . '</b><br>';
	$d_b = phpversion() < 5.6 ? debug_backtrace() : debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);
	for($k = 1; isset($d_b[$k]) && $k < 4;$k++){
		echo '<h1>#'. $k.'</h1>';
		echo '<b>file : ' . $d_b[$k]['file'].'('.$d_b[$k]['line'].')</b><br>';
		if(isset($d_b[$k]['class'])){
			echo 'Class : ' . $d_b[$k]['class'].'<br>';
		}
		$argVal = '';
		if(isset($d_b[$k]['args'])){
			$argVal = GetArrayData($d_b[$k]['args']);
			if(is_array($argVal)) $argVal = implode(',', $argVal);
			else $argVal = print_r($d_b[$k]['args'], true);
		}
		if(isset($d_b[$k]['function'])) echo 'Function : ' . $d_b[$k]['function'].'(' . $argVal . ')<br>';
		if(isset($d_b[$k]['class']) && in_array($d_b[$k]['class'], array('BH_DB_Get', 'BH_DB_GetList', 'BH_DB_GetListWithPage', 'BH_DB_Update', 'BH_DB_Insert', 'BH_DB_Delete')) && $d_b[$k]['object']){
			echo '<br><b style="color:#06c;">';
			$d_b[$k]['object']->PrintTest();
			echo '</b><br>';
		}

		echo '<br>';
	}
	exit;
}

function GetDBText($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v) $v = GetDBText($v);
		return $txt;
	}
	else return str_replace(array('\'', '"'), array('&#39;', '&quot;'), htmlspecialchars($txt));
}

function GetDBRaw($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v) $v = GetDBRaw($v);
		return $txt;
	}
	else return RemoveScriptTag($txt);
}

function v($txt){
	return GetDBText($txt);
}

function vr($txt){
	return GetDBRaw($txt);
}

function vb($txt){
	return nl2br(GetDBText($txt));
}

function my_bcmod($x, $y){
	$take = 5;
	$mod = '';
	do{
		$a = (int)$mod . substr($x, 0, $take);
		$x = substr($x, $take);
		$mod = $a % $y;
	}while(strlen($x));

	return (int)$mod;
}

function toBase($num, $b = 62, $base = ENG_NUM){
	if(_USE_BC_TO === true) return bc_toBase($num, $b, $base);
	if(!isset($num) || !strlen($num)) return '';
	$r = my_bcmod($num, $b);
	$res = $base[$r];
	$q = floor($num / $b);
	while($q){
		$r = my_bcmod($q, $b);
		$q = floor($q / $b);
		$res = $base[$r] . $res;
	}
	return $res;
}

function to10($num, $b = 62, $base = ENG_NUM){
	if(_USE_BC_TO === true) return bc_to10($num, $b, $base);
	if(!isset($num) || !strlen($num)) return '';
	$limit = strlen($num);
	$res = strpos($base, $num[0]);
	for($i = 1; $i < $limit; $i++) $res = $b * $res + strpos($base, $num[$i]);
	return $res;
}

function bc_toBase($num, $b = 62, $base = ENG_NUM){
	if(!isset($num) || !strlen($num)) return '';
	$r = bcmod($num, $b);
	$res = $base[$r];
	$q = bcdiv($num, $b);
	while($q){
		$r = bcmod($q, $b);
		$q = bcdiv($q, $b);
		$res = $base[$r] . $res;
	}
	return $res;
}

function bc_to10($num, $b = 62, $base = ENG_NUM){
	if(!isset($num) || !strlen($num)) return '';
	$limit = strlen($num);
	$res = strpos($base, $num[0]);
	for($i = 1; $i < $limit; $i++) $res = bcadd(bcmul($b, $res), strpos($base, $num[$i]));
	return $res;
}

function JSON($bool, $message = '', $data = array(), $exitIs = true){
	header('Content-Type: application/json');
	echo json_encode(array('result' => $bool, 'message' => $message, 'data' => $data, 'common' => (isset(App::$settingData['jsonCommonData']) ? App::$settingData['jsonCommonData'] : null)));
	if($exitIs) exit;
}

function aes_encrypt($plaintext, $password){
	return strtoupper(bin2hex(@openssl_encrypt($plaintext, 'aes-128-cbc', $password, true, '')));
}

function aes_decrypt($ciphertext, $password){
	return @openssl_decrypt(hex2bin($ciphertext), 'aes-128-cbc', $password, true, '');
}

function delTree($dir){
	if(!is_dir($dir)) return false;
	$files = array_diff(scandir($dir), array('.', '..'));
	foreach($files as $file){
		(is_dir($dir . '/' . $file)) ? delTree($dir . '/' . $file) : unlink($dir . '/' . $file);
	}
	return rmdir($dir);
}

function findDelTree($ConnName, $dir){
	$path = \Paths::DirOfData() . '/temp/' . $ConnName . '/';
	if(!file_exists($path) && !is_dir($path)) mkdir($path, 0755, true);
	$files = array_diff(scandir($path), array('.', '..'));
	foreach($files as $file){
		if(is_dir($path . $file) && strpos($file, $dir) !== false) delTree($path . $file);
	}
}

function StrToSql($args){
	$validateOk = new \BH_Result();
	$validateOk->result = true;
	if(!is_array($args)) $args = func_get_args();

	$n = sizeof($args);
	if(!$n) return false;
	if($n == 1) return $args[0];
	else{
		$p = -1;
		$w = $args[0];
		for($i = 1; $i < $n; $i++){
			$p = strpos($w, '%', $p + 1);
			$find = false;
			while(!$find && $p !== false && $p < strlen($w)){
				$t = $w[$p + 1];
				switch($t){
					case 's':
						$t = is_array($args[$i]) ? implode(',', SetDBText($args[$i])) : SetDBText($args[$i]);
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					break;
					case 'f':
						$res = ValidateFloat($args[$i]);
						if(!$res->result) $validateOk = $res;
						$t = is_array($args[$i]) ? implode(',', $args[$i]) : $args[$i];
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					break;
					case 'd':
						$res = ValidateInt($args[$i]);
						if(!$res->result) $validateOk = $res;
						$t = is_array($args[$i]) ? implode(',', $args[$i]) : $args[$i];
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					break;
					case '1':
						$t = is_array($args[$i]) ? implode(',', $args[$i]) : $args[$i];
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					break;
					default:
						$p = strpos($w, '%', $p + 1);
					break;
				}
			}
		}
		$w = str_replace(array('%\s', '%\f', '%\d', '%\1', '%\t'), array('%s', '%f', '%d', '%1', '%t'), $w);
		if($validateOk->result) return $w;
		else
			URLReplace(-1, $validateOk->message . (_DEVELOPERIS === true ? '[' . $w . ']' : ''));
	}
}

function SqlPassword($input){
	return '*' . strtoupper(sha1(sha1($input, true)));
}

function SqlOldPassword($password) {
	$nr=0x50305735;
	$nr2=0x12345671;
	$add=7;
	$charArr = preg_split("//", $password);
	foreach ($charArr as $char) {
		if (($char == '') || ($char == ' ') || ($char == '\t')) continue;
		$charVal = ord($char);
		$nr ^= ((($nr & 63) + $add) * $charVal) + ($nr << 8);
		$nr2 += ($nr2 << 8) ^ $nr;
		$add += $charVal;
	}
	return sprintf("%08x%08x", ($nr & 0x7fffffff), ($nr2 & 0x7fffffff));
}

function _password_hash($str){
	if(_USE_DB_PASSWORD === true) return SqlPassword($str);
	if(_USE_OLD_PASSWORD === true) SqlOldPassword($str);
	if(phpversion() < '5.3.7') return hash('sha256', hash('sha512', sha1(sha1($str, true))));
	else if(phpversion() < '5.5') require_once _COMMONDIR . '/password.php';
	return password_hash(hash('sha256', $str), PASSWORD_BCRYPT);
}

function _password_verify($str, $hash){
	if(_USE_DB_PASSWORD === true) return SqlPassword($str) === $hash;
	if(_USE_OLD_PASSWORD === true) return SqlOldPassword($str) === $hash;
	if(phpversion() < '5.3.7') return $hash === hash('sha256', hash('sha512', sha1(sha1($str, true))));
	else if(phpversion() < '5.5') require_once _COMMONDIR . '/password.php';
	if(password_verify(hash('sha256', $str), $hash)) return true;
	return false;
}

function modifyFileTime($file, $group = 'default'){
	if(!file_exists($file)) return false;
	if(!is_dir(\Paths::DirOfData())) @mkdir(\Paths::DirOfData(), 0777, true);
	$path = \Paths::DirOfData() . '/fileModTime.php';
	if(file_exists($path)) require_once $path;

	$t = filemtime($file);

	if(!isset($GLOBALS['fileModTime']) || !isset($GLOBALS['fileModTime'][$group]) || !isset($GLOBALS['fileModTime'][$group][$file]) || $t != $GLOBALS['fileModTime'][$group][$file]){
		$GLOBALS['fileModTime'][$group][$file] = $t;
		foreach($GLOBALS['fileModTime'] as $k => $v){
			foreach($v as $k2 => $v2){
				if(!file_exists($v2)) unset($GLOBALS[$k][$k2]);
			}
		}
		$txt = '<?php $GLOBALS[\'fileModTime\'] = ' . var_export($GLOBALS['fileModTime'], true) . ';';
		file_put_contents($path, $txt);
		return true;
	}
	return false;
}

function &Post($param){
	if(!isset(App::$settingData['_BH_PostData'][$param])){
		App::$settingData['_BH_PostData'][$param] = true;
		if(!isset($_POST[$param])) $_POST[$param] = null;
		else if(is_string($_POST[$param])) $_POST[$param] = trim($_POST[$param]);
	}
	return $_POST[$param];
}

function EmptyPost($param){
	if(!isset($_POST[$param])) return true;
	else if(is_string($_POST[$param])) return (strlen(trim($_POST[$param])) === 0);
	else if(is_array($_POST[$param])) return (sizeof($_POST[$param]) === 0);
	return false;
}

function &Get($param){
	if(!isset(App::$settingData['_BH_GetData'][$param])){
		App::$settingData['_BH_GetData'][$param] = true;
		if(!isset($_GET[$param])) $_GET[$param] = null;
		else if(is_string($_GET[$param])) $_GET[$param] = trim($_GET[$param]);
	}
	return $_GET[$param];
}

function EmptyGet($param){
	if(!isset($_GET[$param])) return true;
	else if(is_string($_GET[$param])) return (strlen(trim($_GET[$param])) === 0);
	else if(is_array($_GET[$param])) return (sizeof($_GET[$param]) === 0);
	return false;
}

function Session($param){
	if(!isset($_SESSION[$param])) return null;
	return $_SESSION[$param];
}

function EmptySession($param){
	if(!isset($_SESSION[$param])) return true;
	else if(is_string($_SESSION[$param])) return (strlen(trim($_SESSION[$param])) === 0);
	else if(is_array($_SESSION[$param])) return (sizeof($_SESSION[$param]) === 0);
	return false;
}

function CustomText($str){
	return preg_replace('/\<font[^\>]*?\>\s*\<\/font\>/is', '', $str);
}

function IsImageFileName($path){
	$p = explode('.', $path);
	$ext = end($p);
	return in_array(strtolower($ext), array('png','jpg','jpeg','gif'));
}


function _SecretKeyByFile($filePath){
	if(!file_exists($filePath)){
		$ch = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz@.*^_-!';
		$key = '<?php //';
		for($i = 0; $i < 20; $i++){
			$key .= $ch[mt_rand(0, strlen($ch) - 1)];
		}
		file_put_contents($filePath, $key);
	}
	return substr(file_get_contents($filePath), 8);
}

function GetPathFromRoot($dir){
	$dir = str_replace('\\', '/', $dir);
	$dir2 = Paths::DirOfHtml();
	if(substr($dir, 0, strlen($dir2)) !== $dir2){
		if(_DEVELOPERIS === true){
			URLRedirect(-1, '경로가 다릅니다.');
			exit;
		}
	}
	return substr($dir, strlen($dir2));
}

require _DIR . '/Custom/MyLib.php';
