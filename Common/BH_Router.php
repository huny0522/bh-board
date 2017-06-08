<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CF;
use \BH_Application as App;

switch(App::$SettingData['GetUrl'][1]){
	case _ADMINURLNAME: // 관리자
		App::$NativeDir = 'Admin';
		App::$BaseDir .= '/'.App::$SettingData['GetUrl'][1];

		if(App::$SettingData['GetUrl'][2] == 'Board'){
			App::$ControllerName = App::$SettingData['GetUrl'][2];
			App::$TID = App::$SettingData['GetUrl'][3];
			App::$Action = App::$SettingData['GetUrl'][4];
			App::$ID = App::$SettingData['GetUrl'][5];
			App::$CtrlUrl = _URL.App::$BaseDir.'/'.App::$ControllerName.'/'.App::$TID;
			App::$Data['NowMenu'] = '002';
		}else{
			App::$ControllerName = App::$SettingData['GetUrl'][2];
			App::$Action = App::$SettingData['GetUrl'][3];
			App::$ID = App::$SettingData['GetUrl'][4];
			App::$CtrlUrl = _URL.App::$BaseDir.'/'.App::$ControllerName;
		}

		App::$SettingData['AdminMenu'] = array(
			'001' => array(
				'Category' => 'Config',
				'Name' => '사이트관리'
			),
			'001001' => array(
				'Category' => 'Config',
				'Name' => '환경설정'
			),
			'001002' => array(
				'Category' => 'BannerManager',
				'Name' => '배너관리'
			),
			'001003' => array(
				'Category' => 'PopupManager',
				'Name' => '팝업관리'
			),
			'002' => array(
				'Category' => 'BoardManager',
				'Name' => '게시판관리'
			),
			'002001' => array(
				'Category' => 'BoardManager',
				'Name' => '게시판관리'
			),
			'003' => array(
				'Category' => 'ContentManager',
				'Name' => '컨텐츠관리'
			),
			'004' => array(
				'Category' => 'MenuManager',
				'Name' => '메뉴관리'
			),
			'005' => array(
				'Category' => 'Member',
				'Name' => '회원관리'
			)
		);
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
		CF::_SetMenu();
		if(!CF::_SetMenuRouter(_URL)){
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
