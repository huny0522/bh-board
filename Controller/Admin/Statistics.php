<?php

namespace Controller\Admin;

use \BH_Application as App;
use \BH_Common as CM;
use Common\VisitCounter;
use \DB as DB;

class Statistics
{
	/**
	 * @var VisitCounter
	 */
	public $visitCounter;

	public function __construct(){
		$this->visitCounter = VisitCounter::GetInstance();
		App::$data['optYearArr'] = array();
		for($y = 2010; $y <= date('Y'); $y++){
			App::$data['optYearArr'][$y] = $y . '년';
		}
		App::$data['optMonthArr'] = array();
		for($m = 1; $m <= 12; $m++){
			App::$data['optMonthArr'][$m] = $m . '월';
		}
	}

	public function __Init(){
		App::$data['NowMenu'] = '006001';
		CM::AdminAuth();
		App::$layout = '_AdminStatistics';
	}

	// 접속자별
	public function Index(){
		if(!isset($_GET['by'])) $_GET['by'] = date('Y');
		if(!isset($_GET['bm'])) $_GET['bm'] = date('m');
		App::SetFollowQuery('by', 'bm', 'page');

		$y = Get('by');
		$m = sprintf('%02d', Get('bm'));

		$l = sprintf('%02d', GetLastDay(Get('by'), Get('bm')));

		$qry = DB::GetListPageQryObj(TABLE_VISIT)
			->SetGroup('`ip`')
			->SetKey('*, SUM(visit) as `cnt`')
			->SetSort('`cnt` DESC, `dt` DESC')
			->AddWhere('`dt` BETWEEN %s AND %s', $y.'-'.$m.'-01', $y.'-'.$m.'-' . $l)
			->SetPage(Get('page'))
			->SetArticleCount(30)
			->SetPageUrl(App::URLAction().App::GetFollowQuery('page'))
			->Run();

		App::$data['sData'] = array();
		$total = 0;
		while($data = $qry->Get()){
			$total += $data['cnt'];
			App::$data['sData'][$data['ip']] = $data;
		}
		foreach(App::$data['sData'] as $k => $row){
			App::$data['sData'][$k]['per'] = round(($row['cnt'] / $total) * 100);
		}

		App::View('StatisticsVisitor', $qry);
	}

	// 일별
	public function Day(){
		if(Get('bd') > Get('ed')) URLRedirect(-1, '시작일을 종료일보다 낮게 검색하여주세요.');
		if(!isset($_GET['bd'])) $_GET['bd'] = date('Y-m-01');
		App::SetFollowQuery('bd', 'ed', 'page');
		$this->_SetGetParam();

		$qry = $this->visitCounter->GetStatisticsDayQuery();
		$qry->SetPage(Get('page'))
			->SetArticleCount(30)
			->SetPageUrl(App::URLAction().App::GetFollowQuery('page'))
			->Run();
		App::$data['sData'] = array();
		App::$data['lastDay'] = '';
		App::$data['firstDay'] = '9999-99-99';
		while($data = $qry->Get()){
			$k = $data['d_y'].'-'.sprintf('%02d', $data['d_m']).'-'.sprintf('%02d', $data['d_d']);
			App::$data['firstDay'] = min(App::$data['firstDay'], $k);
			App::$data['lastDay'] = max(App::$data['lastDay'], $k);
			App::$data['sData'][$k] = $data;
		}
		$this->_SetTotal();
		App::View('StatisticsDay', $qry);
	}

	// 월별
	public function Month(){
		if(Get('bd') > Get('ed')) URLRedirect(-1, '시작일을 종료일보다 낮게 검색하여주세요.');
		if(!isset($_GET['bd'])) $_GET['bd'] = (date('Y') - 1).date('m-d');
		$this->_SetGetParam();
		App::$data['sData'] = $this->visitCounter->GetStatisticsMonth();
		$this->_SetTotal();
		App::View('StatisticsMonth');
	}

	// 년도별
	public function Year(){
		if(Get('bd') > Get('ed')) URLRedirect(-1, '시작년도를 종료년도보다 낮게 검색하여주세요.');
		if(!isset($_GET['bd'])) $_GET['bd'] = (date('Y') - 1);
		App::SetFollowQuery('bd', 'ed');
		$this->_SetGetParam();
		App::$data['beginDate'] = substr(App::$data['beginDate'], 0, 4);
		App::$data['endDate'] = substr(App::$data['endDate'], 0, 4);
		App::$data['sData'] = $this->visitCounter->GetStatisticsYear();
		$this->_SetTotal();
		App::View('StatisticsYear');
	}

