<?php
/**
 * Bang Hun.
 * 16.07.10
 */
require _COMMONDIR.'/FileUpload.php';

class UploadController extends \BH_Controller{
	public function __Init(){
	}

	// 임시 파일 업로드
	public function PostImageUpload(){
		$this->FileUpload('image');
	}

	public function PostFileUpload(){
		$this->FileUpload();
	}

	public function PostDelete(){
		@unlink(_UPLOAD_DIR.$_POST['filePath']);
		echo json_encode(array('result' => true));
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

			if(($type == 'image' && !in_array($filename_ext, self::$IMAGE_EXT)) || ($type == '' && !in_array($filename_ext, self::$POSSIBLE_EXT))) {
				echo json_encode(array('result' => false, 'fname'=>$name));
				exit;
			}
			else {
				$path = '/temp/';
				$uploadDir = _UPLOAD_DIR.$path;
				if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

				$newFileName = RandomFileName().'.'.$filename_ext;


				if($type == 'image' && $filename_ext != 'gif') Thumbnail($tmp_name, $uploadDir.$newFileName, _MAX_IMAGE_WIDTH);
				else @move_uploaded_file($tmp_name, $uploadDir.$newFileName);

				$data['uploadDir'] = _UPLOAD_URL;
				$data['path'] = $path.$newFileName;
				$data['fname'] = $_FILES['Filedata']['name'];
				echo json_encode(array('result' => true, 'data' => $data));
				exit;
			}
		}
		// FAILED
		else echo json_encode(array('result' => false));
	}
}