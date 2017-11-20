<?php

namespace Common;

use \BH_Application as App;
use \BH_Common as CM;
use \DB;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
	// 발신자명
	public $senderName = '';
	// 발신자메일주소
	public $senderMail = '';
	// 'google' or blank
	public $sendHost = '';

	public $gMailId = '';
	public $gMailPw = '';

	public $body = '';
	public $subject = '';
	public $attachFile = array();
	/**
	 * composer need "PHPMailer\PHPMailer"
	 * @var PHPMailer
	 */
	private $mailer;
	private $receiverMails = array();

	public function __construct($debug = 0){
		$this->mailer = new PHPMailer();
		$this->mailer->SMTPDebug = $debug;
		$this->mailer->isSMTP();
		$this->mailer->isHTML(true);
		$this->mailer->CharSet = 'UTF-8';
	}

	// sendHost 가 구글일 경우 구글메일발송, 아니면 서버메일발송
	public function Send(){
		if($this->sendHost == 'google') $this->SendGMail();
		else $this->DefaultSendMail();
	}

	public function SendGMail(){
		$this->mailer->Host = 'smtp.gmail.com';
		$this->mailer->Port = 587;
		$this->mailer->SMTPAuth = true;
		$this->mailer->SMTPSecure = 'tls';
		$this->mailer->Username = $this->gMailId;
		$this->mailer->Password = $this->gMailPw;
		$this->mailer->CharSet="UTF-8";
		return $this->SendMail();
	}

	public function DefaultSendMail(){
		$this->mailer->Username = $this->senderName;
		$this->mailer->CharSet="UTF-8";
		return $this->SendMail();
	}

	public function &AddEmail($email, $name){
		$this->receiverMails[] = array('email' => $email, 'name' => $name);
		return $this;
	}

	public function &AddFile($path, $name){
		$this->attachFile[] = array('path' => $path, 'name' => $name);
		return $this;
	}

	private function SendMail(){
		$this->mailer->setFrom($this->senderMail , $this->senderName);
		foreach($this->receiverMails as $v){
			$this->mailer->addBCC($v['email'], $v['name']);
		}
		foreach($this->attachFile as $v){
			$this->mailer->AddAttachment($v['path'], $v['name']);
		}
		$this->mailer->Subject = $this->subject;
		$this->mailer->Body = $this->body;

		if(!$this->mailer->send()) {
			return false;
		}
		return true;
	}
}





