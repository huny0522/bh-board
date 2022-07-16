<?php

namespace Common;

use \DB as DB;

class VisitCounter
{
	public $browser = '';
	public $os = '';
	public $device = '';
	public $http_referer = '';
	public static $instance = null;
	public $session = null;

	public $viewOption;
	private $today;

	public function __construct(){
		if(!strlen((string)\BHG::$session->visit->Get()) && isset($_COOKIE['visit']) && strlen($_COOKIE['visit'])){
			\BHG::$session->visit->Set($_COOKIE['visit']);
		}
		else if(strlen((string)\BHG::$session->visit->Get()) && \BHG::$session->visit->Get() != $_COOKIE['visit']){
			setcookie('visit', \BHG::$session->visit->Get());
		}

		$y = date('Y');
		$m = date('m');
		$d = date('d');
		$h = date('H');
		$w = date('w');

		$this->today = array(
			'y' => $y,
			'm' => $m,
			'd' => $d,
			'h' => $h,
			'w' => $w,
			'ym' => $y.'-'.$m,
			'ymd' => $y.'-'.$m.'-'.$d
		);

		$this->viewOption = array(
			'beginY' => $y,
			'beginM' => $m,
			'beginD' => $d,
			'endY' => $y,
			'endM' => $m,
			'endD' => $d,
			'viewType' => 'day'
		);
	}

	public static function GetInstance(){
		if(is_null(self::$instance)) self::$instance = new static();
		return self::$instance;
	}

	/**
	 * 접속 등록
	 * 등록이 되면 true 반환
	 *
	 * @return bool
	 */
	public function InsertVisitCounter(){
		if($this->_SessionEmptyCheck()){
			$this->SetInfo();
			$this->_InsertVisit();
			$this->_InsertCounters('visit');
			$this->_CreateSession();
			return true;
		}
		return false;
	}

	public function GetTotal(){
		$res = DB::GetQryObj(TABLE_VISIT_COUNTER)
			->AddWhere('`d_y` = -1')
			->AddWhere('`d_m` = -1')
			->AddWhere('`d_d` = -1')
			->AddWhere('`d_h` = -1')
			->AddWhere('`d_w` = -1')
			->AddWhere('`type` = \'total\'' )
			->Get();
		if(!$res) $res = array('visit' => 0, 'login' => 0);
		return $res;
	}

	public function GetToday(){
		$res = DB::GetQryObj(TABLE_VISIT_COUNTER)
			->AddWhere('`d_y` = %d', $this->today['y'])
			->AddWhere('`d_m` = %d', $this->today['m'])
			->AddWhere('`d_d` = %d', $this->today['d'])
			->AddWhere('`d_h` = -1')
			->AddWhere('`d_w` = -1')
			->AddWhere('`type` = \'total\'' )
			->SetKey('`d_y`,`d_m`,`d_d`, `visit`, `login`')
			->Get();
		if(!$res) $res = array('visit' => 0, 'login' => 0);
		return $res;
	}

	public function GetDayMax(){
		$res = DB::GetQryObj(TABLE_VISIT_COUNTER)
			->AddWhere('`d_y` = %d', $this->today['y'])
			->AddWhere('`d_m` = %d', $this->today['m'])
			->AddWhere('`d_d` = %d', $this->today['d'])
			->AddWhere('`d_h` = -1')
			->AddWhere('`d_w` = -1')
			->AddWhere('`type` = \'total\'' )
			->SetKey('MAX(`visit`) as `visit`,MAX(`login`) as `login`')
			->Get();
		if(!$res) $res = array('visit' => 0, 'login' => 0);
		return $res;
	}

	/**
	 * 로그인 등록
	 */
	public function InsertLoginCounter(){
		$this->SetInfo();
		$this->_InsertCounters('login');
	}

	/**
	 * @param string $beginDate
	 * @param string $endDate
	 * @param string $type
	 * @param string $viewType day, week, hour
	 */
	public function GetStatistics($beginDate, $endDate, $type = 'total', $viewType = 'day'){
		$b = preg_replace('/[^0-9]/', '', $beginDate);
		$e = preg_replace('/[^0-9]/', '', $endDate);
		$beginY = substr($b, 0, 4);
		$beginM = substr($b, 4, 2);
		$beginD = substr($b, 6, 2);
		$endY = substr($e, 0, 4);
		$endM = substr($e, 4, 2);
		$endD = substr($e, 6, 2);
		if($beginY  < $endY){
			$this->_GetStatisticsYear($beginY, $endY - 1, $type, $viewType);
		}
	}

	public function GetStatisticsYear(){
		return $this->_GetStatisticsYear($this->viewOption['beginY'], $this->viewOption['endY']);
	}

	public function GetStatisticsMonth(){
		return $this->_GetStatisticsMonth($this->viewOption['beginY'], $this->viewOption['beginM'], $this->viewOption['endY'], $this->viewOption['endM']);
	}

