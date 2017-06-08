<?php
/**
 * Bang Hun.
 * 16.07.10
 */
require _COMMONDIR.'/FileUpload.php';
use \BH_Application as App;
use \BH_Common as CF;

class UploadController{

	// 임시 파일 업로드
	public function PostImageUpload(){
		$this->FileUpload('image');
	}

	public function PostFileUpload(){
		$this->FileUpload();
	}

	private function FileUpload($type = ''){
		DeleteOldTempFiles(_UPLOAD_DIR.'/temp/', strtotime('-6 hours'));
		if(strpos('../', $_FILES['Filedata']['name']) !== false) Redirect('-1');
		$bSuccessUpload = is_uploaded_file($_FILES['Filedata']['tmp_name']);

		// SUCCESSFUL
		if($bSuccessUpload) {
			$tmp_name = $_FILES['Filedata']['tmp_name'];
			$name = $_FILES['Filedata']['name'];

			$temp = explode('.',$name);
			$filename_ext = strtolower(array_pop($temp));
			if(!in_array($filename_ext, App::$SettingData['POSSIBLE_EXT']) || in_array($filename_ext, App::$SettingData['noext'])) JSON(false, _MSG_IMPOSSIBLE_FILE);
			if(($type == 'image' && !in_array($filename_ext, App::$SettingData['IMAGE_EXT'])) || ($type == '' && !in_array($filename_ext, App::$SettingData['POSSIBLE_EXT']))) JSON(false, _MSG_IMPOSSIBLE_FILE);
			$path = '/temp/';
			$uploadDir = _UPLOAD_DIR.$path;
			if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

			$newFileName = RandomFileName().'.'.$filename_ext;


			if($type == 'image' && $filename_ext != 'gif') Thumbnail($tmp_name, $uploadDir.$newFileName, _MAX_IMAGE_WIDTH);
			else @move_uploaded_file($tmp_name, $uploadDir.$newFileName);

			$data['uploadDir'] = _UPLOAD_URL;
			$data['path'] = $path.$newFileName;
			$data['fname'] = $_FILES['Filedata']['name'];
			JSON(true, '', $data);
		}
		// FAILED
		else{
			if($_FILES['Filedata']['error'] ===  UPLOAD_ERR_INI_SIZE) JSON(false, _MSG_FILE_TOO_BIG);
			JSON(false, 'File Upload Error');
		}
	}
}