	// 요일별통계
	public function Week(){
		if(Get('bd') > Get('ed')) URLRedirect(-1, '시작일을 종료일보다 낮게 검색하여주세요.');
		App::SetFollowQuery('bd', 'ed');
		$this->_SetGetParam();
		App::$data['sData'] = $this->visitCounter->GetStatisticsWeek();
		$this->_SetTotal();
		App::View('StatisticsWeek');
	}

	// 시간별통계
	public function Hour(){
		if(Get('bd') > Get('ed')) URLRedirect(-1, '시작일을 종료일보다 낮게 검색하여주세요.');
		App::SetFollowQuery('bd', 'ed');
		$this->_SetGetParam();
		App::$data['sData'] = $this->visitCounter->GetStatisticsHour();
		$this->_SetTotal();
		App::View('StatisticsHour');
	}

	// 브라우저별
	public function Browser(){
		if(Get('bd') > Get('ed')) URLRedirect(-1, '시작일을 종료일보다 낮게 검색하여주세요.');
		App::SetFollowQuery('bd', 'ed');
		$this->_SetGetParam();
		App::$data['sData'] = $this->visitCounter->GetStatisticsBrowser();
		$this->_SetTotal();
		App::View('StatisticsBrowser');
	}

	// 장치별
	public function Device(){
		if(Get('bd') > Get('ed')) URLRedirect(-1, '시작일을 종료일보다 낮게 검색하여주세요.');
		App::SetFollowQuery('bd', 'ed');
		$this->_SetGetParam();
		App::$data['sData'] = $this->visitCounter->GetStatisticsDevice();
		$this->_SetTotal();
		App::View('StatisticsDevice');
	}

	// 접속경로별
	public function URI(){
		if(Get('bd') > Get('ed')) URLRedirect(-1, '시작일을 종료일보다 낮게 검색하여주세요.');
		App::SetFollowQuery('bd', 'ed');
		$this->_SetGetParam();
		App::$data['sData'] = $this->visitCounter->GetStatisticsURI();
		$this->_SetTotal();
		App::View('StatisticsURI');

	}

	// 운영체제별
	public function OS(){
		if(Get('bd') > Get('ed')) URLRedirect(-1, '시작일을 종료일보다 낮게 검색하여주세요.');
		App::SetFollowQuery('bd', 'ed');
		$this->_SetGetParam();
		App::$data['sData'] = $this->visitCounter->GetStatisticsOS();
		$this->_SetTotal();
		App::View('StatisticsOS');

	}



	private function _SetTotal(){
		App::$data['visitTotal'] = 0;
		App::$data['loginTotal'] = 0;
		foreach(App::$data['sData'] as $row){
			App::$data['visitTotal'] += $row['visit'];
			App::$data['loginTotal'] += $row['login'];
		}
		foreach(App::$data['sData'] as $k => $row){
			App::$data['sData'][$k]['visitPer'] = App::$data['visitTotal'] ? round(($row['visit'] / App::$data['visitTotal']) * 100) : 0;
			App::$data['sData'][$k]['loginPer'] = App::$data['loginTotal'] ? round(($row['login'] / App::$data['loginTotal']) * 100) : 0;
		}
	}

	private function _SetGetParam(){
		$bd = preg_replace('/[^0-9]/', '', StrTrim(Get('bd')));
		$ed = preg_replace('/[^0-9]/', '', StrTrim(Get('ed')));
		if(!strlen($bd)) $bd = date('Ymd');
		if(!strlen($ed)) $ed = date('Ymd');
		if(strlen($bd) >= 4) $this->visitCounter->viewOption['beginY'] = substr($bd, 0, 4);
		if(strlen($bd) >= 6) $this->visitCounter->viewOption['beginM'] = substr($bd, 4, 2);
		if(strlen($bd) >= 8) $this->visitCounter->viewOption['beginD'] = substr($bd, 6, 2);
		if(strlen($ed) >= 4) $this->visitCounter->viewOption['endY'] = substr($ed, 0, 4);
		if(strlen($ed) >= 6) $this->visitCounter->viewOption['endM'] = substr($ed, 4, 2);
		if(strlen($ed) >= 8) $this->visitCounter->viewOption['endD'] = substr($ed, 6, 2);
		App::$data['beginDate'] = $this->visitCounter->viewOption['beginY'] . '-' . $this->visitCounter->viewOption['beginM'] . '-' . $this->visitCounter->viewOption['beginD'];
		App::$data['endDate'] = $this->visitCounter->viewOption['endY'] . '-' . $this->visitCounter->viewOption['endM'] . '-' . $this->visitCounter->viewOption['endD'];
	}
}