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

define('_BH_', true);
define('_OB_COMP', 'zlib.output_compression');
define('_POSTIS', $_SERVER['REQUEST_METHOD'] == 'POST');
define('_AJAXIS', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

$_DEVELOPER_IP = array('127.0.0.1');
define('_DEVELOPERIS', true && in_array($_SERVER['REMOTE_ADDR'], $_DEVELOPER_IP));
define('_CREATE_HTML_ALL', (false || !file_exists(_HTMLDIR) || !is_dir(_HTMLDIR)) && _DEVELOPERIS === true);
define('_REMOVE_SPACE', false);
define('_ViewMicrotime', true);
define('_USE_DB_CACHE', true);

define('_STYLEFILE', '_bhinline.css2');

define('MAX_IMAGE_COUNT', 10);
define('MAX_IMAGE_SIZE', '1024');
define('_DBMAXINT', 2147483647);
define('_CATEGORY_LENGTH', 5);

$noext = array('php','htm','html','cfg','inc','phtml', 'php5', 'asp', 'jsp');


// -------------------------------------
//
//		모바일 여부
//
// -------------------------------------
$useragent = $_SERVER['HTTP_USER_AGENT'];
if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))){
	define('_MOBILEIS', true);
}
else define('_MOBILEIS', false);


require _COMMONDIR.'/common.php';
require _COMMONDIR.'/db.info.php';
require _COMMONDIR.'/BH_DB.class.php';
require _COMMONDIR.'/BH_Application.class.php';
require _COMMONDIR.'/BH_Controller.class.php';
require _COMMONDIR.'/BH_Model.class.php';
require _COMMONDIR.'/BH_Router.class.php';
require _COMMONDIR.'/BH_Common.class.php';

if(_DEVELOPERIS === true){
	if(!file_exists(_DATADIR) || !is_dir(_DATADIR)) @mkdir(_DATADIR, 0757, true);
	require _COMMONDIR.'/HtmlConvert.php';
	require _COMMONDIR.'/StyleConvert.php';
	require _COMMONDIR.'/BH_HtmlCreate.class.php';
}

if(_CREATE_HTML_ALL === true){
	delTree(_HTMLDIR);
	ReplaceHTMLAll(_SKINDIR, _HTMLDIR);
	ReplaceCSS2ALL(_HTMLDIR, _HTMLDIR);
	ReplaceCSS2ALL(_SKINDIR, _HTMLDIR);
}

// -------------------------------------
//
//			Site Common
//
// -------------------------------------
define('_DEFAULT_CONTROLLER', 'Home');
define('_DEFAULT_LAYOUT', '_Default');
define('_MEMBERIS', isset($_SESSION['member']) && strlen($_SESSION['member']['muid']));

// 테이블명
define('TABLE_FIRST', 'bh_');
define('TABLE_BOARD_MNG', TABLE_FIRST.'board_manager');
define('TABLE_MEMBER', TABLE_FIRST.'member');
define('TABLE_WITHDRAW_MEMBER', TABLE_FIRST.'w_member');
define('TABLE_MENU', TABLE_FIRST.'menu');
define('TABLE_CONTENT', TABLE_FIRST.'content');
define('TABLE_BANNER', TABLE_FIRST.'banner');
define('TABLE_POPUP', TABLE_FIRST.'popup');
define('TABLE_IMAGES', TABLE_FIRST.'images');

BH_DB_Cache::$DBTableFirst = array(TABLE_FIRST);
BH_DB_Cache::$ExceptTable = array(TABLE_MEMBER);


define('_SECRET_ARTICLE', '비밀글입니다.');
define('_DELETED_ARTICLE', '삭제된 게시물입니다.');
define('_DELETED_REPLY', '삭제된 댓글입니다.');
define('_WRONG_CONNECTED', '잘못된 접근입니다.');
define('_NO_ARTICLE', '존재하지 않는 게시물입니다.');
define('_PAYMENT_WAIT', '입금대기중');
define('_PAYMENT_FIN', '입금완료');
define('_NO_AUTH', '권한이 없습니다.');

$_LevelArray = array(0 => '비회원', 1 => '일반회원', 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 15 => '매니저', 18 => '관리자', 20 => '최고관리자');
define('_MEMBER_LEVEL',1);
define('_MANAGER_LEVEL',15);
define('_ADMIN_LEVEL',18);
define('_SADMIN_LEVEL',20);

/**
 * @var BH_Application $_BH_App
 */
$_BH_App = new \BH_Application();
$_BH_App->run();

if(_ViewMicrotime === true && _AJAXIS !== true){
	$_END_MICROTIME = array_sum(explode(' ',microtime()));
	$_RES_MICROTIME = $_END_MICROTIME - $_BEGIN_MICROTIME;
	echo chr(10).'<!-- RUNTIME : '.$_RES_MICROTIME.' -->';
}
