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

require _DIR . '/Custom/replace.pattern.php';

function ReplaceHTMLFile($source, $target){
	global $styleData;

	$modifyIs = modifyFileTime($source);
	if(file_exists($target) && !$modifyIs) return;

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
		$f = preg_replace(BH_Application::$SettingData['_replace_patterns'], BH_Application::$SettingData['_replace_replace'], $f);


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
