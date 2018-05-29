<?php
use BH\BHCss\BHCss;

if (strpos(php_sapi_name(), 'cli') === false)  exit;
$second = 0;

global $argc, $argv;
if(sizeof($argv) < 2) exit;
if(in_array('-loop', $argv)){
	while(1){
		if($second % 3 === 0){
			if(in_array('-property-update', $argv)) _ModelDirUpdate();
		}
		if($second % 1 === 0){
			if(in_array('-bhcss', $argv)) convertBHCssDir(_SKINDIR . '/css');
		}
		sleep(1);
		$second++;
	}
}
else _ModelDirUpdate();


function _ModelDirUpdate(){
	if($dh = opendir(_DIR . '/Model')){
		while(($file = readdir($dh)) !== false){
			if($file != '.' && $file != '..'){
				$dest_path = _DIR . '/Model/' . $file;
				if(!is_dir($dest_path)){
					$res = _PropertyUpdate($file);
					if($res->result && $res->message) echo mb_convert_encoding('['. date('Y-m-d H:i:s') . '] ' . $res->message, 'euc-kr', 'utf-8').PHP_EOL;
				}
			}
		}
		closedir($dh);
	}
}


function _PropertyUpdate($file){
	$modelName = explode('.', $file);
	array_pop($modelName);
	$modelName = implode('.', $modelName);
	if(substr($modelName, -5) !== 'Model') return \BH_Result::Init(false);

	$temp = '{{'.chr(0).chr(0).'property}}';

	$source = file_get_contents(_DIR . '/Model/'. $modelName . '.php');
	$data = preg_replace("/(\/\*\*\s*\*\s*Class\s+$modelName.*?\*\/)(.*?)class\s+$modelName\s+extends\s+[\\\]*BH_Model\s*\{/s", $temp."$2class $modelName extends \\BH_Model\r\n{", $source);

	if(strpos($data, $temp) === false) $data = preg_replace("/class\s+$modelName\s+extends\s+[\\\]*BH_Model\s*\{/s", $temp."\r\nclass $modelName extends \\BH_Model\r\n{", $data);
	if(strpos($data, $temp) === false) return \BH_Result::Init(false, $file . ' 파일에서 클래스를 찾지 못했습니다.('.$modelName.')');

	if(strpos($data, '} // __Init') !== false) $data = BH_HtmlCreate::ModifyModel($data);


	preg_match_all('/\$this\-\>data\[[\'|\"]([a-zA-Z0-9_]+)[\'|\"]\]/', $data, $matches);
	$matches = array_unique($matches[1]);
	$propText = "/**\r\n * Class $modelName\r\n *\r\n * @property BH_ModelData[] \$data\r\n";
	foreach($matches as $v){
		$propText .= " * @property BH_ModelData \$_$v\r\n";
	}
	$propText .= " */";
	$data = str_replace($temp, $propText, $data);
	$data = str_replace("\r", '', $data);
	$data = str_replace("\n", "\r\n", $data);
	// if($modelName === 'BannerModel') echo $data;
	if($source === $data) return \BH_Result::Init(false);
	file_put_contents(_DIR . '/Model/' . $modelName . '.php', $data);
	return \BH_Result::Init(true, $file . ' 수정완료');
}

function convertBHCssDir($tempfile_path, $beginIs = true){
	if($beginIs)
		BHCss::$convDirMessage = array('success' => array(), 'fail' => array());

	if(!is_dir($tempfile_path)) return;

	if($dh = opendir($tempfile_path)){
		while(($file = readdir($dh)) !== false){
			if($file != '.' && $file != '..'){
				$dest_path = $tempfile_path . '/' . $file;
				if(is_dir($dest_path)) convertBHCssDir($dest_path, false);
				else{
					if(substr($dest_path, strlen(BHCss::$fileExtension) * (-1)) == BHCss::$fileExtension){
						if(!isset(BHCss::$modifyFilesTime[$dest_path]))
							BHCss::$modifyFilesTime[$dest_path] = 0;

						BHCss::reset();
						$res = _cssConvTimeCheck(BHCss::$modifyFilesTime[$dest_path], $dest_path);
						if(!is_null($res)){
							if($res->result){
								echo '['.date('Y-m-d H:i:s').'] '.$dest_path.' convert success' .PHP_EOL;
							}
							else{
								echo '['.date('Y-m-d H:i:s').'] '.$dest_path.' convert faild' .PHP_EOL;
							}
						}
					}
				}
			}
		}
		closedir($dh);
	}
}

// 파일 변경 시간 체크 후 컨버팅
function _cssConvTimeCheck(&$beforeTime, $path){
	$path = str_replace('\\', '/', $path);

	if(in_array($path, BHCss::$passFiles)) return null;

	if(file_exists($path)){
		$targetTime = filemtime($path);
		if($beforeTime != $targetTime){
			$res = BHCSS::conv($path);
			$beforeTime = $targetTime;
			if($res->result) return (object) array('result' => true, 'message' => '');
			return (object) array('result' => false, 'message' => $res->message);
		}
	}
	return null;
}