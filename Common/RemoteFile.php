<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CF;
use \BH_Application as App;

function GetRemoteFile($url)
{
	// host name 과 url path 값을 획득
	$parsedUrl = parse_url($url);
	$host = $parsedUrl['host'];
	if (isset($parsedUrl['path'])) {
		$path = $parsedUrl['path'];
	} else {
		// url이 http://www.mysite.com 같은 형식이라면
		$path = '/';
	}

	if (isset($parsedUrl['query'])) {
		$path .= '?' . $parsedUrl['query'];
	}

	if (isset($parsedUrl['port'])) {
		$port = $parsedUrl['port'];
	} else {
		// 대부분의 사이트들은 80포트를 사용
		$port = '80';
	}

	$timeout = 10;
	$response = '';
	// 원격 서버에 접속한다
	$fp = @fsockopen($host, $port, $errno, $errstr, $timeout );

	if( !$fp ) {
		echo "Cannot retrieve $url";
	} else {
		// 필요한 헤더들 전송
		fputs($fp, "GET $path HTTP/1.0\r\n" .
			"Host: $host\r\n" .
			"User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.3) Gecko/20060426 Firefox/1.5.0.3\r\n" .
			"Accept: */*\r\n" .
			"Accept-Language: en-us,en;q=0.5\r\n" .
			"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n" .
			"Keep-Alive: 300\r\n" .
			"Connection: keep-alive\r\n" .
			"Referer: http://$host\r\n\r\n");

		// 원격 서버로부터 response 받음
		while ( $line = fread( $fp, 4096 ) ) {
			$response .= $line;
		}

		fclose( $fp );

		// header 부분 걷어냄
		$pos      = strpos($response, "\r\n\r\n");
		$response = substr($response, $pos + 4);
	}

	// 파일의 content 리턴
	return $response;
}

