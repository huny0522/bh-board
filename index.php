<?php

/**
 * Bang Hun.
 * 16.07.10
 */
session_start();
$_BEGIN_MICROTIME = array_sum(explode(' ', microtime()));

header('Content-Type: text/html; charset=UTF-8');
//error_reporting(E_ERROR | E_WARNING);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Seoul');
define('_BH_', true);

// BH Global
class BHG
{
	/** @var BHSession */
	public static $session;
	public static $isMember = false;
	public static $isAdmin = false;
	public static $isDeveloper = false;
	public static $language = 0;
	public static $device = 'PC';
	// public static $config;
	// public static $setting;
}

// -------------------------------------
//
//			Directory & URL Set
//
// -------------------------------------
define('PHP_RUN_CLI', strpos(php_sapi_name(), 'cli') !== false);
if(0) define('_DIR', __DIR__); // for phpstorm
$_DIR = '_DIR';
define($_DIR, str_replace('\\', '/', dirname(__FILE__)));

define('_SKINDIRNAME', 'Skin');
define('_DATADIRNAME', 'Data');
define('_UPLOAD_DIRNAME', 'Upload');
define('_ADMINURLNAME', 'BHAdm');

define('_MODELDIR', _DIR . '/Model');
define('_CONTROLLERDIR', _DIR . '/Controller');
define('_COMMONDIR', _DIR . '/Common');
define('_SKINDIR', _DIR . '/' . _SKINDIRNAME);
define('_DATADIR', _DIR . '/' . _DATADIRNAME);
define('_HTMLDIR', _DATADIR . '/_HTML');
define('_UPLOAD_DIR', _DATADIR . '/' . _UPLOAD_DIRNAME);

define('_URL', '');
define('_SKINURL', _URL . '/' . _SKINDIRNAME);
define('_DATAURL', _URL . '/' . _DATADIRNAME);
define('_HTMLURL', _DATAURL . '/_HTML');
define('_ADMINURL', _URL . '/' . _ADMINURLNAME);
define('_IMGURL', _SKINURL . '/images');
define('_UPLOAD_URL', _DATAURL . '/' . _UPLOAD_DIRNAME);

define('_DEFAULT_BOARD_LOGIN_URL', _URL . '/Login');

define('_DOMAIN', isset($_SERVER['HTTP_HOST']) ? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']) : '');

// -------------------------------------
//
//			Setting
//
// -------------------------------------
$_DEVELOPER_IP = array('127.0.0.1');
if(PHP_RUN_CLI === true){
	define('_IS_DEVELOPER_IP', true);
	BHG::$isDeveloper = true;
}
else{
	define('_IS_DEVELOPER_IP', true && in_array(!empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] :  $_SERVER['REMOTE_ADDR'], $_DEVELOPER_IP));
	BHG::$isDeveloper = _IS_DEVELOPER_IP && isset($_SESSION['developer_login']) && $_SESSION['developer_login'] === 'y';
}

ini_set('display_errors', BHG::$isDeveloper ? 1 : 0);

define('_REMOVE_SPACE', true);
define('_VIEW_MICROTIME', true);
define('_USE_DB_PASSWORD', false);
define('_USE_OLD_PASSWORD', false);
define('_SHOW_CREATE_GUIDE', true);
define('_USE_BC_TO', true);

define('_FILE_PUT_GUIDE', true);

define('_MAX_IMAGE_WIDTH', '1024');
define('_DBMAXINT', 2147483647);
define('_CATEGORY_LENGTH', 5);
define('_NO_IMG', '');

define('_MENU_CACHE_FILE', true);

// -------------------------------------
//
//			Site Common
//
// -------------------------------------
define('BHERR1_QUERY', '1');
define('BHERR1_PROCESS', '2');
define('BHERR1_UNKNOWN', '3');
define('BHERR1_VALID', '4');

define('BHERR2_INSERT', '0');
define('BHERR2_SELECT', '1');
define('BHERR2_UPDATE', '2');
define('BHERR2_DELETE', '3');
define('BHERR2_OTHER', '9');

