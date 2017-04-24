<?php
/**
 * Bang Hun.
 * 16.07.10
 */

define('ModelTypeInt', 1);
define('ModelTypeString', 2);
define('ModelTypeEng', 3);
define('ModelTypeEngNum', 4);
define('ModelTypeEngSpecial', 5);
define('ModelTypeFloat', 6);
define('ModelTypeDatetime', 7);
define('ModelTypeDate', 8);
define('ModelTypeEnum', 9);
define('ModelTypePassword', 10);

define('HTMLInputText', 'text');
define('HTMLInputPassword', 'password');
define('HTMLInputRadio', 'radio');
define('HTMLInputCheckbox', 'checkbox');
define('HTMLInputFile', 'file');
define('HTMLSelect', 'select');
define('HTMLTextarea', 'textarea');

define('ENG_NUM', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
define('ENG_UPPER', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
define('ENG_LOWER', 'abcdefghijklmnopqrstuvwxyz');

// -------------------------------------
//
//		기본 함수
//

function my_escape_string($str) {
	if(is_array($str)) return array_map('my_escape_string', $str);
	else return trim(str_replace(array(';'),array(chr(92).';'),mysqli_real_escape_string($GLOBALS['_BH_App']->_MainConn, $str)));
}

function Redirect($url, $msg=''){

	if(_AJAXIS === true){
		JSON($url != '-1', $msg);
	}

	echo '<script>';
	if($msg){
		echo 'alert(\''.$msg.'\');';
	}
	if($url == '-1'){
		echo 'history.go(-1);';
	}else{
		$url = str_replace(' ', '%20', $url);
		echo 'location.replace(\''.$url.'\');';
	}
	echo '</script>';
	exit;
}

function PhoneNumber($num){
	$num = str_replace('-','',$num);
	if(strlen($num) == 11) return substr($num, 0, 3).'-'.substr($num, 3, 4).'-'.substr($num, 7, 4);
	else return substr($num, 0, 3).'-'.substr($num, 3, 3).'-'.substr($num, 6, 4);
}

function KrDate($date, $opt = 'ymdhis', $hourView = 0){
	if($hourView){
		$t = time() - strtotime($date);
		if($t<60) return $t.'초 전';
		else if($t<3600) return floor($t/60).'분 전';
		else if($t < 3600*$hourView) return floor($t/3600).'시간 전';
	}
	$opt = strtolower($opt);
	$res = (strpos($opt, 'y') !== false ? substr($date, 0, 4).'년 ' : '')
		.(strpos($opt, 'm') !== false ? (int)substr($date, 5, 2).'월 ' : '')
		.(strpos($opt, 'd') !== false ? (int)substr($date, 8, 2).'일 ' : '')
		.(strpos($opt, 'h') !== false ? (int)substr($date, 11, 2).'시 ' : '')
		.(strpos($opt, 'i') !== false ? (int)substr($date, 14, 2).'분 ' : '')
		.(strpos($opt, 's') !== false ? (int)substr($date, 17, 2).'초 ' : '');
	return trim($res);
}

function DotDate($dt){
	return str_replace('-', '.', substr($dt, 0, 10));
}

function AutoLinkText($text){
	$text= preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" target=\"_blank\">$3</a>", $text);
	$text= preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" target=\"_blank\">$3</a>", $text);
	$text= preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\" target=\"_blank\">$2@$3</a>", $text);
	return($text);
}