	public function GetStatisticsDayQuery(){
		return $this->_GetStatisticsDay($this->viewOption['beginY'], $this->viewOption['beginM'], $this->viewOption['beginD'], $this->viewOption['endY'], $this->viewOption['endM'], $this->viewOption['endD'], true);
	}

	public function GetStatisticsWeek(){
		return $this->_GetStatisticsByType($this->viewOption['beginY'], $this->viewOption['beginM'], $this->viewOption['beginD'], $this->viewOption['endY'], $this->viewOption['endM'], $this->viewOption['endD'], 'week');
	}

	public function GetStatisticsHour(){
		return $this->_GetStatisticsByType($this->viewOption['beginY'], $this->viewOption['beginM'], $this->viewOption['beginD'], $this->viewOption['endY'], $this->viewOption['endM'], $this->viewOption['endD'], 'hour');
	}

	public function GetStatisticsBrowser(){
		return $this->_GetStatisticsByType($this->viewOption['beginY'], $this->viewOption['beginM'], $this->viewOption['beginD'], $this->viewOption['endY'], $this->viewOption['endM'], $this->viewOption['endD'], 'browser');
	}

	public function GetStatisticsDevice(){
		return $this->_GetStatisticsByType($this->viewOption['beginY'], $this->viewOption['beginM'], $this->viewOption['beginD'], $this->viewOption['endY'], $this->viewOption['endM'], $this->viewOption['endD'], 'device');
	}

	public function GetStatisticsOS(){
		return $this->_GetStatisticsByType($this->viewOption['beginY'], $this->viewOption['beginM'], $this->viewOption['beginD'], $this->viewOption['endY'], $this->viewOption['endM'], $this->viewOption['endD'], 'os');
	}

	public function GetStatisticsURI(){
		return $this->_GetStatisticsByType($this->viewOption['beginY'], $this->viewOption['beginM'], $this->viewOption['beginD'], $this->viewOption['endY'], $this->viewOption['endM'], $this->viewOption['endD'], 'uri');
	}

