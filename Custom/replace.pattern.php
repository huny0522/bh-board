<?php
$_rpData = array(
	// ifp.
	array(
		'pattern' => '/<\?\s*if[p|e]\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php if(isset($1)) echo $1; ?>'
	),
	array(
		'pattern' => '/<\?\s*ifv\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php if(isset($1)) echo v($1); ?>'
	),
	array(
		'pattern' => '/<\?\s*ifvr\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php if(isset($1)) echo vr($1); ?>'
	),
	array(
		'pattern' => '/<\?\s*ifvb\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php if(isset($1)) echo vb($1); ?>'
	),

	// p.
	array(
		'pattern' => '/<\?\s*[p|e]\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php echo $1; ?>'
	),
	array(
		'pattern' => '/<\?\s*v\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php echo v($1); ?>'
	),
	array(
		'pattern' => '/<\?\s*vr\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php echo vr($1); ?>'
	),
	array(
		'pattern' => '/<\?\s*vb\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php echo vb($1); ?>'
	),
	array(
		'pattern' => '/<\?\s*vstag\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php echo strip_tags(v($1)); ?>'
	),
	array(
		'pattern' => '/<\?\s*vbstag\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php echo nl2br(v(strip_tags($1))); ?>'
	),

	//fqn,fqq
	array(
		'pattern' => '/<\?\s*fqn\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php echo \BH_Application::GetFollowQuery($1, \'&\'); ?>'
	),
	array(
		'pattern' => '/<\?\s*fqq\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php echo \BH_Application::GetFollowQuery($1, \'?\'); ?>'
	),

	// a. c.
	array(
		'pattern' => '/<\?\s*a\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php echo \BH_Application::URLAction($1); ?>'
	),
	array(
		'pattern' => '/<\?\s*c\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php echo \BH_Application::URLBase($1); ?>'
	),

	// inc
	array(
		'pattern' => '/<\?\s*inc\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php \$p = Paths::GetSkinPathFromDir($1, __DIR__); if(\$p !== null){CheckReplaceHTMLFile(\$p[\'skinPath\'], \$p[\'htmlPath\']); require \$p[\'htmlPath\'];} else if(\BHG::\$isDeveloper === true) echo \'<div>\' . v($1) . \' not exists.</div>\'; ?>'
	),

	// module
	array(
		'pattern' => '/<\?\s*module\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
		'replace' => '<?php \$_module_path = ViewModuleGetPath($1); foreach(\$_module_path as \$mp){if(\$mp[\'type\'] === \'js\') echo \'<script>\' . PHP_EOL; if(\$mp[\'id\']) \$moduleId = \$mp[\'id\']; else \$moduleId = null; require \$mp[\'path\']; if(\$mp[\'type\'] === \'js\') echo PHP_EOL . \'</script>\' . PHP_EOL;} ?>'
	),

	// mv()
	array(
		'pattern' => '/<\?\s*mt\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Model->data[$1]->displayName; ?>'
	),
	array(
		'pattern' => '/<\?\s*mp\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Model->data[$1]->value; ?>'
	),
	array(
		'pattern' => '/<\?\s*mv\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Model->data[$1]->Safe(); ?>'
	),
	array(
		'pattern' => '/<\?\s*mvn\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Model->data[$1]->Num(); ?>'
	),
	array(
		'pattern' => '/<\?\s*mvr\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Model->data[$1]->SafeRaw(); ?>'
	),
	array(
		'pattern' => '/<\?\s*mvb\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Model->data[$1]->SafeBr(); ?>'
	),
	array(
		'pattern' => '/<\?\s*minp\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Model->HTMLPrintInput($1); ?>'
	),
	array(
		'pattern' => '/<\?\s*menum\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Model->HTMLPrintEnum($1); ?>'
	),

	// mv.modelName()
	array(
		'pattern' => '/<\?\s*mt\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Ctrl->$1->data[$2]->displayName; ?>'
	),
	array(
		'pattern' => '/<\?\s*mp\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Ctrl->$1->data[$2]->value; ?>'
	),
	array(
		'pattern' => '/<\?\s*mv\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Ctrl->$1->data[$2]->Safe(); ?>'
	),
	array(
		'pattern' => '/<\?\s*mvn\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Ctrl->$1->data[$2]->Num(); ?>'
	),
	array(
		'pattern' => '/<\?\s*mvr\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Ctrl->$1->data[$2]->SafeRaw(); ?>'
	),
	array(
		'pattern' => '/<\?\s*mvb\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Ctrl->$1->data[$2]->SafeBr(); ?>'
	),
	array(
		'pattern' => '/<\?\s*minp\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Ctrl->$1->HTMLPrintInput($2); ?>'
	),
	array(
		'pattern' => '/<\?\s*menum\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
		'replace' => '<?php echo $Ctrl->$1->HTMLPrintEnum($2); ?>'
	),

	// img
	array(
		'pattern' => '/<img\s*\!\s*(.*?)\s*src=\"(.*?)\"(.*?)>/is',
		'replace' => '<img $1 src="' . _DOMAIN . \Paths::Url() . '$2" $3>'
	),

	array(
		'pattern' => '#\=\s*([\'"])/' . \Paths::NameOfSkin() . '/#s',
		'replace' => '=$1' . \Paths::UrlOfSkin() . '/'
	),

	// '<?=' -> '<?php echo'
	// '<?' -> '<?php'
	array(
		'pattern' => '#<\?(?!php|=)(.*?)\?>#is',
		'replace' => '<?php $1 ?>'
	),
	array(
		'pattern' => '#<\?=(.*?)\?>#is',
		'replace' => '<?php echo ($1); ?>'
	),
	array(
		'pattern' => '#\?>(\r\n|\n|\s)*<\?php#is',
		'replace' => ''
	),
	array(
		'pattern' => '#<\?php(\?(?!>)|[^\?]*)?([^\s\;\}\{])\s*\?>#is',
		'replace' => '<?php $1$2; ?>'
	),
);
BH_Application::$settingData['_replace_patterns'] = array();

BH_Application::$settingData['_replace_replace'] = array();

for($i = 0, $m = sizeof($_rpData); $i < $m; $i++){
	BH_Application::$settingData['_replace_patterns'][] = $_rpData[$i]['pattern'];
	BH_Application::$settingData['_replace_replace'][] = $_rpData[$i]['replace'];
}
