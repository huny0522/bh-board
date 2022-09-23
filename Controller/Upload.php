<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use Custom\FlowUpload;
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

	public function FlowUpload(){
		@mkdir(\Paths::DirOfUpload('/chunks_temp_folder'), 0777, true);
		@mkdir(\Paths::DirOfUpload('/temp'), 0777, true);
		$config = new \Flow\Config();
		$config->setTempDir(\Paths::DirOfUpload('/chunks_temp_folder'));
		$request = new \Flow\Request();
		$uploadFolder = \Paths::DirOfUpload('/temp'); // Folder where the file will be stored

		$name = $request->getFileName();
		$temp = explode('.',$name);
		$filename_ext = strtolower(array_pop($temp));
		$uploadFileName = \_ModelFunc::RandomFileName().'.'.$filename_ext;

		$uploadPath = $uploadFolder.'/'.$uploadFileName;
		if (\Flow\Basic::save($uploadPath, $config, $request)) {
			$data['uploadDir'] = \Paths::UrlOfUpload();
			$data['path'] = '/temp/'.$uploadFileName;
			$data['fname'] = $request->getFileName();
			JSON(true, '', $data);
		} else {
			// This is not a final chunk or request is invalid, continue to upload.
		}
	}

	public function PostFlowUpload(){
		$this->FlowUpload();
	}

	/**
	 * TODO : 로드밸런싱용
	 * 미완성
	 */
	public function PostSubUpload(){
		if(EmptyPost('type')) JSON(false, 'NO HAVE TYPE FIELD');
		if(EmptyPost('path')) JSON(false, 'NO HAVE PATH FIELD');
		if(EmptyPost('file')) JSON(false, 'NO HAVE FILE FIELD');
		if(!isset(App::$settingData['loadBalancingSubIp'])) JSON(false, 'NOT SETTING SUB IP');
		if(!in_array($_SERVER['REMOTE_ADDR'], App::$settingData['loadBalancingSubIp'])) JSON(false, 'NO SUB IP');

		$type = Post('type');
		$path = Post('path');
		$defaultDir = _UPLOAD_DIR;
		if($type === 'data') $defaultDir = _DATADIR;
		else if($type !== 'uploadFile') JSON(false, 'TYPE IS WRONG');

		$ex = explode('/', $path);
		array_pop($ex);
		$dir = $defaultDir . implode('/', $ex);
		@mkdir($dir, 0777, true);
		@chmod($dir, 0777);

		$str = base64_decode($_POST['file']);
		$res = file_put_contents($defaultDir . $path, $str, FILE_BINARY);
		JSON(true, '', $res);
	}

	/**
	 * TODO : 로드밸런싱용
	 * 미완성
	 */
	public function PostSubUnlinkFile(){
		if(EmptyPost('type')) JSON(false, 'NO HAVE TYPE FIELD');
		if(EmptyPost('path')) JSON(false, 'NO HAVE PATH FIELD');
		if(!isset(App::$settingData['loadBalancingSubIp'])) JSON(false, 'NOT SETTING SUB IP');
		if(!in_array($_SERVER['REMOTE_ADDR'], App::$settingData['loadBalancingSubIp'])) JSON(false, 'NO SUB IP');

		$type = Post('type');
		$path = Post('path');
		$defaultDir = _UPLOAD_DIR;
		if($type === 'data') $defaultDir = _DATADIR;
		else if($type !== 'uploadFile') JSON(false, 'TYPE IS WRONG');

		@UnlinkImage($defaultDir . $path);
		JSON(true);
	}
}