	public function GetOS(){
		if ( isset( $_SERVER ) ) {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}
		else {
			global $HTTP_SERVER_VARS;
			if ( isset( $HTTP_SERVER_VARS ) ) {
				$agent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
			}
			else {
				global $HTTP_USER_AGENT;
				$agent = $HTTP_USER_AGENT;
			}
		}
		$ros[] = array('Windows XP', 'Windows XP');
		$ros[] = array('(Windows NT 5.1|Windows NT5.1)', 'Windows XP');
		$ros[] = array('Windows 2000', 'Windows 2000');
		$ros[] = array('Windows NT 5.0', 'Windows 2000');
		$ros[] = array('Windows NT 4.0|WinNT4.0', 'Windows NT');
		$ros[] = array('Windows NT 5.2', 'Windows Server 2003');
		$ros[] = array('Windows NT 6.0', 'Windows Vista');
		$ros[] = array('Windows NT 7.0', 'Windows 7');
		$ros[] = array('Windows CE', 'Windows CE');
		$ros[] = array('(media center pc).([0-9]{1,2}\.[0-9]{1,2})', 'Windows Media Center');
		$ros[] = array('(win)([0-9]{1,2}\.[0-9x]{1,2})', 'Windows');
		$ros[] = array('(win)([0-9]{2})', 'Windows');
		$ros[] = array('(windows)([0-9x]{2})', 'Windows');
		$ros[] = array('Windows ME', 'Windows ME');
		$ros[] = array('Win 9x 4.90', 'Windows ME');
		$ros[] = array('Windows 98|Win98', 'Windows 98');
		$ros[] = array('Windows 95', 'Windows 95');
		$ros[] = array('(windows)([0-9]{1,2}\.[0-9]{1,2})', 'Windows');
		$ros[] = array('win32', 'Windows');
		$ros[] = array('(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})', 'Java');
		$ros[] = array('(Solaris)([0-9]{1,2}\.[0-9x]{1,2}){0,1}', 'Solaris');
		$ros[] = array('dos x86', 'DOS');
		$ros[] = array('unix', 'Unix');
		$ros[] = array('Mac OS X', 'Mac OS X');
		$ros[] = array('Mac_PowerPC', 'Macintosh PowerPC');
		$ros[] = array('(mac|Macintosh)', 'Mac OS');
		$ros[] = array('(sunos)([0-9]{1,2}\.[0-9]{1,2}){0,1}', 'SunOS');
		$ros[] = array('(beos)([0-9]{1,2}\.[0-9]{1,2}){0,1}', 'BeOS');
		$ros[] = array('(risc os)([0-9]{1,2}\.[0-9]{1,2})', 'RISC OS');
		$ros[] = array('os\/2', 'OS\/2');
		$ros[] = array('freebsd', 'FreeBSD');
		$ros[] = array('openbsd', 'OpenBSD');
		$ros[] = array('netbsd', 'NetBSD');
		$ros[] = array('irix', 'IRIX');
		$ros[] = array('plan9', 'Plan9');
		$ros[] = array('osf', 'OSF');
		$ros[] = array('aix', 'AIX');
		$ros[] = array('GNU Hurd', 'GNU Hurd');
		$ros[] = array('(fedora)', 'Linux - Fedora');
		$ros[] = array('(kubuntu)', 'Linux - Kubuntu');
		$ros[] = array('(ubuntu)', 'Linux - Ubuntu');
		$ros[] = array('(debian)', 'Linux - Debian');
		$ros[] = array('(CentOS)', 'Linux - CentOS');
		$ros[] = array('(Mandriva).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)', 'Linux - Mandriva');
		$ros[] = array('(SUSE).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)', 'Linux - SUSE');
		$ros[] = array('(Dropline)', 'Linux - Slackware (Dropline GNOME)');
		$ros[] = array('(ASPLinux)', 'Linux - ASPLinux');
		$ros[] = array('(Red Hat)', 'Linux - Red Hat');
		$ros[] = array('(linux)', 'Linux');
		$ros[] = array('(amigaos)([0-9]{1,2}\.[0-9]{1,2})', 'AmigaOS');
		$ros[] = array('amiga-aweb', 'AmigaOS');
		$ros[] = array('amiga', 'Amiga');
		$ros[] = array('AvantGo', 'PalmOS');
		$ros[] = array('([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})', 'Linux');
		$ros[] = array('(webtv)\/([0-9]{1,2}\.[0-9]{1,2})', 'WebTV');
		$ros[] = array('Dreamcast', 'Dreamcast OS');
		$ros[] = array('GetRight', 'Windows');
		$ros[] = array('go!zilla', 'Windows');
		$ros[] = array('gozilla', 'Windows');
		$ros[] = array('gulliver', 'Windows');
		$ros[] = array('ia archiver', 'Windows');
		$ros[] = array('NetPositive', 'Windows');
		$ros[] = array('mass downloader', 'Windows');
		$ros[] = array('microsoft', 'Windows');
		$ros[] = array('offline explorer', 'Windows');
		$ros[] = array('teleport', 'Windows');
		$ros[] = array('web downloader', 'Windows');
		$ros[] = array('webcapture', 'Windows');
		$ros[] = array('webcollage', 'Windows');
		$ros[] = array('webcopier', 'Windows');
		$ros[] = array('webstripper', 'Windows');
		$ros[] = array('webzip', 'Windows');
		$ros[] = array('wget', 'Windows');
		$ros[] = array('Java', 'Unknown');
		$ros[] = array('flashget', 'Windows');
		$ros[] = array('MS FrontPage', 'Windows');
		$ros[] = array('(msproxy)\/([0-9]{1,2}.[0-9]{1,2})', 'Windows');
		$ros[] = array('(msie)([0-9]{1,2}.[0-9]{1,2})', 'Windows');
		$ros[] = array('libwww-perl', 'Unix');
		$ros[] = array('UP.Browser', 'Windows CE');
		$ros[] = array('NetAnts', 'Windows');
		$file = count ( $ros );
		$os = '';
		for ( $n=0 ; $n<$file ; $n++ ){
			if ( preg_match('/'.$ros[$n][0].'/i' , $agent, $name)){
				$os = @$ros[$n][1].' '.@$name[2];
				break;
			}
		}
		return trim ( $os );
	}

	public function GetBrowser() {

		if ( isset( $_SERVER ) ) {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}
		else {
			global $HTTP_SERVER_VARS;
			if ( isset( $HTTP_SERVER_VARS ) ) {
				$agent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
			}
			else {
				global $HTTP_USER_AGENT;
				$agent = $HTTP_USER_AGENT;
			}
		}

		$browser        = "Unknown Browser";

		$browser_array = array(
			'/msie/i'      => 'Internet Explorer',
			'/firefox/i'   => 'Firefox',
			'/safari/i'    => 'Safari',
			'/chrome/i'    => 'Chrome',
			'/edge/i'      => 'Edge',
			'/opera/i'     => 'Opera',
			'/netscape/i'  => 'Netscape',
			'/maxthon/i'   => 'Maxthon',
			'/konqueror/i' => 'Konqueror',
			'/mobile/i'    => 'Handheld Browser'
		);

		foreach ($browser_array as $regex => $value)
			if (preg_match($regex, $agent))
				$browser = $value;

		return $browser;
	}

	public function GetDevice(){
		if ( isset( $_SERVER ) ) {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}
		else {
			global $HTTP_SERVER_VARS;
			if ( isset( $HTTP_SERVER_VARS ) ) {
				$agent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
			}
			else {
				global $HTTP_USER_AGENT;
				$agent = $HTTP_USER_AGENT;
			}
		}


		if(strpos($agent,"iPod")) return 'iPod';
		else if(strpos($agent,"iPhone")) return 'iPhone';
		else if(strpos($agent,"iPad")) return 'iPad';
		else if(strpos($agent,"Android")) return 'Android';
		else if(strpos($agent,"webOS")) return 'WebOS';
		else return '';
	}