function OptionAreaNumber($num = ''){
	$numbers = array();
	$numbers[] = array( 'num' => '02', 'loc' => '서울' );
	$numbers[] = array( 'num' => '031', 'loc' => '경기' );
	$numbers[] = array( 'num' => '051', 'loc' => '부산' );
	$numbers[] = array( 'num' => '053', 'loc' => '대구' );
	$numbers[] = array( 'num' => '032', 'loc' => '인천' );
	$numbers[] = array( 'num' => '062', 'loc' => '광주' );
	$numbers[] = array( 'num' => '042', 'loc' => '대전' );
	$numbers[] = array( 'num' => '052', 'loc' => '울산' );
	$numbers[] = array( 'num' => '044', 'loc' => '세종' );
	$numbers[] = array( 'num' => '033', 'loc' => '강원' );
	$numbers[] = array( 'num' => '043', 'loc' => '충북' );
	$numbers[] = array( 'num' => '041', 'loc' => '충남' );
	$numbers[] = array( 'num' => '063', 'loc' => '전북' );
	$numbers[] = array( 'num' => '061', 'loc' => '전남' );
	$numbers[] = array( 'num' => '054', 'loc' => '경북' );
	$numbers[] = array( 'num' => '055', 'loc' => '경남' );
	$numbers[] = array( 'num' => '064', 'loc' => '제주' );

	$str = '';

	foreach ($numbers as $item){
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
	foreach ($numbers as $item)
	{
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
	foreach ($addr as $item){
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
	foreach($OptionValues as $k=>$v){
		$str .= '<option value="' . $k . '"' . ($SelectValue == $k ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}
	return $str;
}

// Cut title length
function StringCut($title, $length, $last = '...'){
	if(mb_strlen($title,'utf-8') > $length){
		$result_title = mb_substr($title, 0, $length, 'utf-8').$last;
	}
	else{
		$result_title = $title;
	}

	Return $result_title;
}

/**
 * @params int $month 월 (숫자 두자리 또는 '2015-10-11'형식으로 $year 생략)
 * @params int $year 년
 *
 */
function GetLastDay($month, $year = false) {
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
	return date('t', mktime(0,0,0,$month, 1, $year));
}

function ToInt($s){return preg_replace('/[^0-9\-]/','$1',$s);}

function ToFloat($s){return preg_replace('/[^0-9\.\-]/','$1',$s);}

function RemoveScriptTag($str){return preg_replace('!<script(.*?)<\/script>!is','',$str);}

function SetDBTrimText($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v){
			$v = SetDBTrimText($v);
		}
		return $txt;
	}
	return chr(39).trim(my_escape_string($txt)).chr(39);
}

function SetDBText($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v){
			$v = SetDBText($v);
		}
		return $txt;
	}
	return chr(39).(my_escape_string($txt)).chr(39);
}

function SetDBQuot($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v){
			$v = SetDBQuot($v);
		}
		return $txt;
	}
	return chr(39).$txt.chr(39);
}

function SetDBInt($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v){
			$v = SetDBInt($v);
		}
		return $txt;
	}

	if(!strlen($txt)){
		Redirect('-1', '숫자값이 비어있습니다.');
	}
	$val = ToInt($txt);
	if($val != $txt){
		Redirect('-1', '숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.');
	}
	return $val;
}

function SetDBFloat($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v){
			$v = SetDBFloat($v);
		}
		return $txt;
	}

	if(!strlen($txt)){
		Redirect('-1', '숫자값이 비어있습니다.');
	}
	$val = ToFloat($txt);
	if($val != $txt){
		Redirect('-1', '숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.');
	}
	return $val;
}

function GetDBText($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v){
			$v = GetDBText($v);
		}
		return $txt;
	}
	else return htmlspecialchars(stripslashes($txt));
}

function GetDBRaw($txt){
	if(is_array($txt)){
		foreach($txt as $k => &$v){
			$v = GetDBRaw($v);
		}
		return $txt;
	}
	else return RemoveScriptTag(stripslashes($txt));
}

function my_bcmod( $x, $y ){
	$take = 5;
	$mod = '';
	do{
		$a = (int)$mod.substr( $x, 0, $take );
		$x = substr( $x, $take );
		$mod = $a % $y;
	}
	while ( strlen($x) );

	return (int)$mod;
}

function toBase($num, $b=62, $base=ENG_NUM) {
	if(!isset($num) || !strlen($num)) return '';
	$r = my_bcmod($num, $b);
	$res = $base[$r];
	$q = floor($num/$b);
	while ($q) {
		$r = my_bcmod($q, $b);
		$q = floor($q / $b);
		$res = $base[$r].$res;
	}
	return $res;
}

