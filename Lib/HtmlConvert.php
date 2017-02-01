<?php
/**
 * Bang Hun.
 * 16.07.10
 */

function ReplaceHTMLFile($source, $target){

	$patterns = array(
		'/<\?\s*p\.\s*(.*?)(\s*\?>|;\s*\?>)/',
		'/<\?\s*v\.\s*(.*?)(\s*\?>|;\s*\?>)/',
		'/<\?\s*vr\.\s*(.*?)(\s*\?>|;\s*\?>)/',
		'/<\?\s*vb\.\s*(.*?)(\s*\?>|;\s*\?>)/',
		'/<\?\s*fn\.\s*(.*?)(\s*\?>|;\s*\?>)/',
		'/<\?\s*fq\.\s*(.*?)(\s*\?>|;\s*\?>)/',
		'/<\?\s*a\.\s*(.*?)(\s*\?>|;\s*\?>)/',
		'/<\?\s*c\.\s*(.*?)(\s*\?>|;\s*\?>)/',
		'/<\?\s*inc\.\s*(.*?)(\s*\?>|;\s*\?>)/'
		);

	$replace = array(
		'<?php echo $1; ?>',
		'<?php echo GetDBText($1); ?>',
		'<?php echo GetDBRaw($1); ?>',
		'<?php echo nl2br(GetDBText($1)); ?>',
		'<?php echo $this->GetFollowQuery($1, \'&\'); ?>',
		'<?php echo $this->GetFollowQuery($1, \'?\'); ?>',
		'<?php echo $this->URLAction($1); ?>',
		'<?php echo $this->URLBase($1); ?>',
		'<?php if(_DEVELOPERIS === true) ReplaceHTMLFile(_SKINDIR.$1, _HTMLDIR.$1); require _HTMLDIR.$1; ?>',
	);
	/*$replace[] = 'return \'<?php echo \'.stripslashes(trim($matches[1])). \'; ?>\';';
	$replace[] = 'return \'<?php echo GetDBText(\'.stripslashes(trim($matches[1])). \'); ?>\';';
	$replace[] = 'return \'<?php echo GetDBRaw(\'.stripslashes(trim($matches[1])). \'); ?>\';';
	$replace[] = 'return \'<?php echo $this->GetFollowQuery(\'.stripslashes(trim($matches[1])). \',\'.chr(39).\'&\'.chr(39).\'); ?>\';';
	$replace[] = 'return \'<?php echo $this->GetFollowQuery(\'.stripslashes(trim($matches[1])). \',\'.chr(39).\'?\'.chr(39).\'); ?>\';';
	$replace[] = 'return \'<?php echo $this->URLAction(\'.stripslashes(trim($matches[1])). \'); ?>\';';
	$replace[] = 'return \'<?php echo $this->URLBase(\'.stripslashes(trim($matches[1])). \'); ?>\';';
	$replace[] = 'return "<?php".chr(10)."if(_DEVELOPERIS === true) ReplaceHTMLFile('.$source_define.'.".stripslashes(trim($matches[1])).", '.$target_define.'.".stripslashes(trim($matches[1]))."); require '.$target_define.'.".stripslashes(trim($matches[1])).";".chr(10)."?>";';*/

	$a = explode('/', $target);
	$filename = array_pop($a);
	$path = implode('/', $a).'/';

	if(file_exists($source)){
		if(!is_dir($path)) mkdir($path, 0777, true);
		$f = file_get_contents($source);
		$f = str_replace("\r",'', $f);
		$f = preg_replace(
			array(
				'/(<\!--)([^\[].*?)(\-\->)/s',
				'/(\/\*)(.*?)(\*\/)/s',
				'/\n\s*/'
			),
			array(
				'',
				'',
				"\n"
			), $f);
		if(_REMOVE_SPACE === true){
			$f = preg_replace(
				array(
					'/>\s*</s'
				),
				array(
					'><'
				), $f);
		}
		$f = preg_replace($patterns, $replace, $f);
		/*foreach ($patterns as $key => $value) {
			$f = preg_replace_callback($value,
				create_function('$matches', $replace[$key]),
				$f);
		}*/

		file_put_contents($target, $f);
	}
}

function ReplaceHTMLAll($tempfile_path, $target_path) {
	if(!$target_path) return;
	if(is_dir($tempfile_path)) {
		if($dh = opendir($tempfile_path)) {
			while(($file = readdir($dh)) !== false) {
				if($file != "." && $file != "..") {
					$dest_path = "{$tempfile_path}/{$file}";
					if(is_dir($dest_path)) {
						ReplaceHTMLAll($dest_path, $target_path.'/'.$file);
					} else if(substr($file, -5) == '.html'){
						ReplaceHTMLFile($dest_path, $target_path.'/'.$file);
					}
				}
			}
			closedir($dh);
		}
	}
}

function delTree($dir) {
	if(!is_dir($dir)) return;
	$files = array_diff(scandir($dir), array('.','..'));
	foreach ($files as $file) {
		(is_dir($dir.'/'.$file)) ? delTree($dir.'/'.$file) : unlink($dir.'/'.$file);
	}
	return rmdir($dir);
}