	// 년도별
	public function _GetStatisticsYear($beginY, $endY){
		if($endY >= $this->today['y']) $endY = $this->today['y'];

		if($beginY > $endY) $beginY = $endY;

		$YearData = array();

		$qry = DB::GetListQryObj(TABLE_VISIT_COUNTER)
			->AddWhere('`d_y` BETWEEN %d AND %d', $beginY, $endY)
			->AddWhere('`d_m` = -1')
			->AddWhere('`d_d` = -1')
			->AddWhere('`d_h` = -1')
			->AddWhere('`d_w` = -1')
			->AddWhere('`type` = \'total\'');
		while($row = $qry->Get()){
			$YearData[$row['d_y']] = array('visit' => $row['visit'], 'login' => $row['login']);
		}
		for($y = $beginY; $y <= $endY; $y++){
			if(!isset($YearData[$y])){
				$monthData = $this->_GetStatisticsMonth($y, 1, $y, 12);
				if(sizeof($monthData)){
					$YearData[$y]['visit'] = 0;
					$YearData[$y]['login'] = 0;
				}
				foreach($monthData as $v){
					$YearData[$y]['visit'] = $v['visit'];
					$YearData[$y]['login'] = $v['login'];
				}
				if($y < $this->today['y'] && isset($YearData[$y]['visit'])){
					DB::InsertQryObj(TABLE_VISIT_COUNTER)
						->SetDataNum('d_y', $y)
						->SetDataNum('d_m', -1)
						->SetDataNum('d_d', -1)
						->SetDataNum('d_h', -1)
						->SetDataNum('d_w', -1)
						->SetDataStr('type', 'total')
						->SetDataNum('visit', $YearData[$y]['visit'])
						->SetDataNum('login', $YearData[$y]['login'])
						->Run();
				}
			}
		}


		return $YearData;
	}

	// 월별
	public function _GetStatisticsMonth($beginY, $beginM, $endY, $endM){
		$endMonth = $endY.'-'.sprintf('%02d', $endM);
		if($endMonth >= $this->today['ym']){
			$endY = $this->today['y'];
			$endM = $this->today['m'];
		}

		if($beginY.sprintf('%02d', $beginM) > $endY.sprintf('%02d', $endM)){
			$beginY = $endY;
			$beginM = $endM;
		}

		$MonthData = array();

		$qry = DB::GetListQryObj(TABLE_VISIT_COUNTER)
			->AddWhere('`d_y` > %d OR (`d_y` = %d AND `d_m` >= %d)', $beginY, $beginY, $beginM)
			->AddWhere('`d_y` < %d OR (`d_y` = %d AND `d_m` <= %d)', $endY, $endY, $endM)
			->AddWhere('`d_m` > 0')
			->AddWhere('`d_d` = -1')
			->AddWhere('`d_h` = -1')
			->AddWhere('`d_w` = -1')
			->AddWhere('`type` = \'total\'');
		while($row = $qry->Get()){
			$MonthData[$row['d_y'].'-'.sprintf('%02d', $row['d_m'])] = array('visit' => $row['visit'], 'login' => $row['login']);
		}
		for($y = $beginY; $y <= $endY; $y++){
			$start_m = ($y == $beginY ? $beginM : 1);
			$end_m = ($y == $endY ? $endM : 12);
			for($m = $start_m; $m <= $end_m; $m++){
				$k = $y . '-' . sprintf('%02d', $m);
				if(!isset($MonthData[$k])){
					$last = GetLastDay($y, $m);
					$dayData = $this->_GetStatisticsDay($y, $m, 1, $y, $m, $last);
					if(sizeof($dayData)){
						$MonthData[$k]['visit'] = 0;
						$MonthData[$k]['login'] = 0;
					}
					foreach($dayData as $v){
						$MonthData[$k]['visit'] += $v['visit'];
						$MonthData[$k]['login'] += $v['login'];
					}
					if($k < $this->today['ym'] && isset($MonthData[$k]['visit'])){
						DB::InsertQryObj(TABLE_VISIT_COUNTER)
							->SetDataNum('d_y', $y)
							->SetDataNum('d_m', $m)
							->SetDataNum('d_d', -1)
							->SetDataNum('d_h', -1)
							->SetDataNum('d_w', -1)
							->SetDataStr('type', 'total')
							->SetDataNum('visit', $MonthData[$k]['visit'])
							->SetDataNum('login', $MonthData[$k]['login'])
							->Run();
					}
				}
			}
		}


		return $MonthData;
	}

