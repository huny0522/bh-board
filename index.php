<?php

error_reporting(E_ALL);

define('_BH_', true);
define('_DIR', str_replace(chr(92), '/', dirname(__FILE__)));

require _DIR . '/BHCss/BHCss.php';

use BH\BHCss\BHCss;
BHCss::setNL(false);


$dir = $oriDir = isset($argv[1]) ? $argv[1] : '';
if (strlen($dir) && $dir[0] !== '/' && $dir[0] !== '\\')
	$dir = '/' . str_replace('\\', '/', $dir);

if (file_exists(_DIR . $dir) && is_dir(_DIR . $dir)) {
	$dir = _DIR . $dir;
}

if (!file_exists($dir) || !is_dir($dir)) {
	echo chr(10) . '[' . $oriDir . '] 폴더가 존재하지 않습니다.' . chr(10) . chr(10);
	exit;
}

echo _DIR . ' : Begin Convert(path : ' . $dir . ')' . chr(10) . chr(10);
while (1) {
	convertBHCssDir($dir);
		sleep(1);
}

$GLOBALS['modifyFileTimes'] = array();

function convertBHCssDir($tempfile_path) {
	if (!is_dir($tempfile_path))
		return;

	if ($dh = opendir($tempfile_path)) {
		while (($file = readdir($dh)) !== false) {
			if ($file != '.' && $file != '..') {
				$dest_path = $tempfile_path . '/' . $file;
				if (is_dir($dest_path))
					convertBHCssDir($dest_path);
				else {
					if (substr($dest_path, strlen(BHCss::$fileExtension) * (-1)) == BHCss::$fileExtension) {
						if (!isset($GLOBALS['modifyFileTimes'][$dest_path]))
							$GLOBALS['modifyFileTimes'][$dest_path] = 0;

						BHCss::reset();
						$res = BHCss::convTimeCheck($GLOBALS['modifyFileTimes'][$dest_path], $dest_path, $tempfile_path . '/' . substr($file, 0, strlen(BHCss::$fileExtension) * (-1)) . '.css');
						if (!is_null($res)) {
							if ($res->result) {
								echo $dest_path . ' : Convert[OK]' . chr(10);
							} else {
								echo $dest_path . ' : Convert[FALSE], ' . $res->message . chr(10);
							}
						}
					}
				}
			}
		}
		closedir($dh);
	}
}
