<?php

error_reporting(E_ALL);

define('_BH_', true);
define('_DIR', str_replace(chr(92), '/', dirname(__FILE__)));

require _DIR . '/core/BHCss.php';

use BH\BHCss\BHCss;

BHCss::setNL($argv[2] == '1');

$res = BHCss::conv($argv[1]);
echo $res->result ? 1 : 0;
echo ':';
echo $res->message . PHP_EOL;
