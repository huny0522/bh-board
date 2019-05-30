<?php
if (BHCSS !== true) {
	exit;
}

// parent : test.bhcss.php

use BH\BHCss\BHCss;

BHCss::$variable['$test'] = '#993322';
BHCss::$variable['$--abc'] = 'content:\' \'; display:block; width:20px;';
?>
<style>
	.selectBox{
		{ $pr; $dib; $--abc;}
		.test{$dn; $fll;}
	}
</style>

