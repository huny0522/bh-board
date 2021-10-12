<?php
/**
 * self::$connectionInfo['MY']['dsn'] : dbName, hostName 대신 직접 PDO 생성자의 dsn을 입력.(우선순위 높음)
 * dsn 예제 ) $dsn = "mysql:host=localhost;port=33306;dbname=bh_site;charset=utf8";
 * self::$connectionInfo['MY']['option'] : 기본값 null
 */

self::$connectionInfo['MY']['hostName'] = 'localhost';
self::$connectionInfo['MY']['userName'] = 'bh_site';
self::$connectionInfo['MY']['userPassword'] = '1234';
self::$connectionInfo['MY']['dbName'] = 'bh_site';

// PHP CLI 용
self::$connectionInfo['CLI']['hostName'] = 'localhost';
self::$connectionInfo['CLI']['userName'] = 'bh_site';
self::$connectionInfo['CLI']['userPassword'] = '1234';
self::$connectionInfo['CLI']['dbName'] = 'bh_site';
