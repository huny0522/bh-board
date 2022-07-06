<?php
/**
 * 아래 플러그인이 필요합니다.
 * composer require blueimp/jquery-file-upload
 */

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class JQUpload
{
	public function PostIndex(){
		if(StrLenGet('ext')) $possbieExt = explode(',', Get('ext'));
		else $possbieExt = App::$settingData['POSSIBLE_EXT'];

		$option = array(
			'upload_dir' => \Paths::DirOfUpload().'/temp/',
			'upload_url' => '/Data/Upload/temp/',
			'accept_file_types' => '/\.(' . implode('|', $possbieExt) . ')$/i',
			'param_name' => 'temp_upload_file'
		);

		if(StrLenGet('maxfilesize')){
			$s = preg_replace('/[^0-9\.]/', '', Get('maxfilesize'));
			$type = strtolower(substr(Get('maxfilesize'), -2));

			if($type === 'mb') $s = $s * 1024 * 1024;
			else if($type === 'kb') $s = $s * 1024;
			$option['max_file_size'] = $s;
		}

		$upload_handler = new \Common\UploadHandler($option);
	}
}