function to10($num, $b=62, $base=ENG_NUM) {
	if(!isset($num) || !strlen($num)) return '';
	$limit = strlen($num);
	$res=strpos($base,$num[0]);
	for($i=1;$i<$limit;$i++) {
		$res = $b * $res + strpos($base,$num[$i]);
	}
	return $res;
}

function JSON($bool, $message = '', $data = array()){
	echo json_encode(array('result' => $bool, 'message' => $message, 'data' => $data));
	exit;
}

function aes_encrypt($plaintext, $password){
	$qry = SqlFetch('SELECT HEX(AES_ENCRYPT(%s, %s)) as txt', $plaintext, $password);
	return $qry['txt'];
}

function aes_decrypt($ciphertext, $password){
	$qry = SqlFetch('SELECT AES_DECRYPT(UNHEX(%s), %s) as txt', $ciphertext, $password);
	return $qry['txt'];
}

function delTree($dir) {
	if(!is_dir($dir)) return false;
	$files = array_diff(scandir($dir), array('.','..'));
	foreach ($files as $file) {
		(is_dir($dir.'/'.$file)) ? delTree($dir.'/'.$file) : unlink($dir.'/'.$file);
	}
	return rmdir($dir);
}

function findDelTree($dir) {
	if(!file_exists(_DATADIR.'/temp/') && !is_dir(_DATADIR.'/temp/')) mkdir(_DATADIR.'/temp/', 0755, true);
	$files = array_diff(scandir(_DATADIR.'/temp/'), array('.','..'));
	foreach ($files as $file) {
		if(is_dir(_DATADIR.'/temp/'.$file) && strpos($file, $dir) !== false) delTree(_DATADIR.'/temp/'.$file);
	}
}

function StrToSql($args){
	if(!is_array($args)) $args = func_get_args();

	$n = sizeof($args);
	if(!$n) return false;
	if($n == 1) return $args[0];
	else{
		$p = -1;
		$w = $args[0];
		for($i = 1; $i < $n; $i++){
			$p = strpos($w, '%', $p+1);
			$find = false;
			while(!$find && $p !== false && $p < strlen($w)){
				$t = $w[$p+1];
				switch($t){
					case 's':
						$t = is_array($args[$i]) ? implode(',', SetDBText($args[$i])) : SetDBText($args[$i]);
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					break;
					case 'f':
						$t = is_array($args[$i]) ? implode(',', SetDBFloat($args[$i])) : SetDBFloat($args[$i]);
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					break;
					case 'd':
						$t = is_array($args[$i]) ? implode(',', SetDBInt($args[$i])) : SetDBInt($args[$i]);
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					break;
					case '1':
						$t = is_array($args[$i]) ? implode(',', my_escape_string($args[$i])) : my_escape_string($args[$i]);
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					break;
					default:
						$p = strpos($w, '%', $p+1);
					break;
				}
			}
		}
		return str_replace(array('%\s', '%\f', '%\d', '%\1', '%\t'), array('%s', '%f', '%d', '%1', '%t'), $w);
	}
}

// ----------------------------------------
//
//			SQL
//
function SqlConnection($_DBInfo){
	return mysqli_connect($_DBInfo['hostName'], $_DBInfo['userName'], $_DBInfo['userPassword'], $_DBInfo['dbName']);
}

function SqlFree($result){
	if(is_bool($result)) return;
	mysqli_free_result($result);
}

/**
 * @param string
 * @return bool
 * */
function SqlTableExists($table){
	$exists = SqlNumRows("SHOW TABLES LIKE '" . $table . "'");
	if($exists)
		return true;
	else
		return false;
}

/**
 * @param $sql
 * @return bool|mysqli_result
 */
function SqlQuery($sql){
	$sql = StrToSql(func_get_args());
	if(_DEVELOPERIS === true)
		$res = mysqli_query($GLOBALS['_BH_App']->_Conn, $sql) or die('ERROR SQL : '.$sql);
	else
		$res = mysqli_query($GLOBALS['_BH_App']->_Conn, $sql) or die('ERROR');
	return $res;
}