	// 일별
	public function _GetStatisticsDay($beginY, $beginM, $beginD, $endY, $endM, $endD, $getPagingQry = false){
		$endDay = $endY.'-'.sprintf('%02d', $endM).'-'.sprintf('%02d', $endD);
		if($endDay >= $this->today['ymd']){
			$endY = $this->today['y'];
			$endM = $this->today['m'];
			$endD = $this->today['d'];
		}

		if($beginY.sprintf('%02d', $beginM).sprintf('%02d', $beginD) > $endY.sprintf('%02d', $endM).sprintf('%02d', $endD)){
			$beginY = $endY;
			$beginM = $endM;
			$beginD = $endD;
		}

		$dayData = array();

		$qry = $getPagingQry ? DB::GetListPageQryObj(TABLE_VISIT_COUNTER) : DB::GetListQryObj(TABLE_VISIT_COUNTER);
		$qry->AddWhere('`d_y` > %d OR (`d_y` = %d AND `d_m` > %d) OR (`d_y` = %d AND `d_m` = %d AND `d_d` >= %d)', $beginY, $beginY, $beginM, $beginY, $beginM, $beginD)
			->AddWhere('`d_y` < %d OR (`d_y` = %d AND `d_m` < %d) OR (`d_y` = %d AND `d_m` = %d AND `d_d` <= %d)', $endY, $endY, $endM, $endY, $endM, $endD)
			->AddWhere('`d_m` > 0')
			->AddWhere('`d_d` > 0')
			->AddWhere('`d_h` = -1')
			->AddWhere('`d_w` = -1')
			->AddWhere('`type` = \'total\'')
			->SetSort('`d_y` DESC, `d_m` DESC, `d_d` DESC')
			->SetKey('`d_y`,`d_m`,`d_d`,`visit`, `login`');

		if($getPagingQry) return $qry;

		while($row = $qry->Get()){
			$dayData[$row['d_y'].'-'.sprintf('%02d', $row['d_m']).'-'.sprintf('%02d', $row['d_d'])] = array('visit' => $row['visit'], 'login' => $row['login']);
		}
		return $dayData;
	}

	private function _SetTypeListQuery($qry, $type, $groupIs = false){
		switch($type){
			case 'week':
				$qry->AddWhere('`type` = \'total\'')->AddWhere('`d_w` > -1')->AddKey('`d_w` as `keyword`');
				if($groupIs) $qry->SetGroup('`d_w`');
				else $qry->AddWhere('`d_h` = -1');
			break;
			case 'hour':
				$qry->AddWhere('`type` = \'total\'')->AddWhere('`d_h` > -1')->AddKey('`d_h` as `keyword`');
				if($groupIs) $qry->SetGroup('`d_h`');
				else $qry->AddWhere('`d_w` = -1');
			break;
			case 'browser':
				$qry->AddWhere('`type` = \'browser\'')->AddKey('`type_detail` as `keyword`');
				if($groupIs) $qry->SetGroup('`type_detail`');
			break;
			case 'device':
				$qry->AddWhere('`type` = \'device\'')->AddKey('`type_detail` as `keyword`');
				if($groupIs) $qry->SetGroup('`type_detail`');
			break;
			case 'uri':
				$qry->AddWhere('`type` = \'uri\'')->AddKey('`type_detail` as `keyword`');
				if($groupIs) $qry->SetGroup('`type_detail`');
			break;
			case 'os':
				$qry->AddWhere('`type` = \'os\'')->AddKey('`type_detail` as `keyword`');
				if($groupIs) $qry->SetGroup('`type_detail`');
			break;
		}
	}

	private function _SetTypeInsertQuery($qry, $type, $keyword){
		switch($type){
			case 'week':
				$qry->SetDataNum('d_w', $keyword);
				$qry->SetDataNum('d_h', -1);
				$qry->SetDataStr('type', 'total');
			break;
			case 'hour':
				$qry->SetDataNum('d_h', $keyword);
				$qry->SetDataNum('d_w', -1);
				$qry->SetDataStr('type', 'total');
			break;
			case 'browser':
				$qry->SetDataStr('type', 'browser');
				$qry->SetDataStr('type_detail', $keyword);
			break;
			case 'device':
				$qry->SetDataStr('type', 'device');
				$qry->SetDataStr('type_detail', $keyword);
			break;
			case 'uri':
				$qry->SetDataStr('type', 'uri');
				$qry->SetDataStr('type_detail', $keyword);
			break;
			case 'os':
				$qry->SetDataStr('type', 'os');
				$qry->SetDataStr('type_detail', $keyword);
			break;
		}
	}

