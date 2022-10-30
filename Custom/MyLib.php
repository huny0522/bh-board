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

// 로드밸런싱용 서브 IP
App::$settingData['loadBalancingSubIp'] = array();
// 로드밸런싱용 메인 IP
App::$settingData['loadBalancingMainIp'] = '192.168.0.2';
*/

if(_IS_DEVELOPER_IP === true && PHP_RUN_CLI !== true) require Paths::Dir('/DBUpdate.php');

// 필요 시 파일 세션이나 DB 세션은 유효 기간 별도 설정
// 세션 데이터 업데이트
\BHG::$session->SetAfterUpdate(function($data){
	$_SESSION = $data ? $data : array();
});

// 세션 데이터를 등록
\BHG::$session->Set($_SESSION, false);

// composer require tinymce/tinymce
if(file_exists(\Paths::Dir('/vendor/tinymce/tinymce/tinymce.min.js')))
	App::$settingData['tinyMCEPath'] = \Paths::Url('/vendor/tinymce/tinymce/tinymce.min.js');

// JSON 으로 공통으로 보낼 값
App::$settingData['jsonCommonData'] = null;

$keyValue = _SecretKeyByFile(_DIR . '/personal_security_key.dont.delete.it.php');
define('PERSONAL_INFO_KEY', $keyValue);

$keyValue = _SecretKeyByFile(_DIR . '/pw_reset_key.dont.delete.it.php');
define('PW_RESET_KEY', $keyValue);

// 에러 출력 여부
if(\BHG::$isDeveloper === true) App::$showError = true;

App::$settingData['noImg'] = Paths::UrlOfUpload() . App::$cfg->Def()->noImg->value;

// 컨트롤러 생성 바로 다음
App::$extendMethod['createControllerInstance'] = function(){

	if(App::$controllerName !== 'Install' && strpos(php_sapi_name(), 'cli') === false){

		// 접속 카운터
		$vcnt = \Common\VisitCounter::GetInstance();
		$vcnt->InsertVisitCounter();
	}

	// 회원 정보 수정 비밀번호 입력 초기화
	if(\BHG::$isMember === true && App::$settingData['GetUrl'][1] != 'MyPage' && App::$settingData['GetUrl'][1] != 'Upload'){
		\BHG::$session->MyInfoView->Set(false);
		unset(\BHG::$session->MyInfoView);
	}

};