function SqlCCQuery($table, $sql){
	$args = func_get_args();
	array_shift($args);
	if(strpos($sql, '%t') === false) die('ERROR SQL(CC)'.(_DEVELOPERIS === true ? ' : '.$sql : ''));
	$args[0] = str_replace('%t', $table, $args[0]);
	$sql = trim(StrToSql($args));

	if(_DEVELOPERIS === true)
		$res = mysqli_query($GLOBALS['_BH_App']->_Conn, $sql) or die('ERROR SQL : '.$sql);
	else
		$res = mysqli_query($GLOBALS['_BH_App']->_Conn, $sql) or die('ERROR SQL');

	if($res && (strtolower(substr($sql, 0, 6)) == 'delete' || strtolower(substr($sql, 0, 6)) == 'update' || strtolower(substr($sql, 0, 6)) == 'insert')) BH_DB_Cache::DelPath($table);

	return $res;
}

/**
 * @param $qry
 * @return bool|int
 */
function SqlNumRows($qry){
	if(is_string($qry))
		$qry = SqlQuery($qry);
	if($qry === false) return false;

	try{
		$r = mysqli_num_rows($qry);

		return $r;
	}
	catch(Exception $e){
		if(_DEVELOPERIS === true) echo 'NUMBER ROWS MESSAGE(DEBUG ON) : <b>'. $e->getMessage().'</b><br>';
		return false;
	}
}

/**
 * @param $qry
 * @return array|bool|null
 */
function SqlFetch($qry){
	if(!isset($qry) || $qry === false || empty($qry)){
		if(_DEVELOPERIS === true) echo 'FETCH ASSOC MESSAGE(DEBUG ON) : <b>query is empty( or null, false).</b><br>';
		return false;
	}
	$string_is = false;
	if(is_string($qry)){
		if(func_num_args() > 1) $qry = StrToSql(func_get_args());
		$qry = SqlQuery($qry);
		if($qry === false) return false;
		$string_is = true;
	}

	$r = mysqli_fetch_assoc($qry);
	if($string_is) SqlFree($qry);

	return $r;
}

function SqlPassword($input) {
	$pass = strtoupper(
		sha1(
			sha1($input, true)
		)
	);
	return $pass;
}

$fileModTime = array();
function modifyFileTime($file){
	if(!file_exists($file)) return false;
	$path = _DATADIR.'/fileModTime.php';
	if(file_exists($path)) require_once $path;

	$t = filemtime($file);

	if(!isset($GLOBALS['fileModTime'][$file]) || $t != $GLOBALS['fileModTime'][$file]){
		$GLOBALS['fileModTime'][$file] = $t;
		$txt = '<?php $GLOBALS[\'fileModTime\'] = '.var_export($GLOBALS['fileModTime'], true).';';
		file_put_contents($path, $txt);
		return true;
	}
	return false;
}

// -------------------------------------
//
//		Model
//



class ModelType{
	const Int = 1;
	const String = 2;
	const Eng = 3;
	const EngNum = 4;
	const EngSpecial = 5;
	const Float = 6;
	const Datetime = 7;
	const Date = 8;
	const Enum = 9;
	const Password = 10;
}

class HTMLType{
	const InputText = 'text';
	const InputPassword = 'password';
	const InputRadio = 'radio';
	const InputCheckbox = 'checkbox';
	const InputFile = 'file';
	const Select = 'select';
	const Textarea = 'textarea';
}

class BH_Result{
	public $result = false;
	public $message = '';
}

class BH_InsertResult{
	public $result = false;
	public $id = null;
	public $message = '';
}

class BH_ModelData{
	public $Name;
	public $Type;
	public $Required = false;
	public $DisplayName;
	public $ModelErrorMsg;
	public $MinLength = false;
	public $MaxLength = false;
	public $MinValue = false;
	public $MaxValue = false;
	public $EnumValues;
	public $Value;
	public $DefaultValue;
	public $HtmlType;
	public $AutoDecrement = false;
	public $ValueIsQuery = false;

