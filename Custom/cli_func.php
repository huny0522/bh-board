<?php

if (strpos(php_sapi_name(), 'cli') === false)  exit;

global $argc, $argv;
if(sizeof($argv) < 2) exit;
if(in_array('-loop', $argv)){
	while(1){
		if(in_array('-property-update', $argv)) _ModelDirUpdate();
		sleep(3);
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
