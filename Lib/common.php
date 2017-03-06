<?php
/**
 * Bang Hun.
 * 16.07.10
 */

// -------------------------------------
//
//		기본 함수
//

function my_escape_string($str) {
	if(is_array($str)) return array_map('my_escape_string', $str);
	else return trim(str_replace(array(';'),array(chr(92).';'),mysqli_real_escape_string($GLOBALS['_BH_App']->_MainConn, $str)));
}

function Redirect($url, $msg=''){

	echo "<script>";
	if($msg){
		echo "alert('".$msg."');";
	}
	if($url == '-1'){
		echo "history.go(-1);";
	}else{
		$url = str_replace(" ", "%20", $url);
		echo "location.replace('".$url."');";
	}
	echo "</script>";
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
function SelectOption($OptionValues, $SelectValue){
	$str = '';
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
	return chr(39).trim(my_escape_string($txt)).chr(39);
}

function SetDBText($txt){
	if(is_array($txt)){
		foreach($txt as $k => $row){
			$txt[$k] = SetDBText($row);
		}
		return $txt;
	}
	else return chr(39).(my_escape_string($txt)).chr(39);
}

function SetDBInt($txt){
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
		foreach($txt as $k => $row){
			$txt[$k] = GetDBText($row);
		}
		return $txt;
	}
	else return htmlspecialchars(stripslashes($txt));
}

function GetDBRaw($txt){
	if(is_array($txt)){
		foreach($txt as $k => $row){
			$txt[$k] = GetDBRaw($row);
		}
		return $txt;
	}
	else return RemoveScriptTag(stripslashes($txt));
}

function toBase($num, $b=62) {
	if(!isset($num) || !strlen($num)) return '';
	$base='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	$r = $num  % $b ;
	$res = $base[$r];
	$q = floor($num/$b);
	while ($q) {
		$r = $q % $b;
		$q =floor($q/$b);
		$res = $base[$r].$res;
	}
	return $res;
}

function to10($num, $b=62) {
	if(!isset($num) || !strlen($num)) return '';
	$base='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
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

function fileModifyIs($file){
	if(_BH_ !== true) return false;
	$path = _DIR.'/Common/fileModTime.inc';

	$val = array();
	$modIs = false;
	if(file_exists($path)) $val = json_decode(file_get_contents($path), true);

	if(is_array($file)){
		foreach($file as $v){
			if(file_exists(_DIR.$v)){
				$lastmod = date("YmdHis", filemtime(_DIR.$v));
				if(!isset($val[$v]) || $val[$v] != $lastmod){
					$val[$v] = $lastmod;
					$modIs = true;
				}
			}else unset($val[$v]);
		}
	}else{
		if(file_exists(_DIR.$file)){
			$lastmod = date("YmdHis", filemtime(_DIR.$file));
			if(!isset($val[$file]) || $val[$file] != $lastmod){
				$val[$file] = $lastmod;
				$modIs = true;
			}
		}else unset($val[$file]);
	}
	if($modIs) file_put_contents($path, json_encode($val));
	return $modIs;
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
	$exists = SqlFetch("SHOW TABLES LIKE '" . $table . "'");
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
	try{
		$res = mysqli_query($GLOBALS['_BH_App']->_Conn, $sql);
		if($GLOBALS['_BH_App']->_Conn->error){
			throw new Exception($GLOBALS['_BH_App']->_Conn->error);
		}
		return $res;
	}
	catch(Exception $e){
		if(_DEVELOPERIS === true) echo 'QUERY MESSAGE(DEBUG ON) : <b>'. $e->getMessage().'</b><br>'.$sql;
		return false;
	}
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
	if($qry === false || !isset($qry) || empty($qry)){
		if(_DEVELOPERIS === true) echo 'FETCH ASSOC MESSAGE(DEBUG ON) : <b>query is empty( or null, false).</b><br>';
		return false;
	}
	$string_is = false;
	if(is_string($qry)){
		$qry = SqlQuery($qry);
		if($qry === false) return false;
		$string_is = true;
	}

	try{
		$r = mysqli_fetch_assoc($qry);
		if($string_is) SqlFree($qry);

		return $r;
	}
	catch(Exception $e){
		if(_DEVELOPERIS === true) echo 'FETCH ASSOC MESSAGE(DEBUG ON) : <b>'. $e->getMessage().'</b><br>';
		return false;
	}
}