	public function __construct($Type = ModelType::String, $Required = false, $DisplayName = '', $HtmlType = HTMLType::InputText){
		$this->Type = $Type;
		$this->Required = $Required;
		$this->DisplayName = $DisplayName;
		if($HtmlType) $this->HtmlType = $HtmlType;
	}
}


class _ModelFunc{
	public static function SetPostValues(&$Data, $Except, &$Need){
		$ret = new \BH_Result();
		$ret->result = true;
		foreach($Data as $k => &$v){
			if(!in_array($k, $Except) && $v->AutoDecrement !== true){
				if(!isset($_POST[$k])){
					if(isset($Need) && in_array($k, $Need)){
						$ret->message = $v->ModelErrorMsg = $v->DisplayName.' 항목이 정의되지 않았습니다.';
						$ret->result = false;
						return $ret;
					}
				}
				else{
					if((isset($v->HtmlType) || $v->Required) && $v->HtmlType != HTMLInputFile){
						if(isset($_POST[$k])) $v->Value = $_POST[$k];
						$Need[] = $k;
					}
				}
			}

		}

		return $ret;
	}

	public static function CheckType($key, &$data){
		switch($data->Type){
			case ModelTypeInt:
				$val = preg_replace('/[^Z0-9\-]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 숫자만 입력 가능합니다.';
					return false;
				}
				return true;
			break;
			case ModelTypeFloat:
				$val = preg_replace('/[^Z0-9\.\-]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 숫자만 입력 가능합니다.';
					return false;
				}
			break;
			case ModelTypeEnum:
				if(isset($data->EnumValues) && is_array($data->EnumValues) && isset($data->EnumValues[$data->Value])){
					return true;
				}else{
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목에 값이 필요합니다.';
					return false;
				}
			break;
			case ModelTypeEng:
				$val = preg_replace('/[^a-zA-Z]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문만 입력가능합니다.';
					return false;
				}
				return true;
			break;
			case ModelTypeEngNum:
				if ( !ctype_alnum($data->Value) ) {
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문과 숫자만 입력가능합니다.';
					return false;
				}
				return true;
			break;
			case ModelTypeEngSpecial:
				$val = preg_replace('/[^a-zA-Z0-9~!@\#$%^&*\(\)\.\,\<\>\'\"\?\-=\+_\:\;\[\]\{\}\/]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문과 숫자, 특수문자만 입력가능합니다.';
					return false;
				}
				return true;
		}
		return true;
	}

	public static function CheckValue($key, &$Data){
		if($Data->Type == ModelTypeInt || $Data->Type == ModelTypeFloat){
			if($Data->MinValue !== false && $Data->MinValue > $Data->Value){
				$Data->ModelErrorMsg = $Data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목에 '.$Data->MinValue.' 이상의 값을 입력하여 주세요.';
				return false;
			}
			if($Data->MaxValue !== false && $Data->MaxValue < $Data->Value){
				$Data->ModelErrorMsg = $Data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목에 '.$Data->MaxValue.' 이하의 값을 입력하여 주세요.';
				return false;
			}
		}
		return true;
	}

	public static function CheckLength($key, &$Data){
		if($Data->Type == ModelTypeString){
			if($Data->MinLength !== false && $Data->MinLength > strlen($Data->Value)){
				$Data->ModelErrorMsg = $Data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 '.$Data->MinLength.'자 이상 입력하여 주세요.';
				return false;
			}
			if($Data->MaxLength !== false && $Data->MaxLength < strlen($Data->Value)){
				$Data->ModelErrorMsg = $Data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 '.$Data->MaxLength.'자 이하 입력하여 주세요.';
				return false;
			}
		}
		return true;
	}

