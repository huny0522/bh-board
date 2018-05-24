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
		if(_POSTIS === true && is_array(Post('file_field')) && Post('Code')){
			$this->fileNames = array(Post('Code') => Post('file_field'));
		}
		else $this->fileNames = array();
	}

	public function __init(){
		App::$Data['NowMenu'] = '001001';
		CM::AdminAuth();

		App::SetFollowQuery(array('where', 'keyword','page'));
		App::$Layout = '_Admin';
		App::$Data['Code'] = App::$Action;
		if(App::$Action === 'SuperSet' && $_SESSION['member']['level'] < _SADMIN_LEVEL) URLRedirect(-1, _MSG_WRONG_CONNECTED);
	}

	public function Index(){
		App::$Data['NowMenu'] = '001001';
		App::$Data['Code'] = 'Default';
		App::View();
	}

	public function PostWrite(){

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

		if(is_array(Post('_delFile'))){
			foreach(Post('_delFile') as $v){
				preg_match('/([a-zA-Z0-9_]+)\[([0-9]*?)\]/', $v, $matches);
				if(isset($matches[2])){
					unset(App::$CFG[$_POST['Code']][$matches[1]][$matches[2]]);
				}
				else{
					App::$CFG[$_POST['Code']][$v] = '';
				}
			}
		}

		foreach($_FILES as $k => $file){
			if(in_array($k, $fileNames)){
				if(is_array($file['name'])){
					$fres_em = \_ModelFunc::FileUploadArray($file, null, '/CFG/files/');
					foreach($fres_em as $row){
						if(is_array($row)){
							App::$CFG[$_POST['Code']][$k][] = $row['file'];
							App::$CFG[$_POST['Code']][$k] = array_values(App::$CFG[$_POST['Code']][$k]);
						}
					}
				}
				else{
					$fres_em = \_ModelFunc::FileUpload($file, null, '/CFG/files/');

					if(is_string($fres_em)) URLRedirect(-1, $fres_em);
					else if(is_array($fres_em)){
						if(isset($data[$_POST['Code']][$k])) @unlink(_UPLOAD_DIR.$data[$_POST['Code']][$k]);
						App::$CFG[$_POST['Code']][$k] = $fres_em['file'];
					}
				}
			}
		}

		$path = _DATADIR.'/CFG/'.$_POST['Code'].'.php';
		$txt = '<?php return;/*'.serialize(App::$CFG[$_POST['Code']]);
		file_put_contents($path, $txt);
		if(Get('redirect_url')){
			URLReplace(Get('redirect_url'), '설정되었습니다.');
		}
		else URLReplace(App::URLAction($_POST['Code'] != 'Default' ? $_POST['Code'] : ''), '설정되었습니다.');
	}

	public function Content(){
		App::$Data['NowMenu'] = '001050';
		reset(App::$Data['menu']);
		if(!App::$ID) App::$ID = key(App::$Data['menu']);
		App::$Data['Code'] = App::$ID;


		App::View();
	}
}