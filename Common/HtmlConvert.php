<?php

/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CM;
use \BH_Application as App;

define('_BHSTYLEBEGIN', '//BH_STYLE:');
define('_BHSTYLEND', '//BH_STYLE_END');
$styleData = false;

function ReplaceHTMLFile($source, $target){
	global $styleData;

	$modifyIs = modifyFileTime($source);
	if(file_exists($target) && !$modifyIs) return;

	$patterns = array(
		// if()
		'/<\?\s*if\((.*?)\)\s*[p|e]\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'/<\?\s*if\((.*?)\)\s*v\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'/<\?\s*if\((.*?)\)\s*vr\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'/<\?\s*if\((.*?)\)\s*vb\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

		// ifp.
		'/<\?\s*if[p|e]\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'/<\?\s*ifv\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'/<\?\s*ifvr\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'/<\?\s*ifvb\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

		// p.
		'/<\?\s*[p|e]\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'/<\?\s*v\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'/<\?\s*vr\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'/<\?\s*vb\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

		//fn,fq
		'/<\?\s*fn\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'/<\?\s*fq\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

		// a. c.
		'/<\?\s*a\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'/<\?\s*c\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

		// inc
		'/<\?\s*inc\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

		// mv()
		'/<\?\s*mt\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*mp\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*mv\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*mvn\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*mvr\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*mvb\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*minp\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*menum\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',

		// mv.modelName()
		'/<\?\s*mt\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*mp\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*mv\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*mvn\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*mvr\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*mvb\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*minp\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'/<\?\s*menum\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',

		// img
		'/<img\s*\!\s*(.*?)\s*src=\"(.*?)\"(.*?)>/is',
	);

	$replace = array(
		// if()
		'<?php if($1) echo $2; ?>',
		'<?php if($1) echo GetDBText($2); ?>',
		'<?php if($1) echo GetDBRaw($2); ?>',
		'<?php if($1) echo nl2br(GetDBText($2)); ?>',

		// ifp.
		'<?php if(isset($1)) echo $1; ?>',
		'<?php if(isset($1)) echo GetDBText($1); ?>',
		'<?php if(isset($1)) echo GetDBRaw($1); ?>',
		'<?php if(isset($1)) echo nl2br(GetDBText($1)); ?>',

		// p.
		'<?php echo $1; ?>',
		'<?php echo GetDBText($1); ?>',
		'<?php echo GetDBRaw($1); ?>',
		'<?php echo nl2br(GetDBText($1)); ?>',

		// fq, fn
		'<?php echo \BH_Application::GetFollowQuery($1, \'&\'); ?>',
		'<?php echo \BH_Application::GetFollowQuery($1, \'?\'); ?>',

		// a, c
		'<?php echo \BH_Application::URLAction($1); ?>',
		'<?php echo \BH_Application::URLBase($1); ?>',

		// inc
		'<?php if(_DEVELOPERIS === true) ReplaceHTMLFile(_SKINDIR.$1, _HTMLDIR.$1); require _HTMLDIR.$1; ?>',

		// mv()
		'<?php echo $Model->data[$1]->DisplayName; ?>',
		'<?php echo $Model->GetValue($1, true); ?>',
		'<?php echo $Model->GetSafeValue($1); ?>',
		'<?php echo number_format(GetDBText($Model->GetValue($1, true))); ?>',
		'<?php echo $Model->GetSafeRawValue($1); ?>',
		'<?php echo $Model->GetSafeBRValue($1); ?>',
		'<?php echo $Model->HTMLPrintInput($1); ?>',
		'<?php echo $Model->HTMLPrintEnum($1); ?>',

		// mv.modelName()
		'<?php echo $Ctrl->$1->data[$2]->DisplayName; ?>',
		'<?php echo $Ctrl->$1->GetValue($2, true); ?>',
		'<?php echo $Ctrl->$1->GetSafeValue($2); ?>',
		'<?php echo number_format(GetDBText($Ctrl->$1->GetValue($2, true))); ?>',
		'<?php echo $Ctrl->$1->GetSafeRawValue($2); ?>',
		'<?php echo $Ctrl->$1->GetSafeBRValue($2); ?>',
		'<?php echo $Ctrl->$1->HTMLPrintInput($2); ?>',
		'<?php echo $Ctrl->$1->HTMLPrintEnum($2); ?>',

		// img
		'<img $1 src="' . _DOMAIN . _URL . '$2" $3>',
	);

	$a = explode('/', $target);
	array_pop($a);
	$path = implode('/', $a) . '/';

	if(file_exists($source)){
		if(!is_dir($path)) mkdir($path, 0777, true);
		$f = file_get_contents($source);

		// 인라인 스타일 찾기 Begin
		$findStyle = '/\<style(.*?)\>(.*?)\<\/style\>/is';
		preg_match_all($findStyle, $f, $matches);

		$cssFileData = array();
		$files = array();
		if(sizeof($matches[1])){
			foreach($matches[1] as $v){
				preg_match('/.*?file="(.*?)".*?/', $v, $matches2);
				$matchFile = sizeof($matches2) ? $matches2[1] . '.bhcss.php' : _STYLEFILE;
				$files[] = $matchFile;
				__styleGet($matchFile);
			}
		}
		else{
			$files[] = _STYLEFILE;
			__styleGet(_STYLEFILE);
		}

		if(sizeof($matches[2])){
			foreach($matches[2] as $k => $v){
				if(!isset($cssFileData[$files[$k]])) $cssFileData[$files[$k]] = '';
				$cssFileData[$files[$k]] .= trim(str_replace(chr(13), '', $v)) . chr(10);
				$cssFileData[$files[$k]] = trim(preg_replace('/' . chr(10) . '\s*/', chr(10), trim($cssFileData[$files[$k]])));
			}
		}

		$file = str_replace(_DIR, '', $source);

		foreach($files as $css){
			$findIs = false;
			if($styleData[$css])
				foreach($styleData[$css] as $k => &$v){
					if($v['type'] == 'incss' && $v['file'] == $file){
						$findIs = true;
						if(!isset($cssFileData[$css]) || $v['data'] !== $cssFileData[$css]){
							if(!isset($cssFileData[$css]) || !$cssFileData[$css]) $v['data'] = '';
							else $v['data'] = $cssFileData[$css];
							__styleWrite($css);
						}
						break;
					}
				}
			if(!$findIs && isset($cssFileData[$css])){
				$styleData[$css][] = array('type' => 'incss', 'file' => $file, 'data' => preg_replace('/' . chr(10) . '\s*/', chr(10), $cssFileData[$css]));
				__styleWrite($css);
			}
		}



		$f = preg_replace($findStyle, '', $f);
		// 인라인 스타일 찾기 End

		$f = str_replace("\r", '', $f);
		$f = preg_replace(
			array(
				'/(<\!--)([^\[].*?)(\-\->)/s',
				'/(\/\*)(.*?)(\*\/)/s',
				'/\n\s*/'
			), array(
			'',
			'',
			"\n"
		), $f);
		if(_REMOVE_SPACE === true){
			$f = preg_replace(
				array(
					'/>\s*</s'
				), array(
				'><'
			), $f);
		}
		$f = preg_replace($patterns, $replace, $f);


		file_put_contents($target, $f);
	}
}

