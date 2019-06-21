<?php
use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

/*
// 삭제 불가능한 게시판 아이디를 등록
App::$settingData['FixedBoardId'] = array();

// 컨트롤명이 Refresh일 시 호출
App::$extendMethod['refreshExtend'] = function(){

};

// 컨트롤러 로딩 전 호출
App::$extendMethod['BeforeLoadController'] = function(){

};

// Html 버퍼 출력 이전 호출
App::$extendMethod['AfterSetView'] = function(){

};

// tinymce 에디터 경로
App::$settingData['tinyMCEPath'] = \Paths::UrlOfSkin() . '/js/tinymce/tinymce.min.js';
*/

$keyValue = _SecretKeyByFile(_DIR . '/personal_security_key.dont.delete.it.php');
define('PERSONAL_INFO_KEY', $keyValue);

$keyValue = _SecretKeyByFile(_DIR . '/pw_reset_key.dont.delete.it.php');
define('PW_RESET_KEY', $keyValue);

// 에러 출력 여부
if(_DEVELOPERIS === true) App::$showError = true;

App::$settingData['noImg'] = Paths::UrlOfUpload() . App::$cfg->Def()->noImg->value;

// 컨트롤러 생성 바로 다음
App::$extendMethod['createControllerInstance'] = function(){

	if(App::$controllerName !== 'Install' && strpos(php_sapi_name(), 'cli') === false){

		// 접속 카운터
		$vcnt = \Common\VisitCounter::GetInstance();
		$vcnt->InsertVisitCounter();
	}

	// 회원 정보 수정 비밀번호 입력 초기화
	if(_MEMBERIS === true && App::$settingData['GetUrl'][1] != 'MyPage' && App::$settingData['GetUrl'][1] != 'Upload'){
		$_SESSION['MyInfoView'] = false;
		unset($_SESSION['MyInfoView']);
	}

};