	public static function HTMLPrintInput($Name, $data, $HtmlAttribute = false){
		$htmlType = strtolower($data->HtmlType);
		$Attribute = '';
		$val = isset($data->Value) ? $data->Value : $data->DefaultValue;

		if($HtmlAttribute === false) $HtmlAttribute = array();

		if(!isset($HtmlAttribute['class'])) $HtmlAttribute['class'] = '';

		if($data->MinLength !== false){
			$Attribute .= ' data-minlength="'.$data->MinLength.'"';
		}
		if($data->MaxLength !== false){
			$Attribute .= ' data-maxlength="'.$data->MaxLength.'"';
			$Attribute .= ' maxlength="'.$data->MaxLength.'"';
		}
		if($data->MinValue !== false){
			$Attribute .= ' data-minvalue="'.$data->MinValue.'"';
		}
		if($data->MaxValue !== false){
			$Attribute .= ' data-maxvalue="'.$data->MaxValue.'"';
		}
		if($data->Required){
			$Attribute .= ' required="required"';
		}
		if($data->Type == ModelTypeInt){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'numberonly';
		}

		if($data->Type == ModelTypeEngNum){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'engnumonly';
		}

		if($data->Type == ModelTypeEng){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'engonly';
		}

		if($data->Type == ModelTypeEngSpecial){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'engspecialonly';
		}

		if($data->Type == ModelTypeDate || $data->Type == ModelTypeDatetime){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'date';
			$HtmlAttribute['maxlength'] = '10';
			$HtmlAttribute['minlength'] = '10';
		}

		foreach($HtmlAttribute as $k => $row){
			$Attribute .= ' '.$k.'="'.$row.'"';
		}

		switch($htmlType){
			case HTMLInputText:
			case HTMLInputPassword:
				return '<input type="'.$htmlType.'" name="'.$Name.'" id="MD_'.$Name.'" '.(isset($val) && $htmlType != HTMLInputPassword ? 'value="'.$val.'"' : '').' data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';
			break;
			case HTMLInputFile:
				return '<input type="'.$htmlType.'" name="'.$Name.'" id="MD_'.$Name.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';
			break;
			case HTMLTextarea:
				return '<textarea name="'.$Name.'" id="MD_'.$Name.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.'>'.(isset($val) ? $val : '').'</textarea>';
			break;
			case HTMLInputRadio:
			case HTMLInputCheckbox:
				$ret = '';
				if(isset($data->EnumValues) && is_array($data->EnumValues)){
					$i = 1;
					foreach($data->EnumValues as $k=>$v){
						$checked = isset($val) && $k == $val ? ' checked="checked"' : '';

						$ret .= '<input type="'.$htmlType.'" name="'.$Name.'" id="MD_'.$Name.'_'.$i.'" value="'.$k.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.$checked.'>';
						$ret .= '<label for="MD_'.$Name.'_'.$i.'">'.$v.'</label>';
						$i++;
					}
				}
				return $ret;
			break;
			case HTMLSelect:
				$ret = '<select name="'.$Name.'" id="MD_'.$Name.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';

				if(isset($data->EnumValues) && is_array($data->EnumValues)){
					foreach($data->EnumValues as $k=>$v){
						$selected = isset($val) && $k == $val ? ' selected="selected"' : '';

						$ret .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
					}
				}
				return $ret.'</select>';
			break;
		};
		return '';
	}

