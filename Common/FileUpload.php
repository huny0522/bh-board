<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CF;
use \BH_Application as App;

function RandomFileName(){
	$t = microtime();
	$t = explode(' ',$t);
	$t2 = (double)($t[0]*1000000);
	$t3 = toBase(rand(0,3843)).toBase($t[1],36).toBase($t2,36);
	return $t3;
}

/**
 * @param array $files : Post $_FILE Array Data
 * @param array $possible_ext
 * @param string $path
 *
 * @return array
 */
function FileUploadArray($files, $possible_ext = null, $path = '/data/'){
	$filedata = array();
	if($files['name']){
		if(is_array($files['name'])){
			foreach($files as $k=>$v){
				foreach($v as $k2=>$v2){
					$filedata[$k2][$k] = $v2;
				}
			}
			//print_r($filedata);exit;
			foreach($filedata as $v){
				$res[]= FileUpload($v, $possible_ext, $path);
			}
		}else{
			$res[]= FileUpload($files, $possible_ext, $path);
		}
	}
	return $res;
}

/**
 * @param array $files : Post $_FILE Data
 * @param array $possible_ext
 * @param string $path
 *
 * @return bool|string
 */
function FileUpload($files, $possible_ext = null, $path = '/data/'){
	if($files['name']){
		$ext = explode('.', $files['name']);
		$ext = $ext[sizeof($ext)-1];
		if(in_array($ext, App::$SettingData['noext'])){
			return 'noext';
		}
		else if(!in_array($ext, App::$SettingData['POSSIBLE_EXT'])){
			return 'noext';
		}
		else if($possible_ext && !in_array($ext, $possible_ext)){
			return 'noext';
		}

		if(!is_dir(_UPLOAD_DIR.$path)){
			@mkdir(_UPLOAD_DIR.$path, 0777, true);
		}

		$newFileName = '';
		while($newFileName == '' || file_exists(_UPLOAD_DIR.$path.$newFileName.'.'.$ext)){
			$newFileName = RandomFileName();
		}


		copy($files['tmp_name'], _UPLOAD_DIR.$path.$newFileName.'.'.$ext);
		$res['path'] = $path;
		$res['name'] = $newFileName;
		$res['ext'] = $ext;
		$res['file'] = $path.$newFileName.'.'.$ext;
		return $res;
	}else{
		return false;
	}
}

function Thumbnail($source, $thumb, $width, $height = 0){
	// 썸네일의 넓이가 넘어오지 않으면 에러
	if (!$width)
		return -1;
	if (!$thumb)
		$thumb = $source;
	$size = getimagesize($source);
	if ($size[2] == 1)
		$source = imagecreatefromgif($source);
	else if ($size[2] == 2)
		$source = imagecreatefromjpeg($source);
	else if ($size[2] == 3)
		$source = imagecreatefrompng($source);
	else
		return -2;
	// 썸네일 이미지 넓이 보다 원본이미지의 넓이가 작다면 그냥 원본이미지가 썸네일이 됨
	if ($width > $size[0]){
		$target = imagecreatetruecolor($size[0], $size[1]);
		if ($size[2] == 3) {
			imagealphablending($target , 0);
			imagesavealpha($target , 1);
		}
		imagecopyresampled($target, $source, 0, 0, 0, 0, $size[0], $size[1], $size[0], $size[1]);
	}
	else{
		// 썸네일 높이가 넘어왔다면 비율에 의해 이미지를 생성하지 않음
		if ($height){
			// 원본이미지를 썸네일로 복사
			// 1000x1500 -> 500x500 으로 복사되는 형식이므로 이미지가 일그러진다.
			$comp_height = $height;
		}
		else{
			// 원래 이미지와 썸네일 이미지와의 비율
			$rate = round($width / $size[0], 2); // 소수점 2자리 , 소수점 3자리에서 반올림됨
			// 비율에 의해 계산된 높이
			$comp_height = floor($size[1] * $rate); // 소수점 이하 버림
		}
		$target = imagecreatetruecolor($width, $comp_height);
		if ($size[2] == 3) {
			imagealphablending($target , 0);
			imagesavealpha($target , 1);
		}
		imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $comp_height, $size[0], $size[1]);
	}
	if ($size[2] == 3) {
		imagepng($target, $thumb, 9);
	} else {
		imagejpeg($target, $thumb, 90);
	}
	imagedestroy($target);
	@chmod($thumb, 0666); // 추후 삭제를 위하여 파일모드 변경
	return 1;
}
