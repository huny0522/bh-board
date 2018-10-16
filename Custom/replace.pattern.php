<?php
BH_Application::$SettingData['_replace_patterns'] = array(
	// if()
	'/<\?\s*if\((.*?)\)\s*[p|e]\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*if\((.*?)\)\s*v\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*if\((.*?)\)\s*vr\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*if\((.*?)\)\s*vb\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

	// ifp.
	'/<\?\s*if[p|e]\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*ifv\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*ifvr\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*ifvb\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

	// p.
	'/<\?\s*[p|e]\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*v\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*vr\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*vb\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*vstag\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*vbstag\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

	//fn,fq
	'/<\?\s*fn\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*fq\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

	// a. c.
	'/<\?\s*a\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',
	'/<\?\s*c\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

	// inc
	'/<\?\s*inc\s*[\.|\;]\s*(.*?)(;*\s*\?>)/is',

	// mv()
	'/<\?\s*mt\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
	'/<\?\s*mp\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
	'/<\?\s*mv\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
	'/<\?\s*mvn\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
	'/<\?\s*mvr\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',

	'/<\?\s*mvb\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',

	'/<\?\s*minp\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
	'/<\?\s*menum\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',

	// mv.modelName()
	'/<\?\s*mt\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
	'/<\?\s*mp\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
	'/<\?\s*mv\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
	'/<\?\s*mvn\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
	'/<\?\s*mvr\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',

	'/<\?\s*mvb\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',

	'/<\?\s*minp\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',
	'/<\?\s*menum\s*\.\s*(.*?)\s*\(\s*(.*?)(\s*\)\s*;*\s*\?>)/is',

	// img
	'/<img\s*\!\s*(.*?)\s*src=\"(.*?)\"(.*?)>/is',
);

BH_Application::$SettingData['_replace_replace'] = array(
	// if()
	'<?php if($1) echo $2; ?>',
	'<?php if($1) echo GetDBText($2); ?>',
	'<?php if($1) echo GetDBRaw($2); ?>',
	'<?php if($1) echo nl2br(GetDBText($2)); ?>',

	// ifp.
	'<?php if(isset($1)) echo $1; ?>',
	'<?php if(isset($1)) echo GetDBText($1); ?>',
	'<?php if(isset($1)) echo GetDBRaw($1); ?>',
	'<?php if(isset($1)) echo nl2br(GetDBText($1)); ?>',

	// p.
	'<?php echo $1; ?>',
	'<?php echo GetDBText($1); ?>',
	'<?php echo GetDBRaw($1); ?>',
	'<?php echo nl2br(GetDBText($1)); ?>',
	'<?php echo strip_tags(GetDBText($1)); ?>',
	'<?php echo nl2br(GetDBText(strip_tags($1))); ?>',

	// fq, fn
	'<?php echo \BH_Application::GetFollowQuery($1, \'&\'); ?>',
	'<?php echo \BH_Application::GetFollowQuery($1, \'?\'); ?>',

	// a, c
	'<?php echo \BH_Application::URLAction($1); ?>',
	'<?php echo \BH_Application::URLBase($1); ?>',

	// inc
	'<?php if(_DEVELOPERIS === true) ReplaceHTMLFile(_SKINDIR.$1, _HTMLDIR.$1); require _HTMLDIR.$1; ?>',

	// mv()
	'<?php echo $Model->data[$1]->DisplayName; ?>',
	'<?php echo $Model->data[$1]->txt(); ?>',
	'<?php echo $Model->data[$1]->safe(); ?>',
	'<?php echo $Model->data[$1]->num(); ?>',
	'<?php echo $Model->data[$1]->safeRaw(); ?>',

	'<?php echo $Model->data[$1]->safeBr(); ?>',

	'<?php echo $Model->HTMLPrintInput($1); ?>',
	'<?php echo $Model->HTMLPrintEnum($1); ?>',

	// mv.modelName()
	'<?php echo $Ctrl->$1->data[$2]->DisplayName; ?>',
	'<?php echo $Ctrl->$1->data[$2]->txt(); ?>',
	'<?php echo $Ctrl->$1->data[$2]->safe(); ?>',
	'<?php echo $Ctrl->$1->data[$2]->num(); ?>',
	'<?php echo $Ctrl->$1->data[$2]->safeRaw(); ?>',

	'<?php echo $Ctrl->$1->data[$2]->safeBr(); ?>',
	'<?php echo $Ctrl->$1->HTMLPrintInput($2); ?>',
	'<?php echo $Ctrl->$1->HTMLPrintEnum($2); ?>',

	// img
	'<img $1 src="' . _DOMAIN . _URL . '$2" $3>',
);