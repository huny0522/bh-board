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

		CM::Config(Post('Code'), '');
		$data = App::$CFG;
		App::$CFG = array();
		$fileNames = isset($this->fileNames[Post('Code')]) ? $this->fileNames[Post('Code')] : array();

		foreach($fileNames as $v){
			App::$CFG[Post('Code')][$v] = (isset($data[Post('Code')][$v])) ? $data[Post('Code')][$v] : '';
		}

		if(!file_exists( _DATADIR.'/CFG') || !is_dir(_DATADIR.'/CFG')) mkdir(_DATADIR.'/CFG', 0755);
		foreach($_POST as $k => $v){
			if($k === '_delFile') continue;
			App::$CFG[Post('Code')][$k] = $v;
		}

		if(is_array(Post('_delFile'))){
			foreach(Post('_delFile') as $v){
				preg_match('/([a-zA-Z0-9_]+)\[([0-9]*?)\]/', $v, $matches);
				if(isset($matches[2])){
					unset(App::$CFG[Post('Code')][$matches[1]][$matches[2]]);
				}
				else{
					App::$CFG[Post('Code')][$v] = '';
				}
			}
		}

		foreach($_FILES as $k => $file){
			if(in_array($k, $fileNames)){
				if(is_array($file['name'])){
					$fres_em = \_ModelFunc::FileUploadArray($file, null, '/CFG/files/');
					foreach($fres_em as $row){
						if(is_array($row)){
							App::$CFG[Post('Code')][$k][] = $row['file'];
							App::$CFG[Post('Code')][$k] = array_values(App::$CFG[Post('Code')][$k]);
						}
					}
				}
				else{
					$fres_em = \_ModelFunc::FileUpload($file, null, '/CFG/files/');

					if(is_string($fres_em)) URLRedirect(-1, $fres_em);
					else if(is_array($fres_em)){
						if(isset($data[Post('Code')][$k])) @unlink(_UPLOAD_DIR.$data[Post('Code')][$k]);
						App::$CFG[Post('Code')][$k] = $fres_em['file'];
						if(class_exists('\\PHP_ICO')){
							if(Post('Code') === 'Default' && $k == 'FaviconPng'){
								$temp = explode('.', $fres_em['file']);
								array_pop($temp);
								$pico = new \PHP_ICO(_UPLOAD_DIR . $fres_em['file'], array( array( 16, 16 ), array( 32, 32 ), array( 64, 64 ) ));
								$pico->save_ico(_DIR . '/favicon.ico');
							}
						}
					}
				}
			}
		}

		$path = _DATADIR.'/CFG/'.Post('Code').'.php';
		$txt = '<?php return;/*'.json_encode(App::$CFG[Post('Code')]);
		file_put_contents($path, $txt);
		if(Get('redirect_url')){
			URLReplace(Get('redirect_url'), '설정되었습니다.');
		}
		else URLReplace(App::URLAction(Post('Code') != 'Default' ? Post('Code') : ''), '설정되었습니다.');
	}

	public function Content(){
		App::$Data['NowMenu'] = '001050';
		reset(App::$Data['menu']);
		if(!App::$ID) App::$ID = key(App::$Data['menu']);
		App::$Data['Code'] = App::$ID;


		App::View();
	}

	public function _ImageInput($code, $key){
		$h = '<input type="hidden" name="file_field[]" value="'. $key . '">';
		if(CM::Config($code, $key)){
			$h .= '<img src="' . _UPLOAD_URL. GetDBText(CM::Config($code, $key)) . '" style="max-width:100px; max-height:100px;">';
		}
		$h .= '<input type="file" name="' . $key .'" value="' . GetDBText(CM::Config($code, $key)) . '" accept="image/png"> <label class="checkbox"><input type="checkbox" name="_delFile[]" value="' . $key . '"><span>삭제</span></label>';
		return $h;
	}
}