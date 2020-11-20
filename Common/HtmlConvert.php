<?php

/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CM;
use \BH_Application as App;

define('_BHSTYLEBEGIN', '//BH_STYLE:');
define('_BHSTYLEND', '//BH_STYLE_END');

require _DIR . '/Custom/replace.pattern.php';

function ReplaceHTMLFile($source, $target){
	$modifyIs = modifyFileTime($source);
	if(file_exists($target) && !$modifyIs) return;

	$a = explode('/', $target);
	array_pop($a);
	$path = implode('/', $a) . '/';

	if(file_exists($source)){
		if(!is_dir($path)) mkdir($path, 0777, true);
		$f = file_get_contents($source);
		$f = str_replace("\r", '', $f);
		$f = preg_replace(
			array(
				'#(<\!--\s*remove\s*-->)(.*?)(<\!--\s*remove end\s*-->)#is',
				'#(/\*\s*remove\s*\*/)(.*?)(/\*\s*remove end\s*\*/)#is',
				'/(<\!--)([^\[].*?)(\-\->)/s',
				'/(\/\*)(.*?)(\*\/)/s'
			),
			array(
				'',
				'',
				'',
				''
			),
			$f);
		foreach(BH_Application::$settingData['_replace_patterns'] as $k => $pt){
			if(is_callable(BH_Application::$settingData['_replace_replace'][$k]))
				$f = preg_replace_callback($pt, BH_Application::$settingData['_replace_replace'][$k], $f);
			else $f = preg_replace($pt, BH_Application::$settingData['_replace_replace'][$k], $f);
		}


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
