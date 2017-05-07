<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Application as App;
use \BH_Common as CF;

class MypageController{

	public function __construct(){
	}

	public function __init(){
		CF::Get()->MemberAuth(1);
		App::$Layout = '_Mypage';
	}

	public function Index(){
		$dbGetList = new \BH_DB_GetList(TABLE_BOARD_MNG);
		$dbGetList->SetKey('bid, subject');
		$data = array();
		while($row = $dbGetList->Get()){
			$controller = 'Board/'.$row['bid'];
			foreach(App::$RouterInstance->SubMenu as $sub){
				foreach($sub as $sub2){
					if($sub2['type'] == 'board' && $sub2['bid'] == $row['bid']){
						$controller = $sub2['controller'];
					}
				}
			}
			$boardGetList = new \BH_DB_GetList(TABLE_FIRST.'bbs_'.$row['bid']);
			$boardGetList->AddWhere('muid='.$_SESSION['member']['muid']);
			$boardGetList->SetKey('seq, subject, mname, reg_date, hit, recommend');
			$boardGetList->limit = '5';
			$data[$row['bid']]['controller'] = $controller;
			$data[$row['bid']]['subject'] = $row['subject'];
			while($row2 = $boardGetList->Get()){
				$data[$row['bid']]['list'][] = $row2;
			}
		}
		App::View($this, null, $data);
	}

	public function PostPassword(){
		$model = \BH_Model::GetModelExtends('Member');
		if(!isset($_POST['pwd']) || strlen($_POST['pwd']) < 1){
			Redirect('-1', '패스워드를 입력하여 주세요.');
		}

		$dbGet = new \BH_DB_Get();
		$dbGet->table = $model->table;
		$dbGet->SetKey(array('muid', 'pwd'));
		$dbGet->AddWhere('muid='.$_SESSION['member']['muid']);
		$res = $dbGet->Get();
		if(!$res || !_password_verify($_POST['pwd'], $res['pwd'])){
			Redirect('-1', '비밀번호가 일치하지 않습니다.');
		}else{
			$_SESSION['MyInfoView'] = true;
			Redirect($_POST['url']);
		}
	}

	public function MyInfo(){
		if(!isset($_SESSION['MyInfoView']) || !$_SESSION['MyInfoView']){
			App::$Html = 'Password.html';
			App::View($this);
			return;
		}
		$model = \BH_Model::GetModelExtends('Member');
		$model->data['pwd']->Required = false;
		$model->DBGet($_SESSION['member']['muid']);
		App::View($this, $model);
	}

	public function PostMyInfo(){
		if(!isset($_SESSION['MyInfoView']) || !$_SESSION['MyInfoView']){
			Redirect(_URL.'/', _WRONG_CONNECTED);
		}

		$model = \BH_Model::GetModelExtends('Member');
		$model->DBGet($_SESSION['member']['muid']);
		$model->AddExcept(array('level','approve'));
		$model->data['pwd']->Required = false;
		$model->SetPostValues();
		if(isset($_POST['pwd']) && strlen($_POST['pwd'])){
			if(isset($_POST['pwdchk']) && strlen($_POST['pwd'])){
				App::$Data['error'] = '비밀번호가 일치하지 않습니다.';
			}else{
				Redirect('-1', _WRONG_CONNECTED);
			}
		}else{
			$model->AddExcept('pwd');
		}

		$error = $model->GetErrorMessage();
		if(sizeof($error)){
			App::$Data['error'] = $error[0];
		}

		if(!isset(App::$Data['error'])){
			$model->DBUpdate();
			Redirect(App::URLAction(), '수정되었습니다.');
		}
		else{
			App::View($this, $model);
		}
	}

	public function WithDraw(){
		if(!isset($_SESSION['MyInfoView']) || !$_SESSION['MyInfoView']){
			App::$Html = 'Password.html';
			App::View($this);
			return;
		}
		App::View($this);
	}
	public function PostWithDraw(){
		if(!isset($_SESSION['MyInfoView']) || !$_SESSION['MyInfoView']){
			Redirect(_URL.'/', _WRONG_CONNECTED);
		}
		$model = \BH_Model::GetModelExtends('Member');
		$model->DBGet($_SESSION['member']['muid']);
		$dbInsert = new \BH_DB_Insert(TABLE_WITHDRAW_MEMBER);
		$dbInsert->SetData('muid', $model->GetValue('muid'));
		$dbInsert->SetData('mid', SetDBText($model->GetValue('mid')));
		$dbInsert->SetData('mname', SetDBText($model->GetValue('mname')));
		$dbInsert->SetData('cname', SetDBText($model->GetValue('cname')));
		$dbInsert->SetData('nickname', SetDBText($model->GetValue('nickname')));
		$dbInsert->SetData('level', SetDBText($model->GetValue('level')));
		$dbInsert->SetData('email', SetDBText($model->GetValue('email')));
		$dbInsert->SetData('reg_date', SetDBText($model->GetValue('reg_date')));
		$dbInsert->SetData('reason', SetDBText($_POST['withdraw_reason']));
		$dbInsert->SetData('w_date', 'NOW()');
		$dbInsert->Run();
		$model->DBDelete($_SESSION['member']['muid']);
		unset($_SESSION['member']);
		session_destroy();
		Redirect(_URL.'/', '탈퇴되었습니다. 이용해 주셔서 감사합니다.');
	}
}
