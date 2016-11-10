<?php
// default redirection
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

$url = $_REQUEST["callback"].'?callback_func='.$_REQUEST["callback_func"];
$bSuccessUpload = is_uploaded_file($_FILES['Filedata']['tmp_name']);

// SUCCESSFUL
if($bSuccessUpload) {
	$tmp_name = $_FILES['Filedata']['tmp_name'];
	$name = $_FILES['Filedata']['name'];

	$filename_ext = strtolower(array_pop(explode('.',$name)));
	$allow_file = array("jpg", "png", "bmp", "gif");

	if(!in_array($filename_ext, $allow_file)) {
		$url .= '&errstr='.$name;
	} else {

		$path = '/smarteditor/'.date('ym').'/';
		$uploadDir = _UPLOAD_DIR.$path;
		//mkdir(_DIR.'/upload/smarteditor/', 0777, true);
		if(!is_dir($uploadDir)){
			mkdir($uploadDir, 0777, true);
		}


		$newPath = $uploadDir.urlencode($_FILES['Filedata']['name']);
		$newFileName = Func::rand_filenm().'.'.$filename_ext;

		@move_uploaded_file($tmp_name, $uploadDir.$newFileName);

		$url .= "&bNewLine=true";
		$url .= "&sFileName=".$newFileName;
		//$url .= "&sFileURL=/smarteditor/demo/upload/".urlencode(urlencode($name));
		$url .= "&sFileURL=/"._UPLOAD_URL.$path.$newFileName;
	}
}
// FAILED
else {
	$url .= '&errstr=error';
}

header('Location: '. $url);
