<?php
/**
 * Bang Hun.
 * 16.07.10
 */
session_start();
$_BEGIN_MICROTIME = array_sum(explode(' ',microtime()));

header('Content-Type: text/html; charset=UTF-8');
//error_reporting(E_ERROR | E_WARNING);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Seoul');
define('_BH_', true);
use \BH_Application as App;

// -------------------------------------
//
//			Directory & URL Set
//
// -------------------------------------
define('_DIR', str_replace('\\', '/', dirname(__FILE__)));
define('_MODELDIR', _DIR . '/Model');
define('_CONTROLLERDIR', _DIR . '/Controller');
define('_COMMONDIR', _DIR . '/Common');
define('_SKINDIR', _DIR.'/Skin');
define('_DATADIR', _DIR.'/Data');
define('_HTMLDIR', _DATADIR.'/_HTML');
define('_UPLOAD_DIRNAME', 'Upload');
define('_UPLOAD_DIR', _DATADIR.'/'._UPLOAD_DIRNAME);

define('_URL', '');
define('_DATAURL', _URL.'/Data');
define('_SKINURL', _URL.'/Skin');
define('_HTMLURL', _DATAURL.'/_HTML');
define('_ADMINURLNAME', 'BHAdm');
define('_ADMINURL', _URL.'/'._ADMINURLNAME);
define('_IMGURL', _SKINURL.'/images');
define('_UPLOAD_URL', _DATAURL.'/'._UPLOAD_DIRNAME);

// -------------------------------------
//
//			Setting
//
// -------------------------------------
$_DEVELOPER_IP = array('127.0.0.1');
define('_DEVELOPERIS', true && in_array($_SERVER['REMOTE_ADDR'], $_DEVELOPER_IP));
define('_CREATE_HTML_ALL', false && _DEVELOPERIS === true);
define('_REFRESH_HTML_ALL', true && _DEVELOPERIS === true);
define('_REFRESH_DB_CACHE_ALL', true && _DEVELOPERIS === true);
define('_REFRESH_BTN', _DEVELOPERIS === true ? '<a id="_BH_RefreshBtn" href="'._URL.'/_Refresh?r_url='.urlencode($_SERVER['REQUEST_URI']).'">새로고침</a>' : '');
define('_REMOVE_SPACE', false);
define('_VIEW_MICROTIME', true);
define('_USE_DB_CACHE', true);
define('_USE_OLD_PASSWORD', false);
define('_SHOW_CREATE_GUIDE', true);

define('_STYLEFILE', '_bhinline.css2');

define('_MAX_IMAGE_COUNT', 10);
define('_MAX_IMAGE_WIDTH', '1024');
define('_DBMAXINT', 2147483647);
define('_CATEGORY_LENGTH', 5);
define('_NO_IMG', '');

// -------------------------------------
//
//			Site Common
//
// -------------------------------------
define('_SECRET_ARTICLE', '비밀글입니다.');
define('_DELETED_ARTICLE', '삭제된 게시물입니다.');
define('_DELETED_REPLY', '삭제된 댓글입니다.');
define('_WRONG_CONNECTED', '잘못된 접근입니다.');
define('_NO_ARTICLE', '존재하지 않는 게시물입니다.');
define('_PAYMENT_WAIT', '입금대기중');
define('_PAYMENT_FIN', '입금완료');
define('_NO_AUTH', '권한이 없습니다.');

// -------------------------------------
//
//			DB Table Name
//
// -------------------------------------
define('TABLE_FIRST', 'bh_');
define('TABLE_BOARD_MNG', TABLE_FIRST.'board_manager');
define('TABLE_MEMBER', TABLE_FIRST.'member');
define('TABLE_WITHDRAW_MEMBER', TABLE_FIRST.'w_member');
define('TABLE_MENU', TABLE_FIRST.'menu');
define('TABLE_CONTENT', TABLE_FIRST.'content');
define('TABLE_BANNER', TABLE_FIRST.'banner');
define('TABLE_POPUP', TABLE_FIRST.'popup');
define('TABLE_IMAGES', TABLE_FIRST.'images');

define('_MEMBERIS', isset($_SESSION['member']) && strlen($_SESSION['member']['muid']));
define('_MEMBER_LEVEL',1);
define('_MANAGER_LEVEL',15);
define('_ADMIN_LEVEL',18);
define('_SADMIN_LEVEL',20);

// -------------------------------------
//
//			Application
//
// -------------------------------------
define('_DEFAULT_CONTROLLER', 'Home');
define('_DEFAULT_LAYOUT', '_Default');

require _COMMONDIR.'/common.php';

BH_DB_Cache::$DBTableFirst = array(TABLE_FIRST);
BH_DB_Cache::$ExceptTable = array(TABLE_MEMBER);

App::$Data['LevelArray'] = array(0 => '비회원', 1 => '일반회원', 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 15 => '매니저', 18 => '관리자', 20 => '최고관리자');
App::$Data['noext'] = array('php','htm','html','cfg','inc','phtml', 'php5', 'asp', 'jsp');

require _COMMONDIR.'/MyLib.php';

App::run();

if(_VIEW_MICROTIME === true && _AJAXIS !== true){
	$_END_MICROTIME = array_sum(explode(' ',microtime()));
	echo chr(10).'<!-- RUNTIME : '.sprintf('%02.6f', $_END_MICROTIME - $_BEGIN_MICROTIME).' -->';
}
