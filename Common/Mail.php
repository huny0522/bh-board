<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CF;
use \BH_Application as App;

/** @param array $params 설명
	from_name : 보내는사람
	from_email : 보내는사람 메일
	subject : 메일제목
	contents : 메일내용
	to : 받는사람 */
function SendMail($params){
	$from_user = "=?UTF-8?B?".base64_encode($params['from_name'])."?=";
	$subject = "=?UTF-8?B?".base64_encode($params['subject'])."?=";

	$headers = "From: {$from_user} <{$params['from_email']}>\r\n".
	"MIME-Version: 1.0" . "\r\n" .
	"Content-Transfer-Encoding: base64 \r\n".
	"Content-type: text/html; charset=UTF-8" . "\r\n";

	mail($params['to'], $subject, chunk_split(base64_encode($params['contents'])), $headers);
}