	// 종류별 통계 가져오기
	public function _GetStatisticsByType($beginY, $beginM, $beginD, $endY, $endM, $endD, $type = 'hour'){
		$res = array();

		$endDay = $endY.'-'.sprintf('%02d', $endM).'-'.sprintf('%02d', $endD);
		if($endDay >= $this->today['ymd']){
			$endY = $this->today['y'];
			$endM = $this->today['m'];
			$endD = $this->today['d'];
		}

		if($beginY.sprintf('%02d', $beginM).sprintf('%02d', $beginD) > $endY.sprintf('%02d', $endM).sprintf('%02d', $endD)){
			$beginY = $endY;
			$beginM = $endM;
			$beginD = $endD;
		}

		// 처음월, 마지막월 데이타
		$beginLastDay = GetLastDay($beginY, $beginM);

		$qry = $type === 'uri' ? DB::GetListPageQryObj(TABLE_VISIT_COUNTER) : DB::GetListQryObj(TABLE_VISIT_COUNTER);
		$qry->AddWhere('(`d_y` = %d AND `d_m` = %d AND `d_d` BETWEEN %d AND %d) OR (`d_y` = %d AND `d_m` = %d AND `d_d` BETWEEN 1 AND %d)', $beginY, $beginM, $beginD, $beginLastDay, $endY, $endM, $endD)
			->SetKey('SUM(`login`) as `login`, SUM(`visit`) as `visit`');
		//$qry->SetTest(true);

		if($type === 'uri'){
			$qry->SetPageUrl(\BH_Application::URLAction(\BH_Application::$action).'/'.\BH_Application::$id.\BH_Application::GetFollowQuery('page'))
				->SetPage(Get('page'))
				->SetArticleCount(20);
		}

		$this->_SetTypeListQuery($qry, $type, true);

		while($row = $qry->Get()){
			$res[$row['keyword']] = $row;
		}

		if($type === 'uri') \BH_Application::$settingData['statistics_page'] = $qry->GetPageHtml();



		// 선택 첫 이전월 데이타
		if($beginM + 1 <= 12){
			$data = $this->_GetStatisticsMonthByType($beginY, $beginM + 1, $beginY, 12, $type);
			foreach($data as $v){
				if(!isset($res[$v['keyword']])){
					$res[$v['keyword']] = array('keyword' => $v['keyword'], 'login' => 0, 'visit' => 0);
				}
				$res[$v['keyword']]['login'] += $v['login'];
				$res[$v['keyword']]['visit'] += $v['visit'];
			}
		}

		// 이전년도 데이타
		if($beginY + 1 < $endY){
			$data = $this->_GetStatisticsYearByType($beginY, $endY, $type);
			foreach($data as $v){
				if(!isset($res[$v['keyword']])){
					$res[$v['keyword']] = array('keyword' => $v['keyword'], 'login' => 0, 'visit' => 0);
				}
				$res[$v['keyword']]['login'] += $v['login'];
				$res[$v['keyword']]['visit'] += $v['visit'];
			}
		}

		// 선택 마지막해 이전월 데이타
		if($endM - 1 >= 1){
			$data = $this->_GetStatisticsMonthByType($endY, 1, $endY, $endM - 1, $type);
			foreach($data as $v){
				if(!isset($res[$v['keyword']])){
					$res[$v['keyword']] = array('keyword' => $v['keyword'], 'login' => 0, 'visit' => 0);
				}
				$res[$v['keyword']]['login'] += $v['login'];
				$res[$v['keyword']]['visit'] += $v['visit'];
			}
		}

		return $res;
	}

	// 년도별로 데이터 가져오기
	private function _GetStatisticsYearByType($beginY, $endY, $type = 'hour'){
		$qry = DB::GetListQryObj(TABLE_VISIT_COUNTER)
			->AddWhere('`d_y` BETWEEN %d AND %d', $beginY, $endY)
			->AddWhere('`d_m` = -1')
			->AddWhere('`d_d` = -1')
			->SetKey('`d_y`,`login`, `visit`');

		$this->_SetTypeListQuery($qry, $type);

		$data = array();
		while($row = $qry->Get()){
			$data[$row['d_y']][$row['keyword']] = array('login' => $row['login'], 'visit' => $row['visit']);
		}

		for($y = $beginY; $y <= $endY; $y++){
			// 년도 데이타가 없으면 캐싱이 안된것이므로 캐싱해리기
			if(!isset($data[$y])){
				$monthData = $this->_GetStatisticsMonthByType($y, 1, $y, 12, $type);
				if(sizeof($monthData)){
					foreach($monthData as $v){
						if(!isset($data[$y][$v['keyword']])){
							$data[$y][$v['keyword']]['login'] = 0;
							$data[$y][$v['keyword']]['visit'] = 0;
						}
						$data[$y][$v['keyword']]['login'] += $v['login'];
						$data[$y][$v['keyword']]['visit'] += $v['visit'];
					}

					$insQry = DB::InsertQryObj(TABLE_VISIT_COUNTER);
					foreach($data[$y] as $k => $v){
						$insQry->SetDataNum('d_y', $y)
							->SetDataNum('d_m', -1)
							->SetDataNum('d_d', -1)
							->SetDataNum('visit', $v['visit'])
							->SetDataNum('login', $v['login']);

						$this->_SetTypeInsertQuery($insQry, $type, $k);

						$insQry->MultiAdd();
					}

					$insQry->MultiRun();
				}
			}
		}
		$res = array();
		foreach($data as $yData){
			foreach($yData as $keyword => $data){
				if(!isset($res[$keyword])) $res[$keyword] = array('keyword' => $keyword, 'login' => 0, 'visit' => 0);

				$res[$keyword]['login'] += $data['login'];
				$res[$keyword]['visit'] += $data['visit'];
			}
		}

		return $res;
	}