	public static function DBInsert($Table, $Data, $Except, $Key, $Need, $test = false){
		$dbInsert = new \BH_DB_Insert($Table);
		$result = new \BH_InsertResult();

		foreach($Data as $k=>$v){
			if(!isset($v->Value) && in_array($k, $Need)){
				$result->result = false;
				$result->message = 'ERROR#101';
				return $result;
			}

			// 예외 패스, 셋이 없거나 셋에 있는것
			if((!in_array($k, $Except) && (!sizeof($Need) || in_array($k, $Need)))){
				if(isset($v->Value)){
					if(in_array($k, $Key) && $v->AutoDecrement === true){
						continue;
					}
					if($v->ValueIsQuery) $dbInsert->data[$k] = $v->Value;
					else if($v->Type == ModelTypeInt){
						if(!strlen($v->Value) && isset($v->DefaultValue)) $dbInsert->data[$k] = $v->DefaultValue;
						else{
							$res = self::CheckInt($k, $v->Value);
							if($res === true) $dbInsert->data[$k] = $v->Value;
							else{
								$result->result = false;
								$result->message = $res;
							}
						}
					}
					else if($v->Type == ModelTypeFloat){
						if(!strlen($v->Value) && isset($v->DefaultValue)) $dbInsert->data[$k] = $v->DefaultValue;
						else{
							$res = self::CheckFloat($k, $v->Value);
							if($res === true) $dbInsert->data[$k] = $v->Value;
							else{
								$result->result = false;
								$result->message = $res;
							}
						}
					}
					else if($v->Type == ModelTypePassword) $dbInsert->data[$k] = 'PASSWORD('.SetDBText($v->Value).')';
					else $dbInsert->data[$k] = SetDBText($v->Value);
				}
			}
		}

		foreach($Key as $k){
			if($Data[$k]->AutoDecrement === true){
				$dbInsert->decrement = $k;
			}
			else if($Data[$k]->Value) $dbInsert->AddWhere($k.'='.SetDBText($Data[$k]->Value));
		}
		if(!$dbInsert->decrement) $dbInsert->UnsetWhere();
		if(_DEVELOPERIS === true) $dbInsert->test = $test;
		$dbInsert->Run();
		$result->id = $dbInsert->id;
		$result->message = $dbInsert->message;
		$result->result = $dbInsert->result;
		return $result;
	}

	public static function DBUpdate($Table, $Data, $Except, $Key, $Need, $test = false){
		$result = new \BH_Result();

		$dbUpdate = new \BH_DB_Update($Table);
		foreach($Data as $k=>$v){
			if(!isset($v->Value) && in_array($k, $Need)){
				$result->result = false;
				$result->message = 'ERROR';
				return $result;
			}

			// 예외와 키값 패스, 셋이 없거나 셋에 있는것
			if(!in_array($k, $Except) && (!sizeof($Need) || in_array($k, $Need)) && !in_array($k, $Key)){
				if(isset($v->Value)){
					if(in_array($k, $Key) && $v->AutoDecrement === true){
						continue;
					}
					if($v->ValueIsQuery) $dbUpdate->data[$k] = $v->Value;
					else if($v->Type == ModelTypeInt){
						$res = self::CheckInt($k, $v->Value);
						if($res === true) $dbUpdate->data[$k] = $v->Value;
						else{
							$result->result = false;
							$result->message = $res;
						}
					}
					else if($v->Type == ModelTypeFloat){
						$res = self::CheckFloat($k, $v->Value);
						if($res === true) $dbUpdate->data[$k] = $v->Value;
						else{
							$result->result = false;
							$result->message = $res;
						}
					}
					else if($v->Type == ModelTypePassword) $dbUpdate->data[$k] = 'PASSWORD('.SetDBText($v->Value).')';
					else $dbUpdate->data[$k] = SetDBText($v->Value);
				}
			}
		}
		foreach($Key as $k){
			if(isset($Data[$k]->Value) && strlen($Data[$k]->Value)) $dbUpdate->AddWhere($k.'='.SetDBText($Data[$k]->Value));
			else{
				$result->message = 'Empty Key';
				$result->result = false;
				return $result;
			}
		}

		if(_DEVELOPERIS === true) $dbUpdate->test = $test;
		$dbUpdate->Run();
		$result->result = $dbUpdate->result;
		$result->message = $dbUpdate->message;
		return $result;
	}

