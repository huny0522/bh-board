<?php
/**
 * self::$connectionInfo['MY']['dsn'] : dbName, hostName 대신 직접 PDO 생성자의 dsn을 입력.(우선순위 높음)
 * self::$connectionInfo['MY']['option'] : 기본값 null
 */

self::$connectionInfo['MY']['hostName'] = 'localhost';
self::$connectionInfo['MY']['userName'] = 'bh_site';
self::$connectionInfo['MY']['userPassword'] = '1234';
self::$connectionInfo['MY']['dbName'] = 'bh_site';