	// 월별로 데이터 가져오기
	private function _GetStatisticsMonthByType($beginY, $beginM, $endY, $endM, $type = 'hour'){
		$qry = DB::GetListQryObj(TABLE_VISIT_COUNTER)
			->AddWhere('`d_y` > %d OR (`d_y` = %d AND `d_m` >= %d)', $beginY, $beginY, $beginM)
			->AddWhere('`d_y` < %d OR (`d_y` = %d AND `d_m` <= %d)', $endY, $endY, $endM)
			->AddWhere('`d_m` > 0')
			->AddWhere('`d_d` = -1')
			->SetKey('`d_y`,`d_m`,`login`, `visit`');

		$this->_SetTypeListQuery($qry, $type);

		$data = array();
		while($row = $qry->Get()){
			$data[$row['d_y']][$row['d_m']][$row['keyword']] = array('login' => $row['login'], 'visit' => $row['visit']);
		}

		for($y = $beginY; $y <= $endY; $y++){
			$start_m = ($y == $beginY ? $beginM : 1);
			$end_m = ($y == $endY ? $endM : 12);
			for($m = $start_m; $m <= $end_m; $m++){
				if(!isset($data[$y][$m])){
					$lastDay = GetLastDay($y, $m);
					$monthData = $this->_GetStatisticsDayByType($y, $m, 1, $y, $m, $lastDay, $type);
					if(sizeof($monthData)){
						foreach($monthData as $v){
							if(!isset($data[$v['keyword']])){
								$data[$y][$m][$v['keyword']]['login'] = 0;
								$data[$y][$m][$v['keyword']]['visit'] = 0;
							}
							$data[$y][$m][$v['keyword']]['login'] += $v['login'];
							$data[$y][$m][$v['keyword']]['visit'] += $v['visit'];
						}

						$insQry = DB::InsertQryObj(TABLE_VISIT_COUNTER);
						foreach($data[$y][$m] as $k => $v){
							$insQry->SetDataNum('d_y', $y)
								->SetDataNum('d_m', $m)
								->SetDataNum('d_d', -1)
								->SetDataNum('visit', $v['visit'])
								->SetDataNum('login', $v['login']);

							$this->_SetTypeInsertQuery($insQry, $type, $k);

							$insQry->MultiAdd();
						}

						$insQry->MultiRun();
					}
				}
			}
		}
		$res = array();
		foreach($data as $yData){
			foreach($yData as $mData){
				foreach($mData as $keyword => $data){
					if(!isset($res[$keyword])) $res[$keyword] = array('keyword' => $keyword, 'login' => 0, 'visit' => 0);

					$res[$keyword]['login'] += $data['login'];
					$res[$keyword]['visit'] += $data['visit'];
				}
			}
		}
		return $res;
	}

	// 일별로 데이터 가져오기
	private function _GetStatisticsDayByType($beginY, $beginM, $beginD, $endY, $endM, $endD, $type = 'hour'){
		$data = array();
		$qry = DB::GetListQryObj(TABLE_VISIT_COUNTER)
			->AddWhere('`d_y` > %d OR (`d_y` = %d AND `d_m` > %d) OR (`d_y` = %d AND `d_m` = %d AND `d_d` >= %d)', $beginY, $beginY, $beginM, $beginY, $beginM, $beginD)
			->AddWhere('`d_y` < %d OR (`d_y` = %d AND `d_m` < %d) OR (`d_y` = %d AND `d_m` = %d AND `d_d` <= %d)', $endY, $endY, $endM, $endY, $endM, $endD)
			->SetKey('SUM(`login`) as `login`, SUM(`visit`) as `visit`');

		$this->_SetTypeListQuery($qry, $type, true);

		while($row = $qry->Get()){
			$data[$row['keyword']] = $row;
		}
		return $data;
	}

	private function _SessionEmptyCheck(){
		if(strlen((string)\BHG::$session->visit->Get())) return false;
		else return true;
	}

	private function _CreateSession(){
		$t = \_ModelFunc::RandomFileName();
		\BHG::$session->visit->Set($t);
		setcookie('visit', $t);
	}

