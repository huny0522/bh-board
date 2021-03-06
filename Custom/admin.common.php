<?php
/**
 * Bang Hun.
 */
use \BH_Common as CM;
use \BH_Application as App;
use \DB as DB;

App::$settingData['AdminMenu'] = array(
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
	'001050' => array(
		'Category' => 'Config/Texts',
		'Name' => '사이트내용관리'
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
	),
	'006' => array(
		'Category' => 'Statistics',
		'Name' => '통계'
	),
	'006001' => array(
		'Category' => 'Statistics',
		'Name' => '접속통계'
	)
);

App::$settingData['koreaDo'] = '서울;경기;부산;대구;인천;광주;대전;울산;세종;강원;충북;충남;전북;전남;경북;경남;제주';

if(App::$settingData['GetUrl'][2] === 'Config' && App::$settingData['GetUrl'][3] === 'Content'){

	App::$data['menu'] = array(
		// 'Guide_Reg' => '회원가입 안내',
		// 'Guide_Order' => '주문 안내',
		// 'Guide_Settle' => '결제 안내',
		// 'Guide_Delivery' => '배송 안내',
		// 'Guide_Change' => '교환/반품 안내',
		// 'Guide_Return' => '환불 안내',
		'EmailCollector' => '이메일무단수집거부',
		'PrivacyText' => '개인정보취급방침',
		'TermsText' => '이용약관',
		// 'Terms_YouthProtection' => '청소년 보호방침',
		// 'JoinMessage' => '가입완료 메세지'
	);
}


function AdminTitleSet(){
	return App::$title = App::$settingData['AdminMenu'][App::$data['NowMenu']]['Name'];
}
