<?php

namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Firebase\Auth\Token\Exception\InvalidToken;

class Message
{
	public $messageModel;
	public $firebase = null;

	public function __construct(){
		$this->messageModel = new \MessageModel();
	}

	public function __Init(){
		CM::MemberAuth();
		App::SetFollowQuery('page', 'keyword', 'type', 'id');
	}

	public function Index(){
		App::$layout = '_MyPage';
		$qry = \MessageModel::GetListQry($_SESSION['member']['muid'], Get('page'))
			->SetPageUrl(App::URLAction().App::GetFollowQuery('page'));

		if(Get('type') === 'send') $qry->AddWhere('`MSG`.`muid` = %d', $_SESSION['member']['muid']);
		else if(Get('type') === 'receive') $qry->AddWhere('`MSG`.`target_muid` = %d', $_SESSION['member']['muid']);

		if(!EmptyGet('keyword')) $qry->AddWhere('INSTR(`MSG`.`comment`, %s)', Get('keyword'));
		if(!EmptyGet('id')) $qry->AddWhere('`MSG`.`muid` = %d OR `MSG`.`target_muid` = %d', Get('id'), Get('id'));

		$blockUsers = CM::GetBlockUsers();

		if($blockUsers){
			$qry->AddWhere('`MSG`.`muid` NOT IN (%d)', $blockUsers)
				->AddWhere('`MSG`.`target_muid` NOT IN (%d)', $blockUsers);
		}

		App::View($qry->Run());
	}

	public function View(){
		$this->_ModelSet(App::$id);
		$blockUsers = CM::GetBlockUsers();

		App::$data['targetMuid'] = $this->messageModel->GetTargetMuid();
		if(App::$data['targetMuid'] === $_SESSION['member']['muid']) JSON(false, '자기 자신에게 메세지를 보낼 수 없습니다.');
		App::$data['target'] = CM::GetOtherMember(App::$data['targetMuid']);

		if(in_array(App::$data['targetMuid'], $blockUsers)) JSON(false, '차단된 사용자의 글입니다.');
		if($this->messageModel->_target_muid->value == $_SESSION['member']['muid'] && !strlen($this->messageModel->_read_date->value)){
			$this->messageModel->_read_date->SetValue(date('Y-m-d H:i:s'));
			$this->messageModel->DBUpdate();
		}
		JSON(true, '', App::GetOnlyView());
	}

	public function Write(){
		if(EmptyGet('id')) JSON(false, _MSG_WRONG_CONNECTED);
		App::$data['target'] = CM::GetOtherMember(Get('id'));
		if(!App::$data) JSON(false, '해당 회원이 존재하지 않습니다.');
		JSON(true, '', App::GetOnlyView('Write'));
	}

	public function Modify(){
		$this->_ModelSet(App::$id);

		if($this->messageModel->_muid->value != $_SESSION['member']['muid']) JSON(false, _MSG_WRONG_CONNECTED);
		if(strlen($this->messageModel->_read_date->value)) JSON(false, '이미 읽은 페이지로 수정이 불가능합니다.');

		App::$data['targetMuid'] = $this->messageModel->GetTargetMuid();
		App::$data['target'] = CM::GetOtherMember(App::$data['targetMuid']);

		JSON(true, '', App::GetOnlyView('Write'));
	}

	public function PostWrite($seq = null){
		if(!is_null($seq)){
			$this->_ModelSet($seq);
			if($this->messageModel->_muid->value != $_SESSION['member']['muid']) JSON(false, _MSG_WRONG_CONNECTED);
			if(strlen($this->messageModel->_read_date->value)) JSON(false, '이미 읽은 페이지로 수정이 불가능합니다.');
		}
		$this->messageModel->SetPostValues();
		$err = $this->messageModel->GetErrorMessage();
		if(sizeof($err)) JSON(false, $err[0]);

		if(is_null($seq)){
			$this->messageModel->_muid->SetValue($_SESSION['member']['muid']);
			$res = $this->messageModel->DBInsert();
		}
		else $res = $this->messageModel->DBUpdate();

		if(!$res->result) JSON(false, $res->message ? $res->message : 'Query Error');
		else JSON(true);
	}

	public function PostModify(){
		$this->PostWrite(App::$id);
	}

	public function PostDelete(){
		$this->_ModelSet(App::$id);
		$res = $this->messageModel->Del();

		if($res->result) JSON(true);
		else JSON(false, $res->message ? $res->message : 'Query Error');
	}

	private function _ModelSet($seq){
		if(!strlen($seq)) URLReplace(-1, _MSG_WRONG_CONNECTED);
		$res = $this->messageModel->DBGet($seq);
		if(!$res->result) URLReplace(-1, $res->message ? $res->message : _MSG_NO_ARTICLE);
		if(($this->messageModel->_muid->value == $_SESSION['member']['muid'] && $this->messageModel->_delis->value == 'y') || ($this->messageModel->_muid->value == $_SESSION['member']['muid'] && $this->messageModel->_delis->value == 'y')) URLRedirect(-1, '삭제된 게시물입니다.');
	}

	/* ----------------------------------------------
	 *
	 *       채팅
	 *
	 ---------------------------------------------- */

	public function GetFirebase(){
		if(is_null($this->firebase)){
			$factory = new Factory();

			$serviceAccount = ServiceAccount::fromJson(App::$cfg->Def()->googleServiceAccount->Val());
			$this->firebase = $factory->withServiceAccount($serviceAccount)->create();
		}
		return $this->firebase;
	}

