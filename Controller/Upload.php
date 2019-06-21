<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class Upload{

	// 임시 파일 업로드
	public function PostImageUpload(){
		$this->FileUpload('image');
	}

	public function PostFileUpload(){
		$this->FileUpload();
	}

	private function FileUpload($type = ''){
		DeleteOldTempFiles(\Paths::DirOfUpload().'/temp/', strtotime('-6 hours'));
		if(strpos('../', $_FILES['Filedata']['name']) !== false) URLReplace('-1');
		$bSuccessUpload = is_uploaded_file($_FILES['Filedata']['tmp_name']);

		// SUCCESSFUL
		if($bSuccessUpload) {
			$tmp_name = $_FILES['Filedata']['tmp_name'];
			$name = $_FILES['Filedata']['name'];

			$temp = explode('.',$name);
			$filename_ext = strtolower(array_pop($temp));
			if(!in_array($filename_ext, App::$settingData['POSSIBLE_EXT']) || in_array($filename_ext, App::$settingData['noext'])) JSON(false, App::$lang['MSG_IMPOSSIBLE_FILE']);
			if(($type == 'image' && !in_array($filename_ext, App::$settingData['IMAGE_EXT'])) || ($type == '' && !in_array($filename_ext, App::$settingData['POSSIBLE_EXT']))) JSON(false, App::$lang['MSG_IMPOSSIBLE_FILE']);
			$path = '/temp/';
			$uploadDir = \Paths::DirOfUpload().$path;
			if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

			$newFileName = \_ModelFunc::RandomFileName().'.'.$filename_ext;

			@move_uploaded_file($tmp_name, $uploadDir.$newFileName);

			$data['uploadDir'] = \Paths::UrlOfUpload();
			$data['path'] = $path.$newFileName;
			$data['fname'] = $_FILES['Filedata']['name'];
			JSON(true, '', $data);
		}
		// FAILED
		else{
			if($_FILES['Filedata']['error'] ===  UPLOAD_ERR_INI_SIZE) JSON(false, App::$lang['MSG_FILE_TOO_BIG']);
			JSON(false, 'File Upload Error');
		}
	}
}