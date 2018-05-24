<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CM;
use \BH_Application as App;

switch(App::$SettingData['GetUrl'][1]){
	case _ADMINURLNAME: // 관리자
		App::$NativeDir = 'Admin';
		App::$BaseDir .= '/'.App::$SettingData['GetUrl'][1];

		if(App::$SettingData['GetUrl'][2] == 'Board' || App::$SettingData['GetUrl'][2] == 'Reply'){
			App::$ControllerName = App::$SettingData['GetUrl'][2];
			App::$TID = App::$SettingData['GetUrl'][3];
			App::$Action = App::$SettingData['GetUrl'][4];
			App::$ID = App::$SettingData['GetUrl'][5];
			App::$CtrlUrl = _URL.App::$BaseDir.'/'.App::$ControllerName.'/'.App::$TID;
			if(App::$SettingData['GetUrl'][2] == 'Board') App::$Data['NowMenu'] = '002';
		}else{
			App::$ControllerName = App::$SettingData['GetUrl'][2];
			App::$Action = App::$SettingData['GetUrl'][3];
			App::$ID = App::$SettingData['GetUrl'][4];
			App::$CtrlUrl = _URL.App::$BaseDir.'/'.App::$ControllerName;
		}

		require _DIR . '/Custom/admin.common.php';

		if(_AJAXIS === true) App::$Layout = null;
		else App::$Layout = '_Admin';
	break;

	case 'Board': // 게시판
	case 'Contents': // Contents
	case 'Reply': // 댓글
		App::$ControllerName = App::$SettingData['GetUrl'][1];
		App::$TID = App::$SettingData['GetUrl'][2];
		App::$Action = App::$SettingData['GetUrl'][3];
		App::$ID = App::$SettingData['GetUrl'][4];
		App::$CtrlUrl = _URL.'/'.App::$ControllerName.'/'.App::$TID;
		if(App::$SettingData['GetUrl'][1] == 'Board') App::$Layout = '_Board';
		if(_AJAXIS === true) App::$Layout = null;
	break;

	default:
		CM::_SetMenu();
		if(!CM::_SetMenuRouter(_URL)){
			App::$ControllerName = App::$SettingData['GetUrl'][1];
			App::$Action = App::$SettingData['GetUrl'][2];
			App::$ID = App::$SettingData['GetUrl'][3];
			App::$CtrlUrl = _URL.'/'.App::$ControllerName;
		}
		if(_AJAXIS === true) App::$Layout = null;
		else App::$Layout = _DEFAULT_LAYOUT;
	break;
}

if(App::$ControllerName != 'Mypage'){
	$_SESSION['MyInfoView'] = false;
	unset($_SESSION['MyInfoView']);
}
