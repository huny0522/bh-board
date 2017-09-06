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

}

class BH_InsertResult
{

	public $result = false;
	public $id = null;
	public $message = '';

}

define('_POSTIS', $_SERVER['REQUEST_METHOD'] == 'POST');
define('_AJAXIS', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
define('_JSONIS', strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false);

require _COMMONDIR . '/BH_PDO.class.php';
require _COMMONDIR . '/BH_Application.class.php';
require _COMMONDIR . '/BH_Model.class.php';
require _COMMONDIR . '/BH_Common.class.php';
require _COMMONDIR . '/BHCss/core/BHCss.php';

App::$SettingData['LevelArray'] = array(0 => '비회원', 1 => '일반회원', 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8,
	9 => 9, 10 => 10, 15 => '매니저', 18 => '관리자', 20 => '최고관리자');
App::$SettingData['noext'] = array('php', 'htm', 'html', 'cfg', 'inc', 'phtml', 'php5', 'asp', 'jsp');
App::$SettingData['IMAGE_EXT'] = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
App::$SettingData['POSSIBLE_EXT'] = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'zip', '7z', 'gz', 'xz', 'tar', 'xls',
	'xlsx', 'ppt', 'doc', 'hwp', 'pdf', 'docx', 'pptx', 'avi', 'mov', 'mkv', 'mpg', 'mpeg', 'wmv', 'asf', 'asx', 'flv',
	'm4v', 'mp4', 'mp3');

if(_DEVELOPERIS === true){
	if(!file_exists(_DATADIR) || !is_dir(_DATADIR)) @mkdir(_DATADIR, 0755, true);
	require _COMMONDIR . '/HtmlConvert.php';
	BH\BHCss\BHCss::setNL(true);
	require _COMMONDIR . '/BH_HtmlCreate.class.php';
}

if(_CREATE_HTML_ALL === true){
	delTree(_HTMLDIR);
	ReplaceHTMLAll(_SKINDIR, _HTMLDIR);
	ReplaceBHCSSALL(_HTMLDIR, _HTMLDIR);
	ReplaceBHCSSALL(_SKINDIR, _HTMLDIR);
}

define('ENG_NUM', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
define('ENG_UPPER', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
define('ENG_LOWER', 'abcdefghijklmnopqrstuvwxyz');

// -------------------------------------
//
//		Mobile Check
//
$useragent = $_SERVER['HTTP_USER_AGENT'];
if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))){
	define('_MOBILEIS', true);
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
	if($msg) echo 'alert(\'' . $msg . '\');';
	if($url == '-1') echo 'history.go(-1);';
	else{
		$url = str_replace(' ', '%20', $url);
		if($redirect) echo 'location.href = \'' . $url . '\';';
		else echo 'location.replace(\'' . $url . '\');';
	}
	echo '</script>';
	if($exitIs) exit;
}

function URLRedirect($url, $msg = '', $data = '', $exitIs = true){
	URLReplace($url, $msg, $data, $exitIs, true);
}

