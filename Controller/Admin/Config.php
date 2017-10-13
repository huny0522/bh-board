<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class Config{
	private $fileNames;

	public function __construct(){
		$this->fileNames = array('Default' => array('LogoUrl'));
	}

	public function __init(){
		App::$Data['NowMenu'] = '001001';
		CM::AdminAuth();

		App::SetFollowQuery(array('where', 'keyword','page'));
		App::$Layout = '_Admin';
		App::$Data['Code'] = App::$Action;
	}

	public function Index(){
		App::$Data['NowMenu'] = '001001001';
		App::$Data['Code'] = 'Default';
		App::View();
	}

	public function PostWrite(){
		require_once _COMMONDIR.'/FileUpload.php';

		CM::Config($_POST['Code'], '');
		$data = App::$CFG;
		App::$CFG = array();
		$fileNames = isset($this->fileNames[$_POST['Code']]) ? $this->fileNames[$_POST['Code']] : array();

		foreach($fileNames as $v){
			App::$CFG[$_POST['Code']][$v] = (isset($data[$_POST['Code']][$v])) ? $data[$_POST['Code']][$v] : '';
		}

		if(!file_exists( _DATADIR.'/CFG') || !is_dir(_DATADIR.'/CFG')) mkdir(_DATADIR.'/CFG', 0755);
		foreach($_POST as $k => $v){
			if($k === '_delFile') continue;
			App::$CFG[$_POST['Code']][$k] = $v;
		}

		foreach($_FILES as $k => $file){
			if(in_array($k, $fileNames)){
				$fres_em = FileUpload($file, null, '/CFG/files/');

				if(is_string($fres_em)) URLRedirect(-1, $fres_em);
				else if(is_array($fres_em)){
					if(isset($data[$_POST['Code']][$k])) @unlink(_UPLOAD_DIR.$data[$_POST['Code']][$k]);
					App::$CFG[$_POST['Code']][$k] = $fres_em['file'];
				}
			}
		}

		if(is_array(Post('_delFile'))){
			foreach(Post('_delFile') as $v){
				@unlink(_UPLOAD_DIR.$data[$_POST['Code']][$v]);
				App::$CFG[$_POST['Code']][$v] = '';
			}
		}

		$path = _DATADIR.'/CFG/'.$_POST['Code'].'.php';
		$txt = '<?php \BH_Application::$CFG[\''.$_POST['Code'].'\'] = '.var_export(App::$CFG[$_POST['Code']], true).';';
		file_put_contents($path, $txt);
		URLReplace(App::URLAction($_POST['Code'] != 'Default' ? $_POST['Code'] : ''), '설정되었습니다.');
	}
}