function ReplaceHTMLAll($tempfile_path, $target_path){
	if(!$target_path) return;
	if(is_dir($tempfile_path)){
		if($dh = opendir($tempfile_path)){
			while(($file = readdir($dh)) !== false){
				if($file != "." && $file != ".."){
					$dest_path = "{$tempfile_path}/{$file}";
					if(is_dir($dest_path)){
						ReplaceHTMLAll($dest_path, $target_path . '/' . $file);
					}
					else if(substr($file, -5) == '.html'){
						ReplaceHTMLFile($dest_path, $target_path . '/' . $file);
					}
				}
			}
			closedir($dh);
		}
	}
}

function ReplaceBHCSSALL($tempfile_path, $target_path){
	if(!$target_path) return;
	if(is_dir($tempfile_path)){
		if($dh = opendir($tempfile_path)){
			while(($file = readdir($dh)) !== false){
				if($file != "." && $file != ".."){
					$dest_path = "{$tempfile_path}/{$file}";
					if(is_dir($dest_path)){
						ReplaceBHCSSALL($dest_path, $target_path . '/' . $file);
					}
					else if(substr($file, strlen(BH\BHCss\BHCss::$fileExtension) * (-1)) === BH\BHCss\BHCss::$fileExtension){
						$pth = $target_path . '/' . substr($file, 0, strlen(BH\BHCss\BHCss::$fileExtension) * (-1)) . '.css';
						BH\BHCss\BHCss::conv($dest_path, $pth);
					}
				}
			}
			closedir($dh);
		}
	}
}

