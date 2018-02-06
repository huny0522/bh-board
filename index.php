<?php

error_reporting(E_ALL);

define('_BH_', true);
define('_DIR', str_replace(chr(92), '/', dirname(__FILE__)));

require _DIR . '/core/BHCss.php';

use BH\BHCss\BHCss;

$getphpfile = _DIR . '/getphp.php';

$setNL = false;


$dir = $oriDir = isset($argv[1]) ? $argv[1] : '';
$dir = str_replace('\\', '/', $dir);

if(substr($dir, -1) == '/') $dir = substr($dir, 0, -1);

if(!file_exists($dir) || !is_dir($dir)){
	echo chr(10) . '[' . $oriDir . '] 폴더가 존재하지 않습니다.' . chr(10) . chr(10);
	exit;
}

echo _DIR . ' : Begin Convert(path : ' . $dir . ')' . chr(10) . chr(10);
while(1){
	$msg = convertBHCssDir($dir);

	foreach(BHCss::$convDirMessage as $successIs => $msgs){
		foreach($msgs as $v){
			echo '"' . $v . '" is Convert ' . $successIs . chr(10);
		}
	}
	sleep(1);
}

function convertBHCssDir($tempfile_path, $beginIs = true){
	if($beginIs)
		BHCss::$convDirMessage = array('success' => array(), 'fail' => array());

	if(!is_dir($tempfile_path)) return;

	if($dh = opendir($tempfile_path)){
		while(($file = readdir($dh)) !== false){
			if($file != '.' && $file != '..'){
				$dest_path = $tempfile_path . '/' . $file;
				if(is_dir($dest_path)) convertBHCssDir($dest_path, false);
				else{
					if(substr($dest_path, strlen(BHCss::$fileExtension) * (-1)) == BHCss::$fileExtension){
						if(!isset(BHCss::$modifyFilesTime[$dest_path]))
							BHCss::$modifyFilesTime[$dest_path] = 0;

						BHCss::reset();
						$res = convTimeCheck(BHCss::$modifyFilesTime[$dest_path], $dest_path);
						if(!is_null($res)){
							if($res->result){
								BHCss::$convDirMessage['success'][] = $dest_path;
							}
							else{
								BHCss::$convDirMessage['fail'][] = $dest_path;
							}
						}
					}
				}
			}
		}
		closedir($dh);
	}
}

// 파일 변경 시간 체크 후 컨버팅
function convTimeCheck(&$beforeTime, $path){
	global $getphpfile, $setNL;
	$path = str_replace('\\', '/', $path);

	if(in_array($path, BHCss::$passFiles)) return null;

	if(file_exists($path)){
		$targetTime = filemtime($path);
		if($beforeTime != $targetTime){
			$arg2 = $setNL ? 1 : 0;
			exec("php -q $getphpfile \"$path\" $arg2", $output);
			$res = implode(PHP_EOL, $output);

			$beforeTime = $targetTime;

			if(substr($res, 0, 2) === '1:') return (object) array('result' => true, 'message' => '');
			return (object) array('result' => false, 'message' => substr($res, 2));
		}
	}
	return null;
}