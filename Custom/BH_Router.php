<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CM;
use \BH_Application as App;

if(PHP_RUN_CLI){
	require _DIR . '/Custom/cli_func.php';
	exit;
}

App::$SettingData['viewMobile'] = _MOBILEIS;

if(Get('_view_mobile') === 'y') $_SESSION['viewMobile'] = true;
if(Get('_view_pc') === 'y') $_SESSION['viewMobile'] = false;

if(isset($_SESSION['viewMobile'])) App::$SettingData['viewMobile'] = $_SESSION['viewMobile'];

switch(App::$SettingData['GetUrl'][1]){
	case _ADMINURLNAME: // 관리자
		App::$NativeDir = 'Admin';
		App::$BaseDir .= '/'.App::$SettingData['GetUrl'][1];

		if(App::$SettingData['GetUrl'][2] == 'Board' || App::$SettingData['GetUrl'][2] == 'Reply'){
			App::$ControllerName = App::$SettingData['GetUrl'][2];

			$temp = explode('-', App::$SettingData['GetUrl'][3]);
			App::$TID = $temp[0];
			if(isset($temp[1])) App::$SUB_TID = $temp[1];

			App::$Action = App::$SettingData['GetUrl'][4];
			App::$ID = App::$SettingData['GetUrl'][5];
			App::$ID2 = App::$SettingData['GetUrl'][6];
			App::$CtrlUrl = _URL.App::$BaseDir.'/'.App::$ControllerName.'/'.App::$SettingData['GetUrl'][3];
			if(App::$SettingData['GetUrl'][2] == 'Board') App::$Data['NowMenu'] = '002';
		}else{
			App::$ControllerName = App::$SettingData['GetUrl'][2];
			App::$Action = App::$SettingData['GetUrl'][3];
			App::$ID = App::$SettingData['GetUrl'][4];
			App::$ID2 = App::$SettingData['GetUrl'][5];
			App::$CtrlUrl = _URL.App::$BaseDir.'/'.App::$ControllerName;
		}

		require _DIR . '/Custom/admin.common.php';

		if(_AJAXIS === true) App::$Layout = null;
		else App::$Layout = '_Admin';
	break;

	case 'Board': // 게시판
	case 'Contents': // 컨텐츠
	case 'Reply': // 댓글
		App::$NativeSkinDir = 'PC';

		App::$ControllerName = App::$SettingData['GetUrl'][1];

		$temp = explode('-', App::$SettingData['GetUrl'][2]);
		App::$TID = $temp[0];
		if(isset($temp[1])) App::$SUB_TID = $temp[1];

		else App::$TID = App::$SettingData['GetUrl'][2];
		App::$Action = App::$SettingData['GetUrl'][3];
		App::$ID = App::$SettingData['GetUrl'][4];
		App::$CtrlUrl = _URL.'/'.App::$ControllerName.'/'.App::$SettingData['GetUrl'][2];
		if(_AJAXIS === true) App::$Layout = null;
		else if(App::$SettingData['GetUrl'][1] == 'Board') App::$Layout = '_Board';
		else if(App::$SettingData['GetUrl'][1] == 'Contents'){
			if(substr(App::$TID, 0, 1) == '~'){
				App::$TID = '';
				App::$Action = App::$SettingData['GetUrl'][2];
				App::$ID = '';
				App::$CtrlUrl = _URL.'/'.App::$ControllerName;
			}
			App::$Layout = '_Default';
		}
	break;

	default:
		// 모바일을 추가할 경우 App::$SettingData['viewMobile'] 를 비교하여 스킨 디렉토리를 변경
		App::$NativeSkinDir = 'PC';

		if(!\Common\MenuHelp::GetInstance()->SetDBMenuRouter(_URL)){
			App::$ControllerName = App::$SettingData['GetUrl'][1];
			App::$Action = App::$SettingData['GetUrl'][2];
			App::$ID = App::$SettingData['GetUrl'][3];
			App::$CtrlUrl = _URL.'/'.App::$ControllerName;
		}
		if(_AJAXIS === true) App::$Layout = null;
		else App::$Layout = _DEFAULT_LAYOUT;
	break;
}
