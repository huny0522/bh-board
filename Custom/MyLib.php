<?php
use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

/*
// 삭제 불가능한 게시판 아이디를 등록
App::$SettingData['FixedBoardId'] = array();

// 컨트롤명이 Refresh일 시 호출
App::$ExtendMethod['refreshExtend'] = function(){

};

// 컨트롤러 로딩 전 호출
App::$ExtendMethod['BeforeLoadController'] = function(){

};

// Html 버퍼 출력 이전 호출
App::$ExtendMethod['AfterSetView'] = function(){

};

// tinymce 에디터 경로
App::$SettingData['tinyMCEPath'] = _SKINURL . '/js/tinymce/tinymce.min.js';
*/

App::$SettingData['noImg'] = _UPLOAD_URL . CM::Config('Default', 'noImg');

// 컨트롤러 생성 바로 다음
App::$ExtendMethod['createControllerInstance'] = function(){

	// 접속 카운터
	$vcnt = \Common\VisitCounter::GetInstance();
	$vcnt->InsertVisitCounter();

	// 회원 정보 수정 비밀번호 입력 초기화
	if(_MEMBERIS === true && App::$SettingData['GetUrl'][1] != 'MyPage' && App::$SettingData['GetUrl'][1] != 'Upload'){
		$_SESSION['MyInfoView'] = false;
		unset($_SESSION['MyInfoView']);
	}

};