	public function Chat(){
		if(!CM::FirebaseSetIs()) URLRedirect(-1);
		if(!strlen(App::$id)) URLRedirect(-1);
		App::$data['targetMember'] = CM::GetOtherMember(App::$id);
		if(!App::$data['targetMember']) URLRedirect(-1, '탈퇴하였거나 삭제된 회원입니다.');

		$blockUser = CM::GetBlockUsers();
		if(in_array(App::$data['targetMember']['muid'], $blockUser)){
			URLRedirect(-1, '차단된 사용자입니다.');
		}

		/*$ruleSet = RuleSet::fromArray(
			array(
				'messages' => array(
					'$roomId' => array(
						'.read' => 'root.child(\'members\').child($roomId).child(auth.uid).exists()',
						'.write' => 'root.child(\'members\').child($roomId).child(auth.uid).exists()',
						'.indexOn' => 'timestamp'
					)
				),
				'members' => array(
					'$roomId' => array(
						'$uid' => array(
							'.read' =>  'auth != null',
							'.write' => false
						)
					)
				)
			)
		);
		$this->GetFirebase()->getDatabase()->updateRules($ruleSet);*/

		$uid = $_SESSION['member']['muid'];
		App::$data['timestamp'] = time();

		App::$data['customToken'] = $this->GetFirebase()->getAuth()->createCustomToken($uid);
		$a = array(App::$data['targetMember']['muid'], $uid);
		rsort($a);
		App::$data['RoomId'] = implode('-', $a);
		$this->GetFirebase()->getDatabase()->getReference('members/' . App::$data['RoomId'])->set(array($uid => true, App::$data['targetMember']['muid'] => true));
		App::View($this->messageModel);
	}

	public function PostChatWrite(){
		$this->messageModel->SetPostValuesWithFile();
		if(EmptyPost('comment')) JSON(false, '내용을 입력하여 주세요.');

		$this->messageModel->_muid->SetValue($_SESSION['member']['muid']);
		$this->messageModel->_target_muid->SetRequired();
		$this->messageModel->_comment->SetRequired();
		$this->messageModel->_target_muid->SetValue(Post('target'));
		$err = $this->messageModel->GetErrorMessage();
		if(sizeof($err)) JSON(false, $err[0]);

		$res = $this->messageModel->DBInsert();
		if($res->result){
			$data = array(
				'seq' => $res->id,
				'msg' => GetDBText($this->messageModel->_comment->value),
				'timestamp' => strtotime($this->messageModel->_reg_date->value),
				'uid' => CM::GetMember('mid'),
				'mname' => CM::GetMember('mname'),
			);
			JSON(true, '', $data);
		}
		else JSON(false, $res->message ? $res->message : 'DB 삽입 오류');

	}

	public function Download(){
		$this->_ModelSet(App::$id);
		if(!strlen($this->messageModel->_file->value)) URLRedirect(-1, '해당 파일이 존재하지 않습니다.');
		Download(_UPLOAD_DIR . $this->messageModel->GetFilePath('file'), $this->messageModel->GetFileName('file'));
	}

	public function GetList(){
		$krWeek = array('일', '월', '화', '수', '목', '금', '토');

		$res = \MessageModel::GetChat(Get('targetId'), 5, EmptyGet('lastSeq') ? null : Get('lastSeq'), Get('beforeIs'));
		if($res->result){
			$res->data->SetKey('comment, file, muid, read_date, reg_date, seq, target_muid');
			$data = array();
			$noReadSeq = array();
			while($row = $res->data->Get()){
				$row['sendIs'] = ($row['muid'] === $_SESSION['member']['muid']);
				$week = $krWeek[date('w', strtotime($row['reg_date']))];
				$hour = substr($row['reg_date'], 11, 2);
				$h = ($hour >= 12) ? '오후' : '오전';
				$h .= ($hour > 12) ? $hour - 12 : $hour;
				$row['date'] = substr($row['reg_date'], 0,4) . '/' . substr($row['reg_date'], 5,2) . '/' . substr($row['reg_date'], 8,2).'(' . $week . ') ' . $h.substr($row['reg_date'], 13,3);
				$row['readIs'] = strlen($row['read_date']) ? true : false;
				if(strlen($row['file'])){
					$row['fileLink'] = App::URLAction('Download/' . $row['seq']);
					$row['filePath'] = _UPLOAD_URL . $this->messageModel->GetFilePathByValue($row['file']);
					$row['fileName'] = $this->messageModel->GetFileNameByValue($row['file']);
					$row['isImage'] = IsImageFileName($row['fileName']);
				}
				else{
					$row['fileLink'] = '';
					$row['filePath'] = '';
					$row['fileName'] = '';
					$row['isImage'] = false;
				}
				// 읽음 처리 할 데이터
				if($row['sendIs'] === false && !strlen($row['read_date'])){
					$noReadSeq[] = $row['seq'];
					$row['readIs'] = true;
				}
				$data[] = $row;

			}

			\MessageModel::UpdateReadArticles($noReadSeq);

			if(Get('beforeIs') && !sizeof($data)) JSON(false, '더이상 데이터가 없습니다.', array('noBeforeDataIs' => true));

			JSON(true, '', array('noReadSeq' => $noReadSeq, 'data' => array_reverse($data)));
		}
		else JSON(false, $res->message);
	}
}