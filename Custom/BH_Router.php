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

App::$settingData['viewMobile'] = _MOBILEIS;

if(Get('_view_mobile') === 'y') $_SESSION['viewMobile'] = true;
if(Get('_view_pc') === 'y') $_SESSION['viewMobile'] = false;

if(isset($_SESSION['viewMobile'])) App::$settingData['viewMobile'] = $_SESSION['viewMobile'];

switch(App::$settingData['GetUrl'][1]){
	case _ADMINURLNAME: // 관리자
		App::$nativeDir = 'Admin';
		App::$baseDir .= '/'.App::$settingData['GetUrl'][1];

		if(App::$settingData['GetUrl'][2] == 'Board' || App::$settingData['GetUrl'][2] == 'Reply'){
			App::$controllerName = App::$settingData['GetUrl'][2];

			$temp = explode('-', App::$settingData['GetUrl'][3]);
			App::$tid = $temp[0];
			if(isset($temp[1])) App::$sub_tid = $temp[1];

			App::$action = App::$settingData['GetUrl'][4];
			App::$id = App::$settingData['GetUrl'][5];
			App::$id2 = App::$settingData['GetUrl'][6];
			App::$ctrlUrl = _URL.App::$baseDir.'/'.App::$controllerName.'/'.App::$settingData['GetUrl'][3];
			if(App::$settingData['GetUrl'][2] == 'Board') App::$data['NowMenu'] = '002';
		}else{
			App::$controllerName = App::$settingData['GetUrl'][2];
			App::$action = App::$settingData['GetUrl'][3];
			App::$id = App::$settingData['GetUrl'][4];
			App::$id2 = App::$settingData['GetUrl'][5];
			App::$ctrlUrl = _URL.App::$baseDir.'/'.App::$controllerName;
		}

		require _DIR . '/Custom/admin.common.php';

		if(_AJAXIS === true) App::$layout = null;
		else App::$layout = '_Admin';
	break;

	case 'Board': // 게시판
	case 'Contents': // 컨텐츠
	case 'Reply': // 댓글
		App::$nativeSkinDir = 'PC';

		App::$controllerName = App::$settingData['GetUrl'][1];

		$temp = explode('-', App::$settingData['GetUrl'][2]);
		App::$tid = $temp[0];
		if(isset($temp[1])) App::$sub_tid = $temp[1];

		else App::$tid = App::$settingData['GetUrl'][2];
		App::$action = App::$settingData['GetUrl'][3];
		App::$id = App::$settingData['GetUrl'][4];
		App::$ctrlUrl = _URL.'/'.App::$controllerName.'/'.App::$settingData['GetUrl'][2];
		if(_AJAXIS === true) App::$layout = null;
		else if(App::$settingData['GetUrl'][1] == 'Board') App::$layout = '_Board';
		else if(App::$settingData['GetUrl'][1] == 'Contents'){
			if(substr(App::$tid, 0, 1) == '~'){
				App::$tid = '';
				App::$action = App::$settingData['GetUrl'][2];
				App::$id = '';
				App::$ctrlUrl = _URL.'/'.App::$controllerName;
			}
			App::$layout = '_Default';
		}
	break;

	default:
		// 모바일을 추가할 경우 App::$settingData['viewMobile'] 를 비교하여 스킨 디렉토리를 변경
		App::$nativeSkinDir = 'PC';

		if(!\Common\MenuHelp::GetInstance()->SetDBMenuRouter(_URL)){
			App::$controllerName = App::$settingData['GetUrl'][1];
			App::$action = App::$settingData['GetUrl'][2];
			App::$id = App::$settingData['GetUrl'][3];
			App::$ctrlUrl = _URL.'/'.App::$controllerName;
		}
		if(_AJAXIS === true) App::$layout = null;
		else App::$layout = _DEFAULT_LAYOUT;
	break;
}
