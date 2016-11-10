<?php
$f = 'common.php';
$cnt = 0;
while(!file_exists($f)){
	$f = '../'.$f;
	$cnt ++;
	if($cnt > 10){
		echo 'ERROR';
		exit;
	}
}
require $f;

$sFileInfo = '';
$headers = array();

foreach($_SERVER as $k => $v) {
	if(substr($k, 0, 9) == "HTTP_FILE") {
		$k = substr(strtolower($k), 5);
		$headers[$k] = $v;
	}
}

$file = new stdClass;
$file->name = rawurldecode($headers['file_name']);
$file->size = $headers['file_size'];
$file->content = file_get_contents("php://input");

$filename_ext = strtolower(array_pop(explode('.',$file->name)));
$allow_file = array("jpg", "png", "bmp", "gif");

if(!in_array($filename_ext, $allow_file)) {
	echo "NOTALLOW_".$file->name;
} else {
	$path = 'upload/smarteditor/'.date('ym').'/';
	$uploadDir = $_SERVER['DOCUMENT_ROOT'].'/'.$path;
	//mkdir(_DIR.'/upload/smarteditor/', 0777, true);
	if(!is_dir($uploadDir)){
		mkdir($uploadDir, 0777, true);
	}

	$newPath = $uploadDir.iconv("utf-8", "cp949", $file->name);
	$newFileName = Func::rand_filenm().'.'.$filename_ext;

	if(file_put_contents($uploadDir.$newFileName, $file->content)) {
		$sFileInfo .= "&bNewLine=true";
		$sFileInfo .= "&sFileName=".$newFileName;
		//$sFileInfo .= "&sFileURL=/smarteditor/demo/upload/".$file->name;
		$sFileInfo .= "&sFileURL=/".$path.$newFileName;
	}

	echo $sFileInfo;
}
