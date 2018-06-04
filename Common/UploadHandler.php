<?php

namespace Common;

require _DIR.'/vendor/blueimp/jquery-file-upload/server/php/UploadHandler.php';

class UploadHandler extends \UploadHandler
{
	protected function get_file_name($file_path, $name, $size, $type, $error, $index, $content_range){
		$name = urlencode($name);
		return parent::get_file_name($file_path, $name, $size, $type, $error, $index, $content_range);
	}
}
