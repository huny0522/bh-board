<?php

error_reporting(E_ALL);

define('_BH_', true);
define('_DIR', str_replace(chr(92), '/', dirname(__FILE__)));

require _DIR . '/core/BHCss.php';

use BH\BHCss\BHCss;

BHCss::setNL(false);


$dir = $oriDir = isset($argv[1]) ? $argv[1] : '';
$dir = str_replace('\\', '/', $dir);

if(!file_exists($dir) || !is_dir($dir)){
	echo chr(10) . '[' . $oriDir . '] 폴더가 존재하지 않습니다.' . chr(10) . chr(10);
	exit;
}

echo _DIR . ' : Begin Convert(path : ' . $dir . ')' . chr(10) . chr(10);
while(1){
	$msg = BHCss::convertBHCssDir($dir);
	foreach(BHCss::$convDirMessage as $successIs => $msgs){
		foreach($msgs as $v){
			echo '"' . $v . '" is Convert ' . $successIs . chr(10);
		}
	}
	sleep(1);
}