function __styleGet($file = ''){
	if($file == '') $file = _STYLEFILE;
	global $styleData;
	if(isset($styleData[$file])) return;

	$f = '';
	$path = _HTMLDIR . '/css/';
	if(file_exists($path . $file))
		$f = str_replace(chr(13), '', file_get_contents($path . $file));

	$styleData[$file] = array();
	$flen = strlen($f);
	for($i = 0; $i < $flen; $i++){
		//주석찾기
		if(substr($f, $i, 2) == '/*'){
			$find = strpos($f, '*/', $i);
			if($find !== false){
				$styleData[$file][] = array('type' => 'comment', 'data' => trim(substr($f, $i, $find - $i + 2)));
				$i = $find + 1;
			}
			else{
				$styleData[$file][] = array('type' => 'txt', 'data' => trim(substr($f, $i, $flen - $i)));
				return;
			}
			continue;
		}

		// HTML 내 태그 찾기
		if(substr($f, $i, strlen(_BHSTYLEBEGIN)) == _BHSTYLEBEGIN){
			$find = strpos($f, chr(10), $i);
			if($find !== false){
				$styleName = substr($f, $i + strlen(_BHSTYLEBEGIN), $find - $i - strlen(_BHSTYLEBEGIN));
				$find2 = strpos($f, _BHSTYLEND, $find);
				if($find2 !== false){
					$styleData[$file][] = array('type' => 'incss', 'file' => $styleName, 'data' => trim(preg_replace('/' . chr(10) . '\s*/', chr(10), substr($f, $find, $find2 - $find))));
					$i = $find2 + strlen(_BHSTYLEND);
				}
				else{
					$styleData[$file][] = array('type' => '', 'data' => trim(substr($f, $i, $flen - $i)));
					return;
				}
			}
			else{
				$styleData[$file][] = array('type' => '', 'data' => trim(substr($f, $i, $flen - $i)));
				return;
			}
			continue;
		}
		if($styleData[$file] === false || !sizeof($styleData[$file])){
			$styleData[$file][0] = array('type' => 'txt', 'data' => $f[$i]);
		}
		else if($styleData[$file][sizeof($styleData[$file]) - 1]['type'] == 'txt'){
			$styleData[$file][sizeof($styleData[$file]) - 1]['data'] .= $f[$i];
		}
		else{
			$styleData[$file][] = array('type' => 'txt', 'data' => $f[$i]);
		}
	}
}

function __styleWrite($file = ''){
	global $styleData;
	if($file == '') $file = _STYLEFILE;
	$f = '';
	foreach($styleData[$file] as $row){
		if(!strlen($row['data'])) continue;
		if($row['type'] == 'incss'){
			$f .= chr(10) . _BHSTYLEBEGIN . $row['file'] . chr(10) . trim(preg_replace('/' . chr(10) . '\s*/', chr(10), $row['data'])) . chr(10) . _BHSTYLEND . chr(10);
		}
		else $f .= trim($row['data']) . chr(10);
	}

	$path = _HTMLDIR . '/css/' . $file;

	if(trim($f)){
		$p = explode('/', $path);
		array_pop($p);
		$p = implode('/', $p);
		if(!is_dir($p)){
			mkdir($p, 0777, true);
		}
	}

	file_put_contents($path, $f);
	@chmod($path, 0777);
}