define('_MSG_SECRET_ARTICLE', '비밀글입니다.');
define('_MSG_DELETED_ARTICLE', '삭제된 게시물입니다.');
define('_MSG_DELETED_REPLY', '삭제된 댓글입니다.');
define('_MSG_WRONG_CONNECTED', '잘못된 접근입니다.');
define('_MSG_NO_ARTICLE', '존재하지 않는 게시물입니다.');
define('_MSG_PAYMENT_WAIT', '입금대기중');
define('_MSG_PAYMENT_FIN', '입금완료');
define('_MSG_NO_AUTH', '권한이 없습니다.');
define('_MSG_NEED_LOGIN', '로그인해주시기 바랍니다.');
define('_MSG_COMPLETE_MODIFY', '수정되었습니다.');
define('_MSG_COMPLETE_REGISTER', '등록되었습니다.');
define('_MSG_COMPLETE_DELETE', '삭제되었습니다.');
define('_MSG_WRONG_PASSWORD', '비밀번호가 일치하지 않습니다.');

define('_MSG_IMPOSSIBLE_FILE', '등록 불가능한 파일입니다.');
define('_MSG_FILE_TOO_BIG', '업로드한 파일이 제한용량보다 큽니다.(' . ini_get('upload_max_filesize') . ')');
define('_MSG_UPLOAD_ERROR', '파일 등록 오류');

// -------------------------------------
//
//			Language Setting
//
// -------------------------------------

define('LANG_KOR', 0);
define('LANG_ENG', 1);
define('LANG_JPN', 2);
define('LANG_CHN', 3);

$langFile = array(
	LANG_KOR => 'kor.php',
	LANG_ENG => 'eng.php',
	LANG_JPN => 'jpn.php',
	LANG_CHN => 'chn.php',
);

// 다국어 지원일때 관리자 페이지를 한국어로 표시할때 아래 주석 제거
// $GetUrl = explode('/', isset($_GET['_bh_url']) ? $_GET['_bh_url'] : '');
// if(isset($GetUrl[1]) && $GetUrl[1] === _ADMINURLNAME) define('SELECT_LANG', LANG_KOR);

define('SELECT_LANG', LANG_KOR);
define('LANG_FILE', $langFile[SELECT_LANG]);

define('_NEED_LOGIN', 'NEED LOGIN');

// -------------------------------------
//
//			DB Table Name
//
// -------------------------------------
define('TABLE_FIRST', 'bh_');
define('TABLE_BOARD_MNG', TABLE_FIRST . 'board_manager');
define('TABLE_MEMBER', TABLE_FIRST . 'member');
define('TABLE_WITHDRAW_MEMBER', TABLE_FIRST . 'w_member');
define('TABLE_MENU', TABLE_FIRST . 'menu');
define('TABLE_CONTENT', TABLE_FIRST . 'content');
define('TABLE_CONTENT_ACTION', TABLE_FIRST . 'contents_action');
define('TABLE_BANNER', TABLE_FIRST . 'banner');
define('TABLE_POPUP', TABLE_FIRST . 'popup');
define('TABLE_IMAGES', TABLE_FIRST . 'images');
define('TABLE_FRAMEWORK_SETTING', TABLE_FIRST . 'framework_setting');
define('TABLE_VISIT', TABLE_FIRST . 'visit');
define('TABLE_VISIT_COUNTER', TABLE_FIRST . 'visit_counter');
define('TABLE_MESSAGE', TABLE_FIRST . 'message');
define('TABLE_USER_BLOCK', TABLE_FIRST . 'block');

define('_MEMBER_LEVEL', 1);
define('_MANAGER_LEVEL', 15);
define('_ADMIN_LEVEL', 18);
define('_SADMIN_LEVEL', 20);

// -------------------------------------
//
//			Application
//
// -------------------------------------
define('_DEFAULT_CONTROLLER', 'Home');
define('_DEFAULT_LAYOUT', '_Default');

require _COMMONDIR . '/common.php';

if(!PHP_RUN_CLI && !DB::SQL()->TableExists(TABLE_FRAMEWORK_SETTING) && _IS_DEVELOPER_IP !== true){
	include _DIR.'/Skin/DeveloperSetup.html';
	return;
}

if(file_exists(_COMMONDIR . '/version.php')) BH_Application::$version = trim(file_get_contents(_COMMONDIR . '/version.php'));

\BHG::$isMember = (bool)strlen((string)\BHG::$session->member->muid->Get());
\BHG::$isAdmin = (bool)strlen((string)\BHG::$session->admin->muid->Get());

BH_Application::run();

if(BHG::$isDeveloper === true && _VIEW_MICROTIME === true && _JSONIS !== true){
	$_END_MICROTIME = array_sum(explode(' ', microtime()));
	echo chr(10) . '<!-- RUNTIME : ' . sprintf('%02.6f', $_END_MICROTIME - $_BEGIN_MICROTIME) . ' -->';
}
