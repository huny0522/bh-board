<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use Controller\Admin\Member;
use Common\MenuHelp;
use \DB as DB;

class MyPage{

	public function __construct(){
	}

	public function __init(){
		CM::MemberAuth(1);
		App::$layout = '_MyPage';
	}

	public function Index(){
		$bmListQry = \BoardManagerModel::GetBoardListQry();
		$data = array();
		while($row = $bmListQry->Get()){
			$bid = $row['bid'].($row['subid'] ? '-' . $row['subid'] : '');
			$controller = $row['controller'] ? $row['controller'] : 'Board/'.$bid;
			$qry = DB::GetListQryObj(TABLE_FIRST.'bbs_'.$row['bid'])
				->AddWhere('muid='.$_SESSION['member']['muid'])
				->AddWhere('subid = %s', $row['subid'])
				->AddWhere('delis=\'n\'')
				->SetKey('seq, subject, mname, reg_date, hit, recommend')
				->SetLimit(5);
			$data[$bid]['controller'] = $controller;
			$data[$bid]['subject'] = $row['title'] ? $row['title'] : $row['subject'];
			while($row2 = $qry->Get()){
				$data[$bid]['list'][] = $row2;
			}
		}
		App::View(null, $data);
	}

	public function Scrap(){
		$bmListQry = \BoardManagerModel::GetBoardListQry();
		$data = array();
		while($row = $bmListQry->Get()){
			$bid = $row['bid'].($row['subid'] ? '-' . $row['subid'] : '');
			$controller = $row['controller'] ? $row['controller'] : 'Board/'.$bid;
			$qry = DB::GetListQryObj(TABLE_FIRST.'bbs_'.$row['bid'] . ' A')
				->AddTable('LEFT JOIN %1 B ON A.seq = B.article_seq', TABLE_FIRST . 'bbs_'.$row['bid'] . '_action')
				->AddWhere('A.subid = %s', $row['subid'])
				->AddWhere('A.delis=\'n\'')
				->AddWhere('A.secret=\'n\'')
				->AddWhere('B.muid = %d', $_SESSION['member']['muid'])
				->AddWhere('B.`action_type` = \'scrap\'')
				->SetKey('A.seq, A.subject, A.mname, A.reg_date, A.hit, A.recommend')
				->SetLimit(5);
			$data[$bid]['controller'] = $controller;
			$data[$bid]['subject'] = $row['title'] ? $row['title'] : $row['subject'];
			while($row2 = $qry->Get()){
				$data[$bid]['list'][] = $row2;
			}
		}
		App::View(null, $data);
	}

	public function PostPassword(){
		$model = App::InitModel('Member');
		if(!isset($_POST['pwd']) || strlen($_POST['pwd']) < 1){
			URLReplace('-1', '패스워드를 입력하여 주세요.');
		}

		$dbGet = new \BH_DB_Get();
		$dbGet->table = $model->table;
		$dbGet->SetKey(array('muid', 'pwd'));
		$dbGet->AddWhere('muid='.$_SESSION['member']['muid']);
		$res = $dbGet->Get();
		if(!$res || !_password_verify($_POST['pwd'], $res['pwd'])){
			URLReplace('-1', '비밀번호가 일치하지 않습니다.');
		}else{
			$_SESSION['MyInfoView'] = true;
			URLReplace($_POST['url']);
		}
	}

	public function MyInfo(){
		if(!isset($_SESSION['MyInfoView']) || !$_SESSION['MyInfoView']){
			App::$html = 'Password.html';
			App::View();
			return;
		}
		$model = App::InitModel('Member');
		$model->data['pwd']->required = false;
		$model->DBGet($_SESSION['member']['muid']);
		App::View($model);
	}

	public function PostMyInfo(){
		if($_SESSION['member']['level'] >= _ADMIN_LEVEL) URLRedirect(-1, '관리자는 관리자페이지에서 수정이 가능합니다.');

		if(!isset($_SESSION['MyInfoView']) || !$_SESSION['MyInfoView']){
			URLReplace(_URL.'/', _MSG_WRONG_CONNECTED);
		}

		$model = App::InitModel('Member');
		$model->DBGet($_SESSION['member']['muid']);
		$model->AddExcept(array('level','approve'));
		$model->data['pwd']->required = false;
		$model->SetPostValues();
		if(isset($_POST['pwd']) && strlen($_POST['pwd'])){
			if(isset($_POST['pwdchk'])){
				if($_POST['pwdchk'] !== $_POST['pwd']) App::$data['error'] = '비밀번호가 일치하지 않습니다.';
			}else{
				URLReplace('-1', _MSG_WRONG_CONNECTED);
			}
		}else{
			$model->AddExcept('pwd');
		}

		$error = $model->GetErrorMessage();
		if(sizeof($error)){
			App::$data['error'] = $error[0];
		}

		if(!isset(App::$data['error'])){
			$model->DBUpdate();
			URLReplace(App::URLAction(), '수정되었습니다.');
		}
		else{
			App::View($model);
		}
	}

	public function PostBlockUser(){
		if(EmptyPost('id')) JSON(false, _MSG_WRONG_CONNECTED);
		$res = DB::InsertQryObj(TABLE_USER_BLOCK)
			->SetDataStr('reg_date', date('Y-m-d H:i:s'))
			->SetDataNum('muid', $_SESSION['member']['muid'])
			->SetDataNum('target_muid', Post('id'))
			->Run();
		if($res->result) JSON($res->result);
		else  JSON($res->result, $res->message ? $res->message : '차단 오류');
	}

	public function PostUnBlockUser(){
		if(EmptyPost('id')) JSON(false, _MSG_WRONG_CONNECTED);
		$res = DB::DeleteQryObj(TABLE_USER_BLOCK)
			->AddWhere('muid = %d', $_SESSION['member']['muid'])
			->AddWhere('target_muid = %d', Post('id'))
			->Run();
		JSON($res, $res ? '' : '차단취소 오류');
	}

	public function BlockList(){
		$qry = DB::GetListPageQryObj(TABLE_USER_BLOCK . ' A')
			->AddTable('LEFT JOIN %1 `B` ON `A`.`muid` = `B`.`muid`', TABLE_MEMBER)
			->AddWhere('`A`.`muid` = %d', $_SESSION['member']['muid'])
			->SetKey('`A`.*, `B`.`nickname`')
			->SetPage(Get('page'))
			->SetPageUrl(App::URLAction(App::$action).App::GetFollowQuery('page'))
			->Run();
		App::View($qry);
	}

	public function WithDraw(){
		if($_SESSION['member']['level'] >= _ADMIN_LEVEL) URLRedirect(-1, '관리자는 탈퇴가 불가능합니다.');

		if(!isset($_SESSION['MyInfoView']) || !$_SESSION['MyInfoView']){
			App::$html = 'Password.html';
			App::View();
			return;
		}
		App::View();
	}

	public function PostWithDraw(){
		if($_SESSION['member']['level'] >= _ADMIN_LEVEL) URLRedirect(-1, '관리자는 탈퇴가 불가능합니다.');

		if(!isset($_SESSION['MyInfoView']) || !$_SESSION['MyInfoView']){
			URLReplace(_URL.'/', _MSG_WRONG_CONNECTED);
		}

		$res = Member::_Withdraw($_SESSION['member']['muid'], Post('withdraw_reason'));
		if(!$res->result) URLRedirect(-1, $res->message ? $res->message : '탈퇴 오류');

		unset($_SESSION['member']);
		session_destroy();
		URLReplace(_URL.'/', '탈퇴되었습니다. 이용해 주셔서 감사합니다.');
	}
}
