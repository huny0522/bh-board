<?php
if (BHCSS !== true) {
	exit;
}

use BH\BHCss\BHCss;

BHCss::$variable['$test'] = '#993322';
BHCss::$variable['$--abc'] = 'content:\' \'; display:block; width:20px;';
if (BHCss::callParent(__FILE__, array('test.bhcss.php'))) {
	return;
}
?>
<style>
	.selectBox{
		{ $pr; $dib; $--abc;}
		.test{$dn; $fll;}
	}
</style>