	private function SetInfo(){
		$this->device = $this->GetDevice();
		$this->os = $this->GetOS();
		$this->browser = $this->GetBrowser();
		$this->http_referer = (isset($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER']) && substr($_SERVER['HTTP_REFERER'], 0, strlen(_DOMAIN) !== _DOMAIN)) ? $_SERVER['HTTP_REFERER'] : 'Direct';
	}

	private function _InsertCounters($countField){
		$this->_InsertCounter($countField);
		$this->_InsertCounter($countField, 'browser', $this->browser);
		$this->_InsertCounter($countField, 'os', $this->os);
		$this->_InsertCounter($countField, 'uri', $this->http_referer);
		$this->_InsertCounter($countField, 'device', $this->device);

		$dt = DB::GetQryObj(TABLE_VISIT_COUNTER)
			->AddWhere('`d_y` = -1')
			->AddWhere('`d_m` = -1')
			->AddWhere('`d_d` = -1')
			->AddWhere('`d_h` = -1')
			->AddWhere('`d_w` = -1')
			->AddWhere('`type` = \'total\'' )
			->SetKey('type')
			->Get();

		if($dt){
			DB::UpdateQryObj(TABLE_VISIT_COUNTER)
				->AddWhere('`d_y` = -1')
				->AddWhere('`d_m` = -1')
				->AddWhere('`d_d` = -1')
				->AddWhere('`d_h` = -1')
				->AddWhere('`d_w` = -1')
				->AddWhere('`type` = \'total\'')
				->SetData($countField , '`' . $countField  .'` + 1')
				->Run();
		}
		else{
			DB::InsertQryObj(TABLE_VISIT_COUNTER)
				->SetDataStr('d_y', -1)
				->SetDataStr('d_m', -1)
				->SetDataStr('d_d', -1)
				->SetDataStr('d_h', -1)
				->SetDataStr('d_w', -1)
				->SetDataStr('type', 'total')
				->SetDataNum($countField , 1)
				->Run();
		}

		$dd = DB::GetQryObj(TABLE_VISIT_COUNTER)
			->AddWhere('`d_y` = %d', $this->today['y'])
			->AddWhere('`d_m` = %d', $this->today['m'])
			->AddWhere('`d_d` = %d', $this->today['d'])
			->AddWhere('`d_h` = -1')
			->AddWhere('`d_w` = -1')
			->AddWhere('`type` = \'total\'' )
			->SetKey('type')
			->Get();

		if($dd){
			DB::UpdateQryObj(TABLE_VISIT_COUNTER)
				->AddWhere('`d_y` = %d', $this->today['y'])
				->AddWhere('`d_m` = %d', $this->today['m'])
				->AddWhere('`d_d` = %d', $this->today['d'])
				->AddWhere('`d_h` = -1')
				->AddWhere('`d_w` = -1')
				->AddWhere('`type` = \'total\'')
				->SetData($countField , '`' . $countField  .'` + 1')
				->Run();
		}
		else{
			DB::InsertQryObj(TABLE_VISIT_COUNTER)
				->SetDataStr('d_y', $this->today['y'])
				->SetDataStr('d_m', $this->today['m'])
				->SetDataStr('d_d', $this->today['d'])
				->SetDataStr('d_h', -1)
				->SetDataStr('d_w', -1)
				->SetDataStr('type', 'total')
				->SetDataNum($countField , 1)
				->Run();
		}
	}

	private function _InsertVisit(){
		DB::InsertQryObj(TABLE_VISIT)
			->SetDataStr('dt', date('Y-m-d'))
			->SetDataStr('ip', !empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] :  $_SERVER['REMOTE_ADDR'])
			->SetDataStr('browser', $this->browser)
			->SetDataStr('os', $this->os)
			->SetDataStr('device', $this->device)
			->SetDataStr('uri', $this->http_referer)
			->SetOnDuplicateData('visit', '`visit` + 1')
			->Run();
	}

	private function _InsertCounter($countField, $type = 'total', $type_detail = ''){
		$y = $this->today['y'];
		$m = $this->today['m'];
		$d = $this->today['d'];
		$h = $this->today['h'];
		$w = $this->today['w'];
		$type_detail_etc = '';
		if($type === 'uri'){
			$parse = parse_url($type_detail);
			if(isset($parse['host'])){
				$type_detail_etc = $type_detail;
				$type_detail = $parse['host'];
			}
		}

		$dt = DB::GetQryObj(TABLE_VISIT_COUNTER)
			->AddWhere('`d_y` = %d', $y)
			->AddWhere('`d_m` = %d', $m)
			->AddWhere('`d_d` = %d', $d)
			->AddWhere('`d_h` = %d', $h)
			->AddWhere('`d_w` = %d', $w)
			->AddWhere('`type` = %s', $type)
			->AddWhere('`type_detail` = %s', $type_detail)
			->SetKey('type_detail')
			->Get();

		if($dt){
			DB::UpdateQryObj(TABLE_VISIT_COUNTER)
				->AddWhere('`d_y` = %d', $y)
				->AddWhere('`d_m` = %d', $m)
				->AddWhere('`d_d` = %d', $d)
				->AddWhere('`d_h` = %d', $h)
				->AddWhere('`d_w` = %d', $w)
				->AddWhere('`type` = %s', $type)
				->AddWhere('`type_detail` = %s', $type_detail)
				->SetData($countField , '`' . $countField  .'` + 1')
				->Run();
		}
		else{
			DB::InsertQryObj(TABLE_VISIT_COUNTER)
				->SetShowError(true)
				->SetDataStr('d_y', $y)
				->SetDataStr('d_m', $m)
				->SetDataStr('d_d', $d)
				->SetDataStr('d_h', $h)
				->SetDataStr('d_w', $w)
				->SetDataStr('type', $type)
				->SetDataStr('type_detail', $type_detail)
				->SetDataStr('type_etc', $type_detail_etc)
				->SetDataNum($countField , 1)
				->Run();
		}
	}
}