function PhoneNumber($num){
	$num = str_replace('-', '', $num);
	if(strlen($num) == 11) return substr($num, 0, 3) . '-' . substr($num, 3, 4) . '-' . substr($num, 7, 4);
	else
		return substr($num, 0, 3) . '-' . substr($num, 3, 3) . '-' . substr($num, 6, 4);
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

function DotDate($dt){
	return str_replace('-', '.', substr($dt, 0, 10));
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
	$addr = array();
	$addr[] = 'naver.com';
	$addr[] = 'gmail.com';
	$addr[] = 'hanmail.net';

	$str = '';
	foreach($addr as $item){
		$str .= '<option value="' . $item . '"' . ($find == $item ? ' selected="selected"' : '') . '>' . $item . '</option>';
	}
	return $str;
}

/**
 * @param array $OptionValues
 * @param string $SelectValue
 * @return string
 */
function SelectOption($OptionValues, $SelectValue = ''){
	$str = '';
	if(!isset($OptionValues) || !is_array($OptionValues)) return $str;
	foreach($OptionValues as $k => $v){
		$str .= '<option value="' . $k . '"' . (isset($SelectValue) && $SelectValue === (string)$k ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}
	return $str;
}

/**
 * @param string $name
 * @param array $OptionValues
 * @param string $SelectValue
 * @return string
 */
function InputRadio($name, $OptionValues, $SelectValue = ''){
	if(is_null($SelectValue)) $SelectValue = '';
	$str = '';
	if(!isset($OptionValues) || !is_array($OptionValues)) return $str;
	foreach($OptionValues as $k => $v) $str .= '<label><input type="radio" name="' . $name . '" value="' . $k . '"' . (isset($SelectValue) && $SelectValue === (string)$k ? ' checked' : '') . '>' . $v . '</label>';
	return $str;
}

// Cut title length
function StringCut($title, $length, $last = '...'){
	if(mb_strlen($title, 'utf-8') > $length) $result_title = mb_substr($title, 0, $length, 'utf-8') . $last;
	else $result_title = $title;
	Return $result_title;
}

/**
 * @params int $month 월 (숫자 두자리 또는 '2015-10-11'형식으로 $year 생략)
 * @params int $year 년
 *
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

function Download($path, $fname){
	$temp = explode('/', $path);
	if(!$fname) $fname = $temp[sizeof($temp) - 1];

	App::$Layout = null;

	ignore_user_abort(true);
	set_time_limit(0); // disable the time limit for this script


	$dl_file = filter_var($path, FILTER_SANITIZE_URL); // Remove (more) invalid characters
	if(!file_exists($dl_file)) URLReplace(-1, '파일이 존재하지 않습니다.');

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

function ResizeImage($path, $width, $noext = _NO_IMG){
	if(!file_exists(_UPLOAD_DIR . $path)) return $noext ? _URL . $noext : _UPLOAD_URL . $path;
	$temp = explode('/', $path);
	$temp[sizeof($temp) - 1] = $width . '_' . $temp[sizeof($temp) - 1];
	$new = implode('/', $temp);
	if(!file_exists(_UPLOAD_DIR . $new)){
		require_once _COMMONDIR . '/FileUpload.php';
		Thumbnail(_UPLOAD_DIR . $path, _UPLOAD_DIR . $new, $width);
	}
	return _UPLOAD_URL . $new;
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
	return (substr($s, 0, 1) == '-' ? substr($s, 0, 1) : '') . preg_replace('/[^0-9]/', '', $s);
}

function ToFloat($s){
	if(!$s) return 0;
	return (substr($s, 0, 1) == '-' ? substr($s, 0, 1) : '') . preg_replace('/[^0-9\.]/', '', $s);
}

function RemoveScriptTag($str){
	return preg_replace(array('/\<\/*\s*(script|form|input|select|button|textarea)(.*?)\>/is',
		'/\<(.*?)(\s+(on|e-).*?)\>/is'), array('', '<$1>'), $str);
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
		$res->message = '숫자값이 비어있습니다.';
	}
	else if((string)$val !== (string)$txt){
		$res->result = false;
		$res->message = '숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.';
	}
	return $res;
}

function SetDBInt($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v) $v = SetDBInt($v);
		return $txt;
	}
	if(!strlen($txt)) URLReplace('-1', '숫자값이 비어있습니다.');
	$val = ToInt($txt);
	if((string)$val !== (string)$txt) URLReplace('-1', '숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.');
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
		$res->message = '숫자값이 비어있습니다.';
	}
	else if((string)$val !== (string)$txt){
		$res->result = false;
		$res->message = '숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.';
	}
	return $res;
}

function SetDBFloat($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v) $v = SetDBFloat($v);
		return $txt;
	}

	if(!strlen($txt)) URLReplace('-1', '숫자값이 비어있습니다.');

	$val = ToFloat($txt);
	if((string)$val !== (string)$txt) URLReplace('-1', '숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.');

	return $val;
}

function GetDBText($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v) $v = GetDBText($v);
		return $txt;
	}
	else return htmlspecialchars($txt);
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
	if(!isset($num) || !strlen($num)) return '';
	$limit = strlen($num);
	$res = strpos($base, $num[0]);
	for($i = 1; $i < $limit; $i++) $res = $b * $res + strpos($base, $num[$i]);
	return $res;
}

function JSON($bool, $message = '', $data = array(), $exitIs = true){
	echo json_encode(array('result' => $bool, 'message' => $message, 'data' => $data));
	if($exitIs) exit;
}

function aes_encrypt($plaintext, $password){
	$qry = DB::SQL()->Fetch('SELECT HEX(AES_ENCRYPT(%s, %s)) as txt', $plaintext, $password);
	return $qry['txt'];
}

function aes_decrypt($ciphertext, $password){
	$qry = DB::SQL()->Fetch('SELECT AES_DECRYPT(UNHEX(%s), %s) as txt', $ciphertext, $password);
	return $qry['txt'];
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
	$path = _DATADIR . '/temp/' . $ConnName . '/';
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
	$pass = strtoupper(sha1(sha1($input, true)));
	return $pass;
}

function _password_hash($str){
	if(_USE_OLD_PASSWORD === true) return '*' . SqlPassword($str);
	if(phpversion() < '5.3.7') return hash('sha256', hash('sha512', sha1(sha1($str, true))));
	else if(phpversion() < '5.5') require_once _COMMONDIR . '/password.php';
	return password_hash(hash('sha256', $str), PASSWORD_BCRYPT);
}

function _password_verify($str, $hash){
	if(_USE_OLD_PASSWORD === true) return '*' . SqlPassword($str) == $hash;
	if(phpversion() < '5.3.7') return $hash === hash('sha256', hash('sha512', sha1(sha1($str, true))));
	else if(phpversion() < '5.5') require_once _COMMONDIR . '/password.php';
	if(password_verify(hash('sha256', $str), $hash)) return true;
	return false;
}

function modifyFileTime($file, $group = 'default'){
	if(!file_exists($file)) return false;
	$path = _DATADIR . '/fileModTime.php';
	if(file_exists($path)) require_once $path;

	$t = filemtime($file);

	if(!isset($GLOBALS['fileModTime']) || !isset($GLOBALS['fileModTime'][$group]) || !isset($GLOBALS['fileModTime'][$group][$file]) || $t != $GLOBALS['fileModTime'][$group][$file]){
		$GLOBALS['fileModTime'][$group][$file] = $t;
		$txt = '<?php $GLOBALS[\'fileModTime\'] = ' . var_export($GLOBALS['fileModTime'], true) . ';';
		file_put_contents($path, $txt);
		return true;
	}
	return false;
}

function &Post($param){
	if(!isset(App::$SettingData['_BH_PostData'][$param])){
		App::$SettingData['_BH_PostData'][$param] = true;
		if(!isset($_POST[$param])) $_POST[$param] = null;
		else if(is_string($_POST[$param])) $_POST[$param] = trim($_POST[$param]);
	}
	return $_POST[$param];
}

function &Get($param){
	if(!isset(App::$SettingData['_BH_GetData'][$param])){
		App::$SettingData['_BH_GetData'][$param] = true;
		if(!isset($_GET[$param])) $_GET[$param] = null;
		else if(is_string($_GET[$param])) $_GET[$param] = trim($_GET[$param]);
	}
	return $_GET[$param];
}

require _COMMONDIR . '/MyLib.php';