	public static function DBGet($keys, $modelKey, $table){
		$res = new \BH_Result();

		if(!isset($modelKey) || !is_array($modelKey)){
			if(_DEVELOPERIS === true){
				echo '키값이 존재하지 않습니다.';
				exit;
			}
			$res->result = false;
			$res->message = 'ERROR#01';
			return $res;
		}
		else if(sizeof($keys) != sizeof($modelKey)){
			if(_DEVELOPERIS === true){
				echo '모델의 키의 길이와 인자값의 키의 길이가 동일하지 않습니다.';
				exit;
			}
			$res->result = false;
			$res->message = 'ERROR#02';
			return $res;
		}
		$dbGet = new \BH_DB_Get($table);
		foreach($modelKey as $k => $v){
			$dbGet->AddWhere($v.' = '.SetDBTrimText($keys[$k]));
		}
		//$dbGet->test = true;

		$data = $dbGet->Get();

		if($data !== false){
			$res->result = $data;
		}else{
			$res->result = false;
		}
		return $res;
	}

	public static function DBDelete($keyData, $ModelKey, $Table){
		$res = new \BH_Result();

		if(!is_array($keyData)){
			$keyData = array($keyData);
		}
		if(!isset($ModelKey) || !is_array($ModelKey)){
			if(_DEVELOPERIS === true){
				echo '키값이 존재하지 않습니다.';
				exit;
			}

			$res->result = false;
			$res->message = 'ERROR#01';
			return $res;
		}
		else if(sizeof($keyData) != sizeof($ModelKey)){
			if(_DEVELOPERIS === true){
				echo '모델의 키의 길이와 인자값의 키의 길이가 동일하지 않습니다.';
				exit;
			}


			$res->result = false;
			$res->message = 'ERROR#02';
			return $res;
		}
		$params['table'] = $Table;
		$params['where'] = array();
		foreach($ModelKey as $k => $v){
			$params['where'][] = $v.' = '.SetDBTrimText($keyData[$k]);
		}

		if(!sizeof($params['where'])){
			$res->result = false;
			$res->message = 'ERROR#03';
			return $res;
		}

		$sql = 'DELETE FROM '.$params['table'].' WHERE '.implode(' AND ', $params['where']);
		$res->result = SqlQuery($sql);
		BH_DB_Cache::DelPath($params['table']);
		return $res;
	}

	public static function CheckInt($k, $v){
		if(!strlen($v)){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자값이 비어있습니다.');
				else Redirect('-1', '['.$k.']숫자값이 비어있습니다.');
			}else return 'ERROR#102';
		}
		$val = ToInt($v);
		if($val != $v){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.');
				else Redirect('-1', '['.$k.']숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.');
			}else return 'ERROR#103';
		}
		return true;
	}

	public static function CheckFloat($k, $v){
		if(!strlen($v)){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자값이 비어있습니다.');
				else Redirect('-1', '['.$k.']숫자값이 비어있습니다.');
			}else return 'ERROR#112';
		}
		$val = ToFloat($v);
		if($val != $v){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자(소수)가 들아갈 항목에 문자가 들어갈 수 없습니다.');
				else Redirect('-1', '['.$k.']숫자(소수)가 들아갈 항목에 문자가 들어갈 수 없습니다.');
			}else return 'ERROR#113';
		}
		return true;
	}
}

function _CategoryGetChild($table, $parent, $length){
	$dbGet = new \BH_DB_GetList($table);
	$dbGet->AddWhere('LEFT(category, '.strlen($parent).') = '.SetDBText($parent));
	$dbGet->AddWhere('LENGTH(category) = '.(strlen($parent) + $length));
	$dbGet->sort = 'sort';
	return $dbGet->GetRows();
}

function _CategorySetChildEnable($table, $parent, $enabled){
	if(is_null($parent)) return;

	$dbUpdate = new \BH_DB_Update($table);
	$dbUpdate->AddWhere('LEFT(category, '.strlen($parent).') = '.SetDBText($parent));
	$dbUpdate->data['parent_enabled'] = SetDBText($enabled);
	$dbUpdate->Run();
}

function _CategoryGetParent($table, $category, $length){
	if(is_null($category)) return false;
	$parent = substr($category, 0, strlen($category) - $length);
	if(!$parent) return false;

	$dbGet = new \BH_DB_Get($table);
	$dbGet->AddWhere('category='.SetDBText($parent));
	return $dbGet->